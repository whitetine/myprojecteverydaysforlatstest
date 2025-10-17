<?php
session_start();
require '../includes/pdo.php';
date_default_timezone_set('Asia/Taipei');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* 啟用中的團隊 */
$teams = $conn->query("
  SELECT team_ID,
         COALESCE(team_project_name, CONCAT('團隊#', team_ID)) AS team_name
  FROM teamdata
  WHERE team_status = 1
  ORDER BY team_ID
")->fetchAll(PDO::FETCH_ASSOC);

/* 老師清單：非學生 + 啟用，去重；科辦排最前 */
$sqlTeacher = "
  SELECT u.u_ID, u.u_name,
         MAX(CASE WHEN ur.role_ID = 2 THEN 1 ELSE 0 END) AS is_keban
  FROM userdata u
  JOIN userrolesdata ur ON ur.u_ID = u.u_ID
  WHERE u.u_status = 1
    AND ur.user_role_status = 1
    AND ur.role_ID <> 6
  GROUP BY u.u_ID, u.u_name
  ORDER BY is_keban DESC, u.u_name
";
$teachers = $conn->query($sqlTeacher)->fetchAll(PDO::FETCH_ASSOC);

/* 預設老師：第一位科辦 */
$defaultTeacher = '';
foreach ($teachers as $t) {
  if ((int)$t['is_keban'] === 1) { $defaultTeacher = $t['u_ID']; break; }
}
?>
<!-- <style>:root {
  --brand: #31d3d7;
  --ink: #0a3f42;
  --muted: #6b7a7a;
  --ring: #bfeff0;
}

body {
  font-family: system-ui, 'Noto Sans TC', Arial, sans-serif;
}

.page-title {
  background: linear-gradient(135deg, var(--brand), #a8e6e7); /* 漸層背景 */
  color: #052b2e;
  border-radius: 20px;
  padding: 28px 24px;
  text-align: center;
  font-weight: 800;
  letter-spacing: 0.08em;
  font-size: clamp(22px, 3.4vw, 38px);
  margin: 26px 0 18px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.page-title:hover {
  transform: scale(1.02);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.helper-bar {
  background: #eefcfc;
  border: 1.5px solid var(--ring);
  border-radius: 16px;
  padding: 12px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
}

.helper-chip {
  padding: 12px 18px;
  background: #fff;
  border: 1.5px solid var(--ring);
  border-radius: 12px;
  font-weight: 700;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: background 0.2s, transform 0.2s;
}

.helper-chip:hover {
  background: var(--ring);
  transform: scale(1.05);
}

.section-title {
  font-weight: 800;
  color: var(--ink);
  margin: 18px 0 8px;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.card-row {
  position: relative;
  border: 1.5px solid var(--ring);
  border-radius: 14px;
  padding: 14px;
  background: linear-gradient(180deg, #fff, #f7ffff); /* 漸層背景 */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.2s, transform 0.2s;
}

.card-row:hover {
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.card-row + .card-row {
  margin-top: 12px;
}

.form-label {
  font-weight: 700;
  color: var(--ink);
}

textarea.form-control {
  min-height: 4.8em;
  max-height: 6.2em;
  resize: none;
  overflow-y: auto;
  border-color: var(--ring);
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 10px;
  border: 2px dashed var(--brand);
  color: var(--ink);
  background: #f7ffff;
  padding: 12px 16px;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.2s;
}

.btn-add:hover {
  background: var(--brand);
  color: #fff;
  transform: scale(1.05);
}

.btn-add .plus {
  font-size: 22px;
  line-height: 1;
}

.btn-link-danger {
  color: #c21a32;
}

.btn-link-danger:hover {
  color: #a41227;
}

.row-actions {
  position: absolute;
  right: 10px;
  top: 8px;
}

.fixed-actions {
  position: sticky;
  bottom: 0;
  background: #fff;
  border-top: 1px solid #eee;
  padding: 12px 0;
  margin-top: 8px;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
}

@media (min-width: 992px) {
  .grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
}</style> -->
<!-- 引用外部 CSS -->
<link rel="stylesheet" href="css/suggest.css">

<div class="page-title">期中期末建議</div>

<!-- 上方團隊快速檢視 -->
<div class="helper-bar mb-3">
  <?php foreach($teams as $t): ?>
    <div class="helper-chip" title="team_ID: <?=htmlspecialchars($t['team_ID'])?>">
      <?= htmlspecialchars($t['team_name']) ?>
    </div>
  <?php endforeach; ?>
</div>

<div class="d-flex align-items-center">
  <div class="section-title mb-2 flex-grow-1">新增評論</div>
</div>

<form id="bulkForm" class="vstack gap-2">
  <!-- 表頭（桌機顯示；手機不顯示以免擠） -->
  <div class="d-none d-lg-grid grid-2col mb-1">
    <div class="form-label">老師 / 評論內容</div>
    <div class="form-label">團隊</div>
  </div>

  <div id="rows"></div>

  <div class="mt-2">
    <button type="button" id="btnAdd" class="btn-add">
      <span class="plus">＋</span> <span>按「＋」可新增多筆評論</span>
    </button>
  </div>

  <div class="fixed-actions d-flex gap-2">
    <button type="submit" class="btn btn-primary">儲存全部</button>
    <button type="button" id="btnClear" class="btn btn-outline-secondary">清空</button>
  </div>
</form>

<!-- 引用外部 JS -->
<script>

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
</script>