const TEACHERS = <?php echo json_encode($teachers, JSON_UNESCAPED_UNICODE); ?>;
const TEAMS    = <?php echo json_encode($teams,    JSON_UNESCAPED_UNICODE); ?>;
const DEFAULT_TEACHER = "<?php echo $defaultTeacher; ?>";

const rowsBox = document.getElementById('rows');
const btnAdd  = document.getElementById('btnAdd');
const btnClear= document.getElementById('btnClear');
const form    = document.getElementById('bulkForm');

function makeSelect(list, vKey, tKey, placeholder, name, def=''){
  const sel = document.createElement('select');
  sel.className = 'form-select'; sel.name = name; sel.required = true;
  const p = document.createElement('option'); p.value=''; p.textContent=placeholder; sel.appendChild(p);
  for (const it of list){
    const o = document.createElement('option');
    o.value = it[vKey];
    o.textContent = it[tKey] || it[vKey];
    if (def && def === String(it[vKey])) o.selected = true;
    sel.appendChild(o);
  }
  return sel;
}

function makeRow(){
  const wrap = document.createElement('div');
  wrap.className = 'card-row shadow-sm';

  const del = document.createElement('button');
  del.type='button'; del.className='btn btn-link btn-sm btn-link-danger row-actions';
  del.innerHTML='✕';
  del.title='刪除此筆';
  del.addEventListener('click', ()=> wrap.remove());
  wrap.appendChild(del);

  const grid = document.createElement('div');
  grid.className = 'grid-2col';

  const left = document.createElement('div');
  const g1 = document.createElement('div'); g1.className='mb-2';
  const lbl1 = document.createElement('label'); lbl1.className='form-label'; lbl1.textContent='老師';
  const selTeacher = makeSelect(TEACHERS,'u_ID','u_name','選擇老師','u_ID[]',DEFAULT_TEACHER);
  g1.append(lbl1, selTeacher);

  const g2 = document.createElement('div');
  const lbl2 = document.createElement('label'); lbl2.className='form-label mt-1'; lbl2.textContent='評論內容';
  const ta = document.createElement('textarea'); ta.className='form-control'; ta.placeholder='請輸入評論內容'; ta.name='suggest_comment[]';
  g2.append(lbl2, ta);

  left.append(g1, g2);

  const right = document.createElement('div');
  const g3 = document.createElement('div');
  const lbl3 = document.createElement('label'); lbl3.className='form-label'; lbl3.textContent='團隊';
  const selTeam = makeSelect(TEAMS,'team_ID','team_name','選擇團隊','team_ID[]');
  g3.append(lbl3, selTeam);
  right.append(g3);

  grid.append(left, right);
  wrap.appendChild(grid);
  return wrap;
}

btnAdd.addEventListener('click', ()=> rowsBox.appendChild(makeRow()));
btnClear.addEventListener('click', ()=>{ rowsBox.innerHTML=''; rowsBox.appendChild(makeRow()); });
rowsBox.appendChild(makeRow());

form.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const payload=[];
  for (const r of rowsBox.querySelectorAll('.card-row')){
    const u=r.querySelector('select[name="u_ID[]"]').value.trim();
    const t=r.querySelector('select[name="team_ID[]"]').value.trim();
    const c=r.querySelector('textarea[name="suggest_comment[]"]').value.trim();
    if (!u && !t && !c) continue;
    if (!u || !t){ alert('每筆至少要選「老師」與「團隊」。'); return; }
    payload.push({u_ID:u, team_ID:t, suggest_comment:c||''});
  }
  if (!payload.length){ alert('沒有可送出的資料'); return; }

  try{
    const res = await fetch('suggest_api.php?action=bulk_insert', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({items: payload})
    });
    const js = await res.json();
    if (res.ok && js.ok){
      alert(`新增成功，共 ${js.inserted} 筆。`);
      rowsBox.innerHTML=''; rowsBox.appendChild(makeRow());
    }else{
      alert('新增失敗：'+(js.error||res.status));
    }
  }catch(err){ alert('連線失敗：'+err); }
});