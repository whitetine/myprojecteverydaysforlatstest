<?php
session_start();
require __DIR__ . "/includes/pdo.php";
if (!isset($_SESSION['u_ID'])) {
  echo "<script>alert('請先登入!');location.href='index.php';</script>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <!-- 讓所有相對路徑以專案根為基準 -->
  <base href="/projecteveryday/myprojecteverydaysforlatstest/">

  <title>專題日總彙 · Light Blue</title>

  <!-- Light Blue / Bootstrap 4 的成品 CSS -->
  <link rel="stylesheet" href="assets/light-blue/css/application.min.css?v=20251107">

  <!-- 必要外掛（走 CDN，避免本機 node_modules 路徑） -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.green.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css"/>

  <!-- 你的客製（可留空檔） -->
  <link rel="stylesheet" href="css/custom.css?v=20251107"/>
  <style>
    /* 確保內容層級在背景之上 */
    .page, .page-content, .content-inner { position:relative; z-index:2; }
    #techbg-host { z-index:0; pointer-events:none; }
    body.theme-default { background:#0f1a36; } /* Demo 深藍底 */
  </style>
</head>
<body class="theme-default">

  <!-- 背景（可要可不要） -->
  <div id="techbg-host" class="position-fixed top-0 start-0 w-100 h-100"
       data-mode="app" data-speed="1.15" data-density="1.2"></div>

  <div class="page">
    <!-- 頂部（先略過 Demo 的複雜 navbar，可之後再做） -->
    <!-- <header class="page-header">...</header> -->
      <?php include __DIR__ . "/nav.php"; ?>

    <!-- 關鍵：Light Blue 的兩欄骨架 -->
    <div class="page-content d-flex align-items-stretch">
      <!-- 側欄（用 LB 結構；先用極簡版，之後再換成你的動態項目） -->
      <?php include __DIR__ . "/includes/sidebar.php"; ?>

      <!-- 內容區：給 AJAX .load() -->
      <div class="content-inner w-100">
        <div class="container-fluid" id="content"><!-- via .load() --></div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . "/modules/notify.php"; ?>

  <!-- 正確載入順序（Bootstrap 4 生態鏈） -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>

  <!-- 其他外掛 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>

  <!-- Light Blue 主腳本（依賴 jQuery/Bootstrap 4，所以放在它們之後） -->
  <script src="assets/light-blue/js/app.js?v=20251107"></script>

  <!-- 你的 SPA 載入邏輯（放最後） -->
  <script>
    $(function () {
      const $content = $("#content");

      // 讓側欄的 .ajax-link 可以無刷新換頁
      $(document).on("click", ".ajax-link", function (e) {
        e.preventDefault();
        const page = $(this).attr("href");
        if (page && page.endsWith(".php")) {
          $content.load(page, function () {
            if (window.initPageScript) initPageScript(); // 你的頁內初始化
          });
        }
      });

      // 預設載入一頁（用你專案真的存在的頁）
      $content.load("pages/apply.php");
    });
  </script>

  <!-- 你的自訂 JS（若有） -->
  <script src="js/app.js?v=20251107"></script>
</body>
</html>
