    </main>
  </div>

  <!-- Light Blue 主腳本 -->
  <script src="assets/light-blue/js/app.js"></script>

  <!-- SPA 切換邏輯 -->
  <script>
  $(document).ready(function(){
    const $content = $("#content");

    $(".sidebar-nav a").on("click", function(e){
      e.preventDefault();
      const page = $(this).data("page");
      if (page) {
        $content.load(page, function(){
          if (window.initPageScript) initPageScript();
        });
      }
    });

    // 預設載入首頁
    const defaultPage = "pages/dashboard.php";
    $content.load(defaultPage);
  });
  </script>
</body>
</html>
