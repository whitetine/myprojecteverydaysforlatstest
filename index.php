<!DOCTYPE html>
<html lang="zh-Hant">
<?php
session_start();
$_SESSION = [];
?>
<head name="app-base" content="/myprojecteverydaysforlasttest/">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>專題日總彙-登入</title>

  <!-- Bootstrap 5 CSS（你若有自己的就換掉） -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Font Awesome（給眼睛 icon 用） -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- 你的登入頁樣式 -->
  <link rel="stylesheet" href="css/login.css?v=<?= time() ?>" />
</head>

<body id="indexbody">
  <!-- 背景 -->
  <div id="techbg-host"
       class="position-fixed top-0 start-0 w-100 h-100"
       data-mode="login" data-speed="1.12" data-density="1.35"
       data-contrast="bold"
       style="z-index:0; pointer-events:none;"></div>

  <!-- Vue 掛載點 -->
  <div id="app"></div>

  <!-- 先載函式庫，再載你的腳本（順序不能錯） -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- 你的背景與登入邏輯 -->
  <script src="js/breeze-ink-bg.js"></script>
  <script src="js/login.js?v=<?= time() ?>"></script>
</body>
</html>
