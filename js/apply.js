const app = Vue.createApp({
  methods: {
    async submitForm() {
      const formEl = document.getElementById('applyForm');
      const fd = new FormData(formEl); // 自動包含檔案與文字欄位

      try {
        const res = await fetch('pages/api/upload.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.status === 'success') {
          Swal.fire('成功', data.message, 'success');
          formEl.reset();
        } else {
          Swal.fire('失敗', data.message || '請檢查表單', 'error');
        }
      } catch (e) {
        Swal.fire('錯誤', '無法連線到伺服器', 'error');
      }
    }
  }
});
app.mount('#app');