<?php
session_start();
require '../includes/pdo.php';

// --- 通過/退件更新 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['apply_ID'] ?? null;
  $action = $_POST['action'] ?? null;
  $isAjax = ($_POST['ajax'] ?? '') === '1';

  if ($id && in_array($action, ['approve', 'reject'])) {
    $status = $action === 'approve' ? 3 : 2;  // 3=已通過, 2=退件
    $stmt = $pdo->prepare("UPDATE apply SET apply_status=?, apply_b_u_ID=?, approved_d=NOW() WHERE apply_ID=?");
    $stmt->execute([$status, $_SESSION['u_ID'] ?? 0, $id]);

    if ($isAjax) {
      echo json_encode(['ok' => true, 'new_status' => $status, 'status_text' => $status == 3 ? '已通過' : '退件']);
      exit;
    }
    header("Location: apply_preview.php");
    exit;
  }
  if ($isAjax) {
    echo json_encode(['ok' => false]);
    exit;
  }
}

try {
  $sql = "SELECT a.*, f.file_ID f_id, f.file_name 
                     FROM applydata a LEFT JOIN filedata f ON a.file_ID=f.file_ID
                     ORDER BY a.apply_status ASC, a.apply_created_d DESC";
  $row = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

  $fileTypes = $conn->query("SELECT file_ID, file_name FROM filedata")->fetchAll();
} catch (PDOException $e) {
  http_response_code(500);
  die("DB error:" . htmlspecialchars($e->getMessage()));
}

// --- 撈表單類型 (下拉式&篩選) ---
?>

<meta charset="UTF-8">
<title>申請審核列表</title>
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<!-- <style>
  

    .filters {
      display: flex;
      gap: 10px;
      margin-bottom: 10px
    }

    .filters input,
    .filters select {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px
    }

    table {
      width: 100%;
      border-collapse: collapse
    }

  

    th {
      background: #f5f5f5
    }

    tr:nth-child(even) {
      background: #fafafa
    }

    img.preview {
      max-width: 80px;
      cursor: pointer;
      border-radius: 4px
    }

    button {
      padding: 5px 10px;
      border: none;
      border-radius: 4px;
      color: #fff;
      cursor: pointer
    }

    .ok {
      background: #28a745
    }

    .no {
      background: #dc3545
    }

    /* --- 圖片放大 modal --- */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      justify-content: center;
      align-items: center;
      background: rgba(0, 0, 0, .8)
    }

    .modal img {
      max-width: 85%;
      max-height: 85%
    }
  </style> -->
<style>
  .fixed-thumb:hover {
    transform: scale(1.05);
    transition: transform 0.2s;
  }
</style>



<header>
  <h2>申請審核列表</h2>
</header>


<div class="page">
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">查詢</h5>

    </div>
    <div class="card-body">

      <div class="container">
        <!-- 篩選工具列 -->
        <div class="filters d-flex align-items-center gap-2 flex-nowrap">
          <input
            id="searchBox"
            class="form-control flex-grow-1 min-w-0"
            type="search"
            placeholder="🔍 搜尋文件或申請人..." />

          <select id="statusFilter" class="form-select flex-shrink-0" style="width:10%;">
            <option value="all">全部狀態</option>
            <option>待審核</option>
            <option>已通過</option>
            <option>退件</option>
          </select>

          <select id="typeFilter" class="form-select flex-shrink-0" style="width:16%;">
            <option value="all">全部表單類型</option>
            <?php foreach ($fileTypes as $f): ?>
              <option value="<?= htmlspecialchars($f['file_ID'], ENT_QUOTES) ?>">
                <?= htmlspecialchars($f['file_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
      </div>

    </div>
    
  </div>
<div class="card mb-4">
  <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle mb-0 bg-white text-center" id="applyTable">
              <thead>
                <tr>
                  <th>表單類型</th>
                  <th>文件名稱</th>
                  <th>申請人</th>
                  <th>時間</th>
                  <th>檔案</th>
                  <th>狀態</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($row as $r): ?>
                    <td><?= $r['apply_ID'] ?></td>
                    <td><?= htmlspecialchars($r['file_name']) ?></td>
                    <td><?= htmlspecialchars($r['apply_other']) ?></td>
                    <td><?= $r['apply_created_d'] ?></td>
                    <td>
                      <?php if (preg_match('/\.(jpg|jpeg|png)$/i', $r['apply_url'])): ?>
                        <img src="<?= htmlspecialchars($r['apply_url']) ?>" class="preview fixed-thumb" style="width: 100px; height: 100px; object-fit: cover; border-radius: 6px; cursor: pointer;" onclick="showModal(this.src)">
                      <?php elseif ($r['apply_url']): ?>
                        <a href="<?= htmlspecialchars($r['apply_url']) ?>" target="_blank">檔案</a>
                        <?php else: ?>無<?php endif; ?>
                    </td>
                    <td class="status-cell"><?= $r['apply_status'] == 1 ? '待審核' : ($r['apply_status'] == 2 ? '退件' : '已通過') ?></td>
                    <td class="op-cell">
                      <?php if ($r['apply_status'] == 1): ?>
                        <button class="btn btn-success" onclick="updateStatus(<?= $r['apply_ID'] ?>,'approve',this)">通過</button>
                        <button class="btn btn-danger" onclick="updateStatus(<?= $r['apply_ID'] ?>,'reject',this)">退件</button>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        </div>
</div>



<!-- 圖片放大 modal -->
<div id="imgModal" class="modal" onclick="closeModal()"><img id="modalImg"></div>


<script>
  // 圖片放大
  function showModal(src) {
    document.getElementById('modalImg').src = src;
    document.getElementById('imgModal').style.display = 'flex'
  }

  function closeModal() {
    document.getElementById('imgModal').style.display = 'none'
  }

  // 前端搜尋&篩選
  function filterTable() {
    const kw = document.getElementById('searchBox').value.toLowerCase();
    const st = document.getElementById('statusFilter').value,
      tp = document.getElementById('typeFilter').value;
    document.querySelectorAll('#applyTable tbody tr').forEach(tr => {
      const text = tr.innerText.toLowerCase(),
        s = tr.querySelector('.status-cell').innerText,
        fid = tr.dataset.fileid;
      tr.style.display = (text.includes(kw) && (st === 'all' || s === st) && (tp === 'all' || fid === tp)) ? '' : 'none';
    });
  }
  ['searchBox', 'statusFilter', 'typeFilter'].forEach(id => document.getElementById(id).addEventListener('input', filterTable));
  window.addEventListener('DOMContentLoaded', filterTable);

  // 通過/退件：AJAX 更新
  function updateStatus(id, action, btn) {
    const tr = btn.closest('tr'),
      name = tr.cells[2].innerText;
    Swal.fire({
      title: '確認操作',
      text: (action === 'approve' ? `確定將「${name}」通過？` : `確定將「${name}」退件？`),
      icon: action === 'approve' ? 'question' : 'warning',
      showCancelButton: true
    }).then(r => {
      if (!r.isConfirmed) return;
      fetch('apply_preview.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `apply_ID=${id}&action=${action}&ajax=1`
        })
        .then(res => res.json()).then(data => {
          if (data.ok) {
            tr.querySelector('.status-cell').innerText = data.status_text;
            tr.querySelector('.op-cell').innerText = '-';
            Swal.fire('成功', `${name}${data.status_text}`, 'success');

            // 更新成功後，重新排序
            reorderTable();
          } else Swal.fire('失敗', '更新失敗', 'error');
        }).catch(() => Swal.fire('錯誤', '無法連線', 'error'));
    });
  }

  // 讓待審核永遠在最上方
  function reorderTable() {
    const tbody = document.querySelector('#applyTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
      const statusOrder = {
        '待審核': 0,
        '已通過': 1,
        '退件': 2
      };
      const sa = a.querySelector('.status-cell').innerText.trim();
      const sb = b.querySelector('.status-cell').innerText.trim();

      if (statusOrder[sa] !== statusOrder[sb]) {
        return statusOrder[sa] - statusOrder[sb];
      }

      const ta = new Date(a.cells[4].innerText);
      const tb = new Date(b.cells[4].innerText);
      return tb - ta;
    });

    rows.forEach(r => tbody.appendChild(r));
  }
</script>