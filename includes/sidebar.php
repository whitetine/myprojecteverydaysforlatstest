<?php
$user_ID   = $_SESSION['u_ID'] ?? null;
$user_img  = $_SESSION['u_img'] ?? null;
$user_name = $_SESSION['u_name'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;
$role_ID   = $_SESSION['role_ID'] ?? null;

if (!$user_ID) {
  echo "<script>alert('請先登入!');location.href='index.php';</script>";
  exit;
}
?>

<aside class="page-sidebar">
  <!-- 頂部使用者資訊卡 -->
  <div class="sidebar-header d-flex align-items-center gap-2 p-3 border-bottom">
    <?php if (!empty($user_img)): ?>
      <img src="headshot/<?= htmlspecialchars($user_img) ?>" width="40" height="40"
           class="rounded-circle shadow-sm" style="object-fit:cover;">
    <?php else: ?>
      <img src="https://cdn-icons-png.flaticon.com/512/1144/1144760.png" width="40" height="40"
           class="rounded-circle shadow-sm" style="object-fit:cover;" alt="User">
    <?php endif; ?>
    <div>
      <div class="fw-semibold"><?= htmlspecialchars($user_name ?: '未登入') ?></div>
      <small class="text-muted"><?= htmlspecialchars($role_name ?: '無') ?></small>
    </div>
    <button class="btn btn-sm btn-light ms-auto" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="fa-solid fa-ellipsis-vertical"></i>
    </button>

    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-2">
      <li class="px-3 small text-muted">
        <?= htmlspecialchars($_SESSION['u_gmail'] ?? $_SESSION['u_ID']) ?>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item ajax-link" href="pages/user_profile.php">
        <i class="fa-solid fa-address-card me-2"></i> 個人資料</a></li>
      <li><a class="dropdown-item ajax-link" href="pages/admin_notify.php">
        <i class="fa-solid fa-bell me-2"></i> 公告管理</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="index.php">
        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> 登出</a></li>
    </ul>
  </div>

  <!-- 功能選單 -->
  <div class="sidebar-nav p-2">
    <?php if ($role_ID == 2): ?>
      <a class="nav-link ajax-link" href="pages/admin_usermanage.php">
        <i class="fa-solid fa-user me-2"></i>帳號管理</a>
      <a class="nav-link ajax-link" href="pages/group_manage.php">
        <i class="fa-solid fa-table-cells me-2"></i>類組管理</a>
      <a class="nav-link ajax-link" href="pages/file.php">
        <i class="fa-solid fa-folder me-2"></i>文件管理(更新)</a>
      <a class="nav-link ajax-link" href="pages/apply.php">
        <i class="fa-solid fa-file-lines me-2"></i>申請文件上傳</a>
      <a class="nav-link ajax-link" href="pages/teacher_review_status.php">
        <i class="fa-solid fa-star-half-alt me-2"></i>互評(status)</a>
      <a class="nav-link ajax-link" href="pages/work_draft.php">
        <i class="fa-solid fa-file-lines me-2"></i>work_draft</a>
      <a class="nav-link ajax-link" href="pages/work_form.php">
        <i class="fa-solid fa-pen-to-square me-2"></i>work_form</a>
      <a class="nav-link ajax-link" href="pages/apply_preview.php">
        <i class="fa-solid fa-pen-to-square me-2"></i>apply_preview</a>
      <a class="nav-link ajax-link" href="pages/suggest.php">
        <i class="fa-solid fa-lightbulb me-2"></i>suggest</a>

    <?php elseif ($role_ID == 6): ?>
      <a class="nav-link ajax-link" href="pages/apply.php">
        <i class="fa-solid fa-file-lines me-2"></i>文件管理</a>
      <a class="nav-link ajax-link" href="pages/work_form.php">
        <i class="fa-solid fa-pen-to-square me-2"></i>work_form</a>

    <?php elseif ($role_ID == 4): ?>
      <a class="nav-link ajax-link" href="pages/teacher_review_status.php">
        <i class="fa-solid fa-star-half-alt me-2"></i>互評(status)</a>
      <a class="nav-link ajax-link" href="pages/teacher_review_detail.php">
        <i class="fa-solid fa-file-lines me-2"></i>互評(detail)</a>
    <?php endif; ?>
  </div>
</aside>
