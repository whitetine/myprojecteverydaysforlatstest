  <header>
    <h2 class="mb-4">申請文件上傳</h2>
  </header>

  <div id="app" class="main container">
    <!-- <div id="app" class="main"> -->
<div id="apply-uploader">
  <div class="card mb-4">
    <div class="card-header"><strong>上傳區</strong></div>
    <div class="card-body">
      <form @submit.prevent="submitForm" enctype="multipart/form-data">
        <label class="form-label" for="file_ID">選擇表單類型：</label>
        <select v-model="selectedFileID" name="file_ID" id="file_ID" class="form-select" required>
          <option disabled value="">請選擇表單</option>
          <option v-for="file in files" :key="file.file_ID" :value="file.file_ID">
            {{ file.file_name }}
          </option>
        </select>

        <label class="form-label mt-3" for="apply_user">申請人姓名：</label>
        <input type="text" class="form-control" v-model="applyUser" id="apply_user" name="apply_user" required>

        <label for="apply_other" class="form-label mt-3">檔案名稱/其他備註：</label>
        <textarea v-model="applyOther" class="form-control" id="apply_other" name="apply_other" rows="3"></textarea>

        <label for="apply_image" class="form-label mt-3">上傳圖片（PNG/JPG）：</label>
        <input type="file" ref="applyImage" class="form-control" name="apply_image" id="apply_image"
               accept="image/png, image/jpeg" @change="previewImage" />

        <div v-if="imagePreview" class="preview-pane">
          <label class="form-label mt-3">圖片預覽：</label>
          <div class="d-flex align-items-center gap-3 mb-2">
            <label class="form-label mb-0">預覽大小：<strong>{{ previewPercent }}%</strong></label>
            <input type="range" class="form-range flex-grow-1" min="10" max="100" step="5"
                   v-model.number="previewPercent">
          </div>
          <!-- 解法A：調容器寬度 -->
          <div class="preview-box" :style="{ width: previewPercent + '%', maxWidth: '100%' }">
            <img :src="imagePreview" class="preview-img" alt="圖片預覽">
          </div>
        </div>

        <button type="submit" class="btn btn-secondary mt-3">送出申請</button>
      </form>
    </div>
  </div>

  <div class="preview-container" v-if="selectedFileUrl">
    <h4>範例檔案預覽</h4>
    <iframe :src="selectedFileUrl" style="width:100%; height:350px; border:none;"></iframe>
  </div>
</div>