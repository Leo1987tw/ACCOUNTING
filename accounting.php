<style>
    /* 使用較長、較精確的選擇器來提升權重，避免使用 !important */
    
    /* 1. 外層容器：改用 block 或 flex 並設定 min-width 撐開 */
    main .ledger-container {
        display: flex;
        flex-direction: row;
        justify-content: center; /* 讓內容在中間，左右留白 */
        align-items: stretch;
        gap: 40px;               /* 左右卡片間距加大一點，視覺較舒服 */
        padding: 40px 20px;
        width: 100%;
        max-width: 1100px;       /* 限制總寬度，讓大螢幕左右有明顯留白 */
        margin: 0 auto;          /* 置中關鍵 */
        box-sizing: border-box;
    }

    /* 2. 左側與右側卡片：設定一樣的權重，讓寬度接近 */
    main .ledger-container .card-left,
    main .ledger-container .card-right {
        flex: 1;                /* 兩邊各佔一半 */
        min-width: 450px;       /* 保持最基本的寬度，不准再縮小 */
        background-color: #ffffff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        border: 1px solid #eef2f1;
        height: auto;
        min-height: 650px;      /* 兩邊等高會比較好看 */
    }

    /* 4. 內部表單垂直佈局修正 */
    main .ledger-container form {
        display: block; /* 覆蓋可能繼承的 flex */
    }

    main .ledger-container .form-group {
        display: flex;
        flex-direction: column; /* 強制標題在上，輸入框在下 */
        gap: 8px;
        margin-bottom: 20px;
        width: 100%;
    }

    main .ledger-container .form-group label {
        font-weight: 600;
        font-size: 14px;
        color: #444;
        display: block;
    }

    main .ledger-container .form-group input, 
    main .ledger-container .form-group select {
        width: 100%;
        height: 42px;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
        background-color: #fafafa;
        box-sizing: border-box; /* 確保 padding 不會撐開寬度 */
    }

    /* 讓科目和分類並排 */
    main .ledger-container .row-group {
        display: flex;
        gap: 15px;
        width: 100%;
    }
    main .ledger-container .row-group .form-group {
        flex: 1;
    }

    /* 收支統計盒 */
    main .ledger-container .summary-container {
        display: flex;
        justify-content: space-around;
        background-color: #f8faf9;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 25px;
        text-align: center;
    }

    main .ledger-container .btn-submit {
        width: 100%;
        padding: 14px;
        background-color: #5c8d78;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
    }
</style>

<!-- 內容結構 -->
<div class="ledger-container">

    <!-- 左側：表單區 -->
    <section class="card-left">
        <div class="summary-container">
            <div class="summary-box">
                <div style="font-size:12px; color:#888;">當期總收入</div>
                <div style="font-size:20px; font-weight:bold; color:#3a8bbb;">+ $0</div>
            </div>
            <div class="summary-box">
                <div style="font-size:12px; color:#888;">當期總支出</div>
                <div style="font-size:20px; font-weight:bold; color:#e57373;">- $0</div>
            </div>
        </div>

        <h2 style="font-size: 20px; margin-bottom: 20px; color: #333;">✍️ 新增帳務分錄</h2>
        
        <form onsubmit="return false;">
            <div class="form-group">
                <label>交易日期</label>
                <input type="date" value="<?= date('Y-m-d'); ?>">
            </div>

            <div class="row-group">
                <div class="form-group">
                    <label>會計科目</label>
                    <select><option>💵 現金資產</option></select>
                </div>
                <div class="form-group">
                    <label>功能分類</label>
                    <select><option>🍔 食 (飲食)</option></select>
                </div>
            </div>

            <div class="form-group">
                <label>交易金額</label>
                <input type="number" placeholder="0">
            </div>

            <div class="form-group">
                <label>往來對象</label>
                <select><option>某某有限公司</option></select>
            </div>

            <div class="form-group">
                <label>備註說明</label>
                <input type="text" placeholder="輸入細節...">
            </div>

            <button type="button" class="btn-submit">確認記入日記簿</button>
        </form>
    </section>

    <!-- 右側：明細區 -->
    <section class="card-right">
        <h2 style="font-size: 20px; margin-bottom: 20px; color: #333;">📋 歷史日記簿明細</h2>
        <div style="color: #ccc; text-align: center; margin-top: 150px; font-size: 18px;">
            暫無明細資料
        </div>
    </section>

</div>