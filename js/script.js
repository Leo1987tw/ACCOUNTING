// ============================================================================
// 📊 智慧記帳面板 (main.php) 非同步 Fetch 過帳處理專用外部腳本
// ============================================================================
document.addEventListener("DOMContentLoaded", function () {
    const saveLedgerBtn = document.getElementById('saveLedgerBtn');
    
    // 💡 智慧防呆：只有當畫面上有「確認記入日記簿」按鈕時才啟動監聽，不對註冊頁造成任何變數干擾
    if (saveLedgerBtn) {
        saveLedgerBtn.addEventListener('click', function () {
            const form = document.getElementById('ledgerForm');
            
            // 1. 收集前端數值，進行第一道嚴密的財務防呆攔截
            const accountId = form.elements['account_id'].value;
            const amount = form.elements['amount'].value;
            const description = form.elements['description'].value;

            if (!accountId) {
                alert('❌ 商業防呆：請選擇會計科目！');
                return;
            }
            if (!amount || amount <= 0) {
                alert('❌ 財務防呆：請輸入大於 0 的正確整數交易金額！');
                return;
            }
            if (!description.trim()) {
                alert('❌ 審計防呆：請輸入摘要說明，以便未來查帳與核對！');
                return;
            }

            // 2. 打包表單欄位為 FormData 非同步載體
            const formData = new FormData(form);

            // 3. 發送至後端複式記帳安全過帳引擎 API
            fetch('./api/add_transaction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('🎉 ' + data.message);
                    location.reload(); // 💡 智慧過帳後自動刷新，即時同步統計盒與右側歷史日記簿
                } else {
                    alert('⚠️ 會計過帳失敗：' + data.message);
                }
            })
            .catch(error => {
                alert('系統連線異常，請檢查您的後端過帳 API 服務是否正常啟動！');
                console.error('Fetch 錯誤:', error);
            });
        });
    }
});