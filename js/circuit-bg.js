
(function(){
  if (window.__circuitBgLoaded) return; // 防重複載入
  window.__circuitBgLoaded = true;

  // ===== 讀取 Bootstrap / Bootswatch 變數 =====
  function cssVars() {
    const root = getComputedStyle(document.documentElement);
    return {
      bg  : root.getPropertyValue('--bs-body-bg').trim()    || '#0b1220',
      pri : root.getPropertyValue('--bs-primary').trim()     || '#6cf',
      info: root.getPropertyValue('--bs-info').trim()        || '#0df',
      body: root.getPropertyValue('--bs-body-color').trim()  || '#e9eef6'
    };
  }

  // ===== 初始化（可找多個 .circuit-bg-host；每頁放一個就好）=====
  function initHost(host){
    if (!host || host.__inited) return;
    host.__inited = true;

    const opts = {
      mode: (host.dataset.mode || 'login'),            // login | app
      interactive: host.dataset.interactive !== 'false',
      speed: parseFloat(host.dataset.speed || 1.1),    // 0.5 ~ 2
      density: parseFloat(host.dataset.density || 1.0),
      lineWidth: parseFloat(host.dataset.lineWidth || 2)
    };

    const c = document.createElement('canvas');
    c.className = 'circuit-bg-canvas';
    host.appendChild(c);
    const ctx = c.getContext('2d');

    let W=0,H=0, paths=[], pulses=[], t=0, paused=false, mouse={x:-1,y:-1,active:false};

    function fit(){
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      W = host.clientWidth; H = host.clientHeight;
      c.width = W*dpr; c.height = H*dpr;
      c.style.width = W+'px'; c.style.height = H+'px';
      ctx.setTransform(dpr,0,0,dpr,0,0);
    }

    function makePaths(){
      const count = Math.max(10, Math.floor(14 * opts.density));
      const stepX = 24, stepY = 16;
      paths = [];
      for (let i=0;i<count;i++){
        let x = (Math.random()*W*0.55)|0, y = (Math.random()*H)|0;
        const segs = 8 + (Math.random()*6|0);
        const pts = [[x,y]];
        for (let s=0;s<segs;s++){
          x += Math.random()<0.5 ? stepX : -stepX;
          y += [-stepY,0,stepY][Math.random()*3|0];
          x = Math.max(0,Math.min(W,x));
          y = Math.max(0,Math.min(H,y));
          pts.push([x,y]);
        }
        let len=0, seglen=[];
        for (let k=0;k<pts.length-1;k++){
          const dx=pts[k+1][0]-pts[k][0], dy=pts[k+1][1]-pts[k][1];
          const ll=Math.hypot(dx,dy); seglen.push(ll); len+=ll;
        }
        paths.push({pts, len, seglen});
      }
    }

    function drawBackground(){
      const { bg } = cssVars();
      ctx.fillStyle = opts.mode==='login' ? 'rgba(8,12,22,1)' : (bg || '#060a14');
      ctx.fillRect(0,0,W,H);
    }

    function drawPaths(){
      for (let i=0;i<paths.length;i++){
        const p = paths[i];
        const glow = 180 + 60*Math.sin((t*0.04 + i*0.6));
        ctx.strokeStyle = `rgba(${glow|0},${glow|0},255,${opts.mode==='login'?0.55:0.75})`;
        ctx.lineWidth = opts.lineWidth;
        ctx.beginPath(); ctx.moveTo(p.pts[0][0], p.pts[0][1]);
        for (let k=1;k<p.pts.length;k++) ctx.lineTo(p.pts[k][0], p.pts[k][1]);
        ctx.stroke();

        // 滑鼠靠近加亮
        if (opts.interactive && mouse.active){
          const near = nearestSegmentDist(p, mouse.x, mouse.y);
          const a = Math.max(0, 1 - near/60);
          if (a>0){
            ctx.strokeStyle = `rgba(140,220,255,${0.35*a})`;
            ctx.lineWidth = opts.lineWidth+1;
            ctx.beginPath(); ctx.moveTo(p.pts[0][0], p.pts[0][1]);
            for (let k=1;k<p.pts.length;k++) ctx.lineTo(p.pts[k][0], p.pts[k][1]);
            ctx.stroke();
          }
        }
      }
    }

    function nearestSegmentDist(p, x, y){
      let best=1e9;
      for (let i=0;i<p.pts.length-1;i++){
        const [x1,y1]=p.pts[i], [x2,y2]=p.pts[i+1];
        const vx=x2-x1, vy=y2-y1, wx=x-x1, wy=y-y1;
        const c1=vx*wx+vy*wy, c2=vx*vx+vy*vy;
        const b = c2 ? Math.max(0,Math.min(1,c1/c2)) : 0;
        const px=x1+b*vx, py=y1+b*vy;
        const d=Math.hypot(px-x,py-y);
        if (d<best) best=d;
      }
      return best;
    }

    function stepPulses(){
      const sp = (opts.speed||1) * (opts.mode==='login'?0.8:1.2);
      for (let i=pulses.length-1;i>=0;i--){
        const pu = pulses[i];
        pu.s += pu.v * sp;
        if (pu.s > pu.path.len) pulses.splice(i,1);
      }
    }

    function drawPulses(){
      const { info } = cssVars();
      for (const pu of pulses){
        let s=pu.s, x=pu.path.pts[0][0], y=pu.path.pts[0][1];
        for (let k=0;k<pu.path.seglen.length;k++){
          const seg=pu.path.seglen[k];
          const [x1,y1]=pu.path.pts[k], [x2,y2]=pu.path.pts[k+1];
          if (s<=seg){ const r=s/seg; x=x1+(x2-x1)*r; y=y1+(y2-y1)*r; break; }
          s-=seg;
        }
        ctx.fillStyle='rgba(255,255,255,0.96)'; ctx.beginPath(); ctx.arc(x,y,3,0,Math.PI*2); ctx.fill();
        ctx.strokeStyle=info || '#0df'; ctx.lineWidth=2; ctx.beginPath(); ctx.arc(x,y,5,0,Math.PI*2); ctx.stroke();
      }
    }

    function pulseAt(x,y){
      let best={path:null,s:0,d:1e9};
      for (const p of paths){
        let acc=0;
        for (let i=0;i<p.pts.length-1;i++){
          const a=p.pts[i], b=p.pts[i+1], vx=b[0]-a[0], vy=b[1]-a[1];
          const wx=x-a[0], wy=y-a[1], c1=vx*wx+vy*wy, c2=vx*vx+vy*vy;
          const u=c2?Math.max(0,Math.min(1,c1/c2)):0, px=a[0]+u*vx, py=a[1]+u*vy;
          const d=Math.hypot(px-x,py-y);
          if (d<best.d) best={path:p, s: acc + u*Math.hypot(vx,vy), d};
          acc += Math.hypot(vx,vy);
        }
      }
      if (best.path) pulses.push({ path:best.path, s:best.s, v: 2 + Math.random()*1.2 });
    }
    function pulseRandom(n=1){ for(let i=0;i<n;i++){ const p=paths[Math.random()*paths.length|0]; pulses.push({path:p,s:Math.random()*p.len*.8,v:2+Math.random()*1.2}); } }

    function loop(){
      if (paused){ requestAnimationFrame(loop); return; }
      t++;
      drawBackground(); drawPaths(); stepPulses(); drawPulses();
      requestAnimationFrame(loop);
    }

    // 事件
    function onMove(e){ mouse.x=e.clientX; mouse.y=e.clientY; mouse.active=true; }
    function onClick(e){ if (opts.interactive) pulseAt(e.clientX, e.clientY); }
    function onVisibility(){ paused=document.hidden; }
    function onResize(){ fit(); makePaths(); }

    fit(); makePaths(); loop();
    window.addEventListener('mousemove', onMove, {passive:true});
    window.addEventListener('click', onClick, {passive:true});
    window.addEventListener('resize', onResize);
    document.addEventListener('visibilitychange', onVisibility);

    // 暴露控制 API（全站可用）
    host.circuit = {
      pulse: (x,y)=> (typeof x==='number'&&typeof y==='number') ? pulseAt(x,y) : pulseRandom(1),
      pulseRandom: (n)=> pulseRandom(n||1),
      setMode: (m)=> { opts.mode = (m==='app'?'app':'login'); },
      setSpeed: (v)=> { opts.speed = Math.max(0.3, Math.min(3, +v||1)); },
      setDensity: (v)=> { opts.density = Math.max(0.4, Math.min(2, +v||1)); makePaths(); },
      destroy: ()=> {
        window.removeEventListener('mousemove', onMove);
        window.removeEventListener('click', onClick);
        window.removeEventListener('resize', onResize);
        document.removeEventListener('visibilitychange', onVisibility);
        host.innerHTML = '';
        host.__inited = false;
      }
    };

    // 也掛幾個全域別名，方便子頁直接叫
    window.circuitBgPulse       = (x,y)=> host.circuit.pulse(x,y);
    window.circuitBgPulseRandom = (n)=> host.circuit.pulseRandom(n);
    window.circuitBgSetMode     = (m)=> host.circuit.setMode(m);
    window.circuitBgSetSpeed    = (v)=> host.circuit.setSpeed(v);
    window.circuitBgSetDensity  = (v)=> host.circuit.setDensity(v);
  }

  // 自動尋找容器並初始化
  function boot(){
    document.querySelectorAll('#techbg-host, .circuit-bg-host').forEach(initHost);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else boot();

})();
