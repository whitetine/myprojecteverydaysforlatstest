
<div id="work-form-page" data-page-id="work_form" class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">每日工作日誌</h3>
<a href="#pages/work_draft.php" data-page="work_draft" class="btn btn-outline-secondary spa-link">
  查看日誌
</a>


  </div>

  <div class="card">
    <div class="card-body">
      <form action="pages/work_save.php" method="post" enctype="multipart/form-data" id="work-main-form">
        <input type="hidden" name="work_ID" id="wf-work_id" value="">

        <div class="mb-3">
          <label class="form-label">標題</label>
          <input type="text" name="work_title" id="wf-title" class="form-control" maxlength="2000" required>
        </div>

        <div class="mb-3">
          <label class="form-label">內容</label>
          <textarea name="work_content" id="wf-content" class="form-control textarea-fixed" required></textarea>
          <div class="hint mt-1">每日僅一筆。暫存可重進修改；正式送出或過期即結案。</div>
        </div>

        <div class="mb-3">
          <label class="form-label mb-1">上傳檔案（選填，最大 50MB）</label>
          <div class="file-row">
            <input type="file" name="work_file" id="wf-file" class="form-control file-input">
            <button type="button" id="btn-clear-file" class="btn btn-sm btn-outline-secondary">清空選擇檔案</button>
          </div>

          <div id="wf-current-file" class="mt-2 d-flex align-items-center justify-content-between flex-wrap d-none">
            <div class="me-2">
              暫存檔案：
              <a id="wf-file-link" href="#" target="_blank"></a>
            </div>
            <button type="button" id="btn-remove-file" class="btn btn-sm btn-outline-danger">移除暫存檔案</button>
          </div>

          <div class="hint mt-1">超過 50MB 請把雲端連結放在內容區。</div>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-secondary" type="submit" name="action" value="draft" id="wf-btn-draft">暫存</button>
          <button class="btn btn-primary"   type="submit" name="action" value="submit" id="wf-btn-submit">正式送出</button>
          <span id="wf-readonly-badge" class="badge bg-success align-self-center d-none">今日紀錄已結案</span>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- 掛上本頁 CSS / JS（若你的 main.js 會動態注入，可改用動態載入） -->
<link rel="stylesheet" href="css/work-form.css">
<script src="../js/work-form.js"></script>
