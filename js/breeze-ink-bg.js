/* breeze-ink-bg.js  — 白底水墨 / 科技紙感
   用法：
     <div id="techbg-host" data-mode="login|app" data-speed="1.0" data-density="1.0"></div>
     <script src="js/breeze-ink-bg.js"></script>
   全域 API（與你現有相容）：
     circuitBgPulse(x?,y?) / circuitBgSetMode(m) / circuitBgSetSpeed(n) / circuitBgSetDensity(n)
*/
(function () {
  if (window.__breezeInkLoaded) return;
  window.__breezeInkLoaded = true;

  // 讀取 Bootstrap 變數
  const cssVars = () => {
    const r = getComputedStyle(document.documentElement);
    const get = (k, d) => (r.getPropertyValue(k) || '').trim() || d;
    return {
      bg  : get('--bs-body-bg', '#ffffff'),
      pri : get('--bs-primary', '#0d6efd'),
      info: get('--bs-info',    '#0dcaf0'),
      txt : get('--bs-body-color', '#212529'),
    };
  };

  // HEX→HSL / HSL→RGBA
  const clamp = (v,a,b)=>Math.max(a,Math.min(b,v));
  function hex2hsl(hex){
    let h = hex.replace('#',''); if (h.length===3) h = h.split('').map(x=>x+x).join('');
    const r = parseInt(h.slice(0,2),16)/255, g=parseInt(h.slice(2,4),16)/255, b=parseInt(h.slice(4,6),16)/255;
    const max=Math.max(r,g,b), min=Math.min(r,g,b);
    let H,S,L=(max+min)/2;
    if (max===min){ H=S=0; } else {
      const d=max-min;
      S = L>0.5 ? d/(2-max-min) : d/(max+min);
      switch(max){
        case r: H=(g-b)/d + (g<b?6:0); break;
        case g: H=(b-r)/d + 2; break;
        case b: H=(r-g)/d + 4; break;
      }
      H/=6;
    }
    return {h:H*360,s:S*100,l:L*100};
  }
  const hslA = (h,s,l,a)=>`hsla(${h},${s}%,${l}%,${a})`;

  // 生成可平舖的噪點（紙感）
  function makeNoise(size=128, alpha=14){
    const c=document.createElement('canvas'); c.width=c.height=size;
    const ctx=c.getContext('2d', { willReadFrequently:true });
    const img=ctx.createImageData(size,size);
    for(let i=0;i<img.data.length;i+=4){
      const v=Math.random()*255|0;
      img.data[i]=img.data[i+1]=img.data[i+2]=v; img.data[i+3]=alpha;
    }
    ctx.putImageData(img,0,0);
    return c;
  }

  function initHost(host){
    if (!host || host.__inited) return;
    host.__inited = true;

    const opts = {
      mode: (host.dataset.mode || 'login'),      // login | app
      speed: clamp(parseFloat(host.dataset.speed||1.0), .4, 2),
      density: clamp(parseFloat(host.dataset.density||1.0), .5, 2),
    };
    const contrast = (host.dataset.contrast || 'normal');
const ALPHA = contrast === 'extra' ? 1.9 : (contrast === 'bold' ? 1.5 : 1.0);
const amp = v => Math.min(1, v * ALPHA);   // 透明度放大器

    if (matchMedia('(prefers-reduced-motion: reduce)').matches) opts.speed = Math.min(opts.speed, 0.9);

    const c=document.createElement('canvas'); c.className='breeze-ink-canvas';
    c.style.width='100%'; c.style.height='100%'; c.style.display='block';
    host.appendChild(c);
    const ctx=c.getContext('2d');

    let W=0,H=0,dpr=1;
    function fit(){
      dpr=Math.min(window.devicePixelRatio||1,2);
      W=host.clientWidth; H=host.clientHeight;
      c.width=W*dpr; c.height=H*dpr;
      ctx.setTransform(dpr,0,0,dpr,0,0);
    }

    // 淺色墨團（用 multiply 疊在白紙上）
    const blobs=[];
    function makeBlobs(){
      blobs.length=0;
      const { pri, info } = cssVars();
      const p = hex2hsl(pri), i = hex2hsl(info);
      // 降彩提亮，做霧粉色調
      const tints = [
        {h:p.h, s:clamp(p.s*0.35,10,45), l:clamp(88,70,92)},
        {h:i.h, s:clamp(i.s*0.35,10,45), l:clamp(86,70,92)},
      ];
      const n = Math.round(5 * opts.density);
      for(let k=0;k<n;k++){
        const r = Math.min(W,H) * (0.20 + Math.random()*0.22);
        const x = Math.random()*W, y = Math.random()*H;
        const vx = (Math.random()*0.15 + 0.04)*(Math.random()<.5?-1:1)*opts.speed;
        const vy = (Math.random()*0.15 + 0.04)*(Math.random()<.5?-1:1)*opts.speed;
        const tint = tints[k%tints.length];
        blobs.push({x,y,vx,vy,r,tint});
      }
    }

    // 微點陣網格（極淡）
    function drawMicroGrid(){
      const step = Math.round(28 / Math.sqrt(opts.density));
      const alpha = opts.mode==='login' ? 0.06 : 0.08;
      ctx.fillStyle = `rgba(0,0,0,${alpha})`;
      for(let y=0;y<H;y+=step){
        const offset = (y/step)%2 ? step/2 : 0; // 蜂巢點位
        for(let x=0;x<W;x+=step){
          ctx.beginPath(); ctx.arc(x+offset, y, 0.6, 0, Math.PI*2); ctx.fill();
        }
      }
    }

    // 邊緣明暗暈（淡淡 vignette，讓白底不刺眼）
    function drawVignette(){
      const g = ctx.createRadialGradient(W/2,H/2, Math.min(W,H)*0.3, W/2,H/2, Math.max(W,H)*0.72);
      g.addColorStop(0, 'rgba(0,0,0,0)');
      g.addColorStop(1, 'rgba(0,0,0,0.06)');
      ctx.fillStyle=g; ctx.fillRect(0,0,W,H);
    }

    // 光帶（很淡、暖調；避免像掃描線）
    let tick=0;
    function drawSunSweep(){
      const band = (tick*opts.speed*0.5) % (W+200) - 100;
      const { pri } = cssVars();
      const P = hex2hsl(pri);
      const warm = hslA((P.h+20)%360, 60, 85, opts.mode==='login'?0.10:0.14);
      const grad = ctx.createLinearGradient(band-60,0,band+60,0);
      grad.addColorStop(0, 'rgba(255,255,255,0)');
      grad.addColorStop(0.5, warm);
      grad.addColorStop(1, 'rgba(255,255,255,0)');
      ctx.fillStyle = grad; ctx.fillRect(band-60,0,120,H);
    }

    // 墨滴漣漪（事件）
    const pulses=[];
    function pulse(x,y){
      if (typeof x!=='number'||typeof y!=='number'){ x=Math.random()*W; y=Math.random()*H; }
      pulses.push({x,y,life:0});
    }
    function drawPulses(){
      for(let i=pulses.length-1;i>=0;i--){
        const p=pulses[i]; p.life += 1.5*opts.speed;
        const r=p.life*5.5, a=Math.max(0, 1 - p.life/36);
        if (a<=0){ pulses.splice(i,1); continue; }
        // 外圈灰墨
        ctx.strokeStyle = `rgba(0,0,0,${0.08*a})`;
        ctx.lineWidth = 2;
        ctx.beginPath(); ctx.arc(p.x,p.y,r,0,Math.PI*2); ctx.stroke();
        // 內圈粉色
        const { pri } = cssVars(); const H = hex2hsl(pri);
        ctx.strokeStyle = hslA(H.h, 35, 75, 0.28*a);
        ctx.lineWidth = 1.5;
        ctx.beginPath(); ctx.arc(p.x,p.y,r*0.66,0,Math.PI*2); ctx.stroke();
      }
    }

    // 噪點（紙纖維）
    const noiseCanvas = makeNoise(128, 14);
    let noisePat;

    // 微視差（非常小）
    const mouse = { x:0, y:0, tx:0, ty:0, active:false };
    function onMove(e){ mouse.tx=e.clientX; mouse.ty=e.clientY; mouse.active=true; }
    function onClick(e){ pulse(e.clientX, e.clientY); }
    function onVis(){ paused=document.hidden; }
    function onResize(){ fit(); makeBlobs(); noisePat=ctx.createPattern(noiseCanvas,'repeat'); }

    let paused=false;
    function render(){
      if (paused){ requestAnimationFrame(render); return; }
      tick++;
      const { bg } = cssVars();
      ctx.fillStyle = bg || '#ffffff';
      ctx.fillRect(0,0,W,H);

      // 微視差
      mouse.x += (mouse.tx - mouse.x)*0.05;
      mouse.y += (mouse.ty - mouse.y)*0.05;
      const offx = (mouse.x - W/2)*0.01, offy = (mouse.y - H/2)*0.01;

      // 墨團（multiply 疊色）
      ctx.save();
      ctx.globalCompositeOperation = 'multiply';
      for(const b of blobs){
        b.x += b.vx; b.y += b.vy;
        if (b.x<-b.r) b.x=W+b.r; if (b.x>W+b.r) b.x=-b.r;
        if (b.y<-b.r) b.y=H+b.r; if (b.y>H+b.r) b.y=-b.r;

        const g = ctx.createRadialGradient(b.x+offx, b.y+offy, b.r*0.25, b.x+offx, b.y+offy, b.r);
        const alpha = opts.mode==='login' ? 0.35 : 0.45;
        g.addColorStop(0, hslA(b.tint.h, b.tint.s, b.tint.l, alpha));
        g.addColorStop(1, hslA(b.tint.h, b.tint.s, b.tint.l, 0));
        ctx.fillStyle=g; ctx.beginPath(); ctx.arc(b.x+offx, b.y+offy, b.r, 0, Math.PI*2); ctx.fill();
      }
      ctx.restore();

      // 漣漪
      drawPulses();

      // 微點格
      drawMicroGrid();

      // 暈影
      drawVignette();

      // 柔光掃帶
      drawSunSweep();

      // 顆粒
      if (!noisePat) noisePat = ctx.createPattern(noiseCanvas,'repeat');
      ctx.save();
      ctx.globalAlpha = opts.mode==='login' ? 0.08 : 0.10;
      ctx.fillStyle = noisePat; ctx.fillRect(0,0,W,H);
      ctx.restore();

      requestAnimationFrame(render);
    }

    fit(); makeBlobs(); onResize(); render();
    window.addEventListener('mousemove', onMove, { passive:true });
    window.addEventListener('click', onClick, { passive:true });
    window.addEventListener('resize', onResize);
    document.addEventListener('visibilitychange', onVis);

    // 對外 API（保持與你現有命名一致）
    const api = {
      pulse: (x,y)=> pulse(x,y),
      pulseRandom: (n)=>{ for(let i=0;i<(n||1);i++) pulse(); },
      setMode: (m)=>{ opts.mode = (m==='app'?'app':'login'); },
      setSpeed: (v)=>{ opts.speed = clamp(+v||1, .4, 2); },
      setDensity: (v)=>{ opts.density = clamp(+v||1, .5, 2); makeBlobs(); }
    };
    host.breeze = api;
    window.circuitBgPulse      = (x,y)=> api.pulse(x,y);
    window.circuitBgSetMode    = (m)=> api.setMode(m);
    window.circuitBgSetSpeed   = (v)=> api.setSpeed(v);
    window.circuitBgSetDensity = (v)=> api.setDensity(v);
  }

  function boot(){
    document.querySelectorAll('#techbg-host, .circuit-bg-host').forEach(initHost);
  }
  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
