    <?php include "head.php" ?>

    <?php
    session_start();
    include("includes/pdo.php");

    if (!isset($_SESSION['u_ID'])) {
      echo "<script>alert('請先登入!');location.href='index.php';</script>";
      exit;
    }
    $user_name = $_SESSION['user_name'] ?? '未登入';
    $role_name = $_SESSION['role_name'] ?? '無';
    ?>
    <!DOCTYPE html>
    <html lang="zh-Hant">

    <head>
      <meta charset="UTF-8">
      <div id="techbg-host"
     class="position-fixed top-0 start-0 w-100 h-100"
     data-mode="app" data-speed="1.15" data-density="1.2"
     style="z-index:0; pointer-events:none;"></div>

      <title>專題日總彙 - 首頁</title>
      <style>

      </style>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    </head>

    <body class="sb-nav-fixed">
      <?php include "nav.php"; ?>


      <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
          <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <?php include "sidebar.php"; ?>
          </nav>
        </div>
        <main id="content" class="container-fluid py-4"><!-- .load() 塞子頁面 --></main>


      </div>
      <!-- 通知 Modal -->
      <div class="modal fade" id="bell_box">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">通知中心<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button></div>

            <div class="modal-body">
              <p>📌 7/10 上傳檔案截止</p>
              <p>📌 7/15 提交報表</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 載入 JS -->
      <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.lordicon.com/lordicon.js"></script> -->
    <!-- head.php：全域只載一次 -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  .preview-pane { width:100%; max-width:640px; margin:10px auto 0; }
  .preview-box  { margin:0 auto; }
  .preview-img  { width:100%; height:auto; object-fit:contain; border:1px solid #ddd; border-radius:8px; display:block; }
</style>

<!-- <script>
  // 目前你「還沒把 storage 合併到 api.php」→ 先用獨立 upload.php
  window.API_UPLOAD_URL = 'pages/upload.php'; // ←依實際路徑調整
  // 這條是「撈啟用中的表單」列表 API（看你要用哪個 case，見下節）
  window.API_LIST_URL   = 'api.php?do=listActiveFiles';

  // 檔名 apply.php → renderApplyPage（符合你 main.js 的推導規則）
  window.renderApplyPage = function (mountSel) {
    const mountEl = document.querySelector(mountSel) || document.querySelector('#app');
    if (!mountEl) return;

    const { createApp } = Vue;
    const app = createApp({
      data() {
        return {
          selectedFileID: '',
          selectedFileName: '',
          files: [],
          imagePreview: null,
          applyUser: '',
          applyOther: '',
          previewPercent: 60,
        };
      },
      computed: {
        selectedFileUrl() {
          const f = this.files.find(x => String(x.file_ID) === String(this.selectedFileID));
          return f ? f.file_url : '';
        }
      },
      methods: {
        previewImage(e) {
          const file = e.target.files[0];
          this.imagePreview = (file && file.type.startsWith('image/')) ? URL.createObjectURL(file) : null;
        },
        async submitForm() {
          const fd = new FormData();
          fd.append('file_ID', this.selectedFileID);
          fd.append('apply_user', this.applyUser);
          fd.append('apply_other', this.applyOther);
          const f = this.$refs.applyImage?.files?.[0];
          if (f) fd.append('apply_image', f);

          try {
            const res  = await fetch(window.API_UPLOAD_URL, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
              Swal.fire('成功', '申請已送出！', 'success');
              this.applyUser = ''; this.applyOther = '';
              this.selectedFileID = ''; this.selectedFileName = '';
              if (this.$refs.applyImage) this.$refs.applyImage.value = '';
              this.imagePreview = null;
            } else {
              Swal.fire('失敗', data.message || '發生錯誤', 'error');
            }
          } catch {
            Swal.fire('錯誤', '無法送出申請', 'error');
          }
        }
      },
      mounted() {
        fetch(window.API_LIST_URL)
          .then(r => r.json())
          .then(arr => { if (Array.isArray(arr)) this.files = arr; });
      }
    });

    app.mount(mountEl);
    return app; // 讓你的 main 載入器能記住並在換頁時 unmount
  };
</script>

     <script>
/** ========================
 *  共用設定
 * ======================== */
const CONTENT_SEL = '#content';    // 主內容容器
const BASE_PREFIX = 'pages/';      // 受控子頁的前綴

// 依檔名推導對應的 render 函式：file_storage.php -> renderFileStoragePage
function filenameToRenderFn(filePath) {
  const base = filePath.replace(/^.*\//, '').replace(/\.php.*/, '');
  const pascal = base.replace(/(^|[_-])(\w)/g, (_, __, c) => c.toUpperCase());
  return 'render' + pascal + 'Page';
}

// 顯示/關閉 Loading（若沒載 Swal 就忽略）
function showLoading(title = '載入中…') {
  // Swal.fire({ title, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
}
function hideLoading() { if (window.Swal) Swal.close(); }

/** ========================
 *  你原本的頁面初始化（子頁載入後會呼叫）
 *  – 維持原本事件委派與成功提示
 * ======================== */
function initPageScript() {
  console.log("[diag] initPageScript() run. .toggle-btn =", $(".toggle-btn").length);

  // 事件委派：啟/停用帳號
  $(document).off("click", ".toggle-btn").on("click", ".toggle-btn", function() {
    const acc    = $(this).data("acc");
    const status = $(this).data("status");
    const action = $(this).data("action");

    if (!window.Swal) {
      if (confirm(`是否要${action}此帳號？`)) {
        location.href = `pages/somefunction/toggle_user.php?acc=${acc}&status=${status}`;
      }
      return;
    }

    Swal.fire({
      title: `是否要${action}此帳號？`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: `是，${action}`,
      cancelButtonText: "取消",
      reverseButtons: true
    }).then((r) => {
      if (r.isConfirmed) {
        location.href = `pages/somefunction/toggle_user.php?acc=${acc}&status=${status}`;
      }
    });
  });

  // 成功提示（依 hash）
  const hash = window.location.hash;
  // if (hash.includes("?success=")) {
  //   const [base, paramStr] = hash.split("?");
  //   const params = new URLSearchParams(paramStr);
  //   let msg = params.get("success") === "enable" ? "帳號已成功啟用"
  //           : params.get("success") === "disable" ? "帳號已成功停用" : "";
  //   if (msg) {
  //     if (window.Swal) {
  //       Swal.fire({ icon: 'success', title: '成功', text: msg })
  //           .then(() => window.location.hash = base);
  //     } else {
  //       alert(msg); window.location.hash = base;
  //     }
  //   }
  // }
}

/** ========================
 *  通用子頁載入器
 *  – 由 hash 控制；.ajax-link 只改 hash，不直接 load
 * ======================== */
let currentApp = null; // 可選：若子頁 render 回傳 Vue App，留著方便下頁時 unmount

function loadSubpage(path) {
  // 僅處理我們關心的路徑
  if (!path || !path.startsWith(BASE_PREFIX)) return;

  // 卸載上一個 Vue App（若有提供）
  if (currentApp && typeof currentApp.unmount === 'function') {
    try { currentApp.unmount(); } catch(e) {}
    currentApp = null;
  }

  showLoading();
  $(CONTENT_SEL).load(path, function(response, status, xhr) {
    try {
      if (status === 'error') {
        console.error('Load error:', xhr?.status, xhr?.statusText, 'for', path);
        if (window.Swal) Swal.fire('載入失敗', `無法載入：${path}`, 'error'); else alert('載入失敗');
        return;
      }

      // 子頁 DOM 進來後，先跑共用初始化
      initPageScript();

      // 依檔名呼叫對應的 render 函式（若有）
      const fnName = filenameToRenderFn(path);
      const fn = window[fnName];
      if (typeof fn === 'function') {
        const app = fn(`${CONTENT_SEL} #app`); // 建議子頁都用 <div id="app">
        if (app && typeof app.unmount === 'function') currentApp = app;
      } else {
        console.log(`[diag] 未定義初始化函式：window.${fnName}（若子頁用 Vue/抓資料，請在該頁定義它）`);
      }
    } finally {
      hideLoading();
    }
  });
}

// 側邊欄 AJAX 導頁：只改 hash，實際載入交給 hashchange
$(document).on("click", ".ajax-link", function(e) {
  e.preventDefault();
  const url = $(this).attr("href");
  window.location.hash = url; // 觸發 hashchange → loadSubpage
});

// 監聽 hash 改變
$(window).on("hashchange", function() {
  const path = location.hash.slice(1); // 去掉 #
  loadSubpage(path);
});

// 首次進站
$(function() {
  console.log("[diag] jQuery =", typeof window.jQuery, "SweetAlert2 =", typeof window.Swal);
  // 側邊欄收合
  const btn = document.getElementById("sidebarToggle");
  if (btn) btn.addEventListener("click", (e) => { e.preventDefault(); document.body.classList.toggle("sb-sidenav-toggled"); });

  // 如果網址已有 hash，直接載入；否則維持空白或自行指定預設頁
  const initial = location.hash.slice(1);
  if (initial) {
    loadSubpage(initial);
  } else {
    // 想給預設頁就打開下面這行：
    // window.location.hash = 'pages/admin_usermanage.php';
  }
});
</script>
<script>
  // 全域 API 設定
  window.API_UPLOAD_URL = 'api.php?do=upload';
  window.API_LIST_URL   = 'api.php?do=listActiveFiles'; // 或你現有的 do=get_all_TemplatesFile
</script> -->



<script>
(function () {
  // ========================
  // 全域 API 設定（未合併 storage 前先用 upload.php；日後改下一行即可）
  // ========================
  window.API_UPLOAD_URL = 'pages/upload.php';            // ← 之後合併時改成 'api.php?do=upload'
  window.API_LIST_URL   = 'api.php?do=listActiveFiles';

  // ========================
  // Router 共用
  // ========================
  const CONTENT_SEL = '#content';      // 主內容容器
  const BASE_PREFIX = 'pages/';        // 受控子頁的前綴
  let currentApp = null;               // 若子頁用 Vue，這裡接住以便換頁時 unmount

  // 檔名 -> render 函式名：apply.php -> renderApplyPage
  function filenameToRenderFn(filePath) {
    const base   = filePath.replace(/^.*\//, '').replace(/\.php.*/, '');
    const pascal = base.replace(/(^|[_-])(\w)/g, (_, __, c) => c.toUpperCase());
    return 'render' + pascal + 'Page';
  }

  // ========================
  // 子頁若用到 Vue 的初始化（範例：apply.php）
  // 命名規則：render + 檔名帕斯卡 + Page
  // ========================
  window.renderApplyPage = function (mountSel) {
    const mountEl = document.querySelector(mountSel) || document.querySelector('#app');
    if (!mountEl || !window.Vue) return;
    const { createApp } = Vue;

    const app = createApp({
      data() {
        return {
          selectedFileID: '',
          selectedFileName: '',
          files: [],
          imagePreview: null,
          applyUser: '',
          applyOther: '',
          previewPercent: 60,
        };
      },
      computed: {
        selectedFileUrl() {
          const f = this.files.find(x => String(x.file_ID) === String(this.selectedFileID));
          return f ? f.file_url : '';
        }
      },
      methods: {
        previewImage(e) {
          const file = e.target.files[0];
          this.imagePreview = (file && file.type.startsWith('image/')) ? URL.createObjectURL(file) : null;
        },
        async submitForm() {
          const fd = new FormData();
          fd.append('file_ID', this.selectedFileID);
          fd.append('apply_user', this.applyUser);
          fd.append('apply_other', this.applyOther);
          const f = this.$refs.applyImage?.files?.[0];
          if (f) fd.append('apply_image', f);

          try {
            const res  = await fetch(window.API_UPLOAD_URL, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
              Swal.fire('成功', '申請已送出！', 'success');
              this.applyUser = ''; this.applyOther = '';
              this.selectedFileID = ''; this.selectedFileName = '';
              if (this.$refs.applyImage) this.$refs.applyImage.value = '';
              this.imagePreview = null;
            } else {
              Swal.fire('失敗', data.message || '發生錯誤', 'error');
            }
          } catch {
            Swal.fire('錯誤', '無法送出申請', 'error');
          }
        }
      },
      mounted() {
        fetch(window.API_LIST_URL)
          .then(r => r.json())
          .then(arr => { if (Array.isArray(arr)) this.files = arr; });
      }
    });

    app.mount(mountEl);
    return app; // 讓 Router 能在換頁時 unmount
  };

  // ========================
  // 通用子頁載入器（hash 控制）
  // ========================
  function loadSubpage(path) {
    if (!path || !path.startsWith(BASE_PREFIX)) return;

    // 卸載上一個 Vue App（若有）
    if (currentApp && typeof currentApp.unmount === 'function') {
      try { currentApp.unmount(); } catch(e) {}
      currentApp = null;
    }

    $(CONTENT_SEL).html('<div class="p-5 text-center text-secondary">載入中…</div>');

    $(CONTENT_SEL).load(path, function(response, status, xhr) {
      if (status === 'error') {
        $(CONTENT_SEL).html('<div class="alert alert-danger m-3">載入失敗：' + (xhr?.status||'') + ' ' + (xhr?.statusText||'') + '</div>');
        return;
      }

      // 子頁 DOM 進來後，跑共用初始化
      initPageScript();

      // 依檔名呼叫對應的 render 函式（若有；建議子頁都用 <div id="app">）
      const fnName = filenameToRenderFn(path);
      const fn = window[fnName];
      if (typeof fn === 'function') {
        const app = fn(`${CONTENT_SEL} #app`);
        if (app && typeof app.unmount === 'function') currentApp = app;
      }

      // 選單高亮
      $('.ajax-link').removeClass('active').each(function () {
        if ($(this).attr('href') === path) $(this).addClass('active');
      });
    });
  }

  // ========================
  // 共用初始化（事件委派、SweetAlert 等）
  // ========================
  function initPageScript() {
    // 啟/停用帳號（委派）
    $(document).off("click", ".toggle-btn").on("click", ".toggle-btn", function() {
      const acc    = $(this).data("acc");
      const status = $(this).data("status");
      const action = $(this).data("action");

      if (window.Swal) {
        Swal.fire({
          title: `是否要${action}此帳號？`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: `是，${action}`,
          cancelButtonText: "取消",
          reverseButtons: true
        }).then((r) => {
          if (r.isConfirmed) {
            location.href = `pages/somefunction/toggle_user.php?acc=${acc}&status=${status}`;
          }
        });
      } else {
        if (confirm(`是否要${action}此帳號？`)) {
          location.href = `pages/somefunction/toggle_user.php?acc=${acc}&status=${status}`;
        }
      }
    });
  }

  // 攔截 .ajax-link（含 dropdown 裡的）
  $(document).on("click", "a.ajax-link", function(e) {
    e.preventDefault();
    const url = $(this).attr("href");
    window.location.hash = url; // 觸發 hashchange → loadSubpage
  });

  // 監聽 hash 改變
  window.addEventListener('hashchange', function () {
    loadSubpage(location.hash.slice(1));
  });

  // ========================
  // 使用者卡 dropdown 修正（z-index / transform）+ 次選單滑過展開
  // ========================
  document.addEventListener('DOMContentLoaded', () => {
    // 側邊欄收合
    const btn = document.getElementById("sidebarToggle");
    if (btn) btn.addEventListener("click", (e) => { e.preventDefault(); document.body.classList.toggle("sb-sidenav-toggled"); });

    // 對「使用者卡」的 dropdown 啟用 fixed 策略（避免父層 transform 影響疊層）
    document.querySelectorAll('.user-menu .dropdown-toggle').forEach(btn => {
      if (!window.bootstrap || !bootstrap.Dropdown) return;
      bootstrap.Dropdown.getOrCreateInstance(btn, {
        autoClose: 'outside',
        popperConfig: (defaultConfig) => ({ ...defaultConfig, strategy: 'fixed' })
      });
    });

    // 「說明」子選單：滑過展開 / 移出關閉
    document.querySelectorAll('.dropdown-submenu').forEach(el => {
      el.addEventListener('mouseenter', () => {
        const toggle = el.querySelector('[data-bs-toggle="dropdown"]');
        if (!toggle || !window.bootstrap || !bootstrap.Dropdown) return;
        const dd = bootstrap.Dropdown.getOrCreateInstance(toggle, { autoClose: false, popperConfig: { strategy: 'fixed' }});
        dd.show();
      });
      el.addEventListener('mouseleave', () => {
        const toggle = el.querySelector('[data-bs-toggle="dropdown"]');
        const dd = (toggle && window.bootstrap) ? bootstrap.Dropdown.getInstance(toggle) : null;
        if (dd) dd.hide();
      });
    });

    // 首次進站：有 hash 就載入；沒有就保持空白或自行指定預設頁
    const initial = location.hash.slice(1);
    if (initial) {
      loadSubpage(initial);
    } else {
      // 想指定預設頁就解除下面註解：
      // window.location.hash = 'pages/admin_usermanage.php';
    }
  });
})();
</script>
<script src="js/breeze-ink-bg.js"></script>

    </body>

    </html>