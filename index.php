<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimal Ledger</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- 頂部導覽列 -->
    <header class="navbar">
        <div class="nav-container">
            <span class="nav-status">當前工作帳簿：</span>
            <div class="nav-select-wrapper">
                <span class="nav-icon">📁</span>
                <select class="nav-select">
                    <option>複中個人的私人生活帳 (Ledger #1)</option>
                </select>
            </div>
        </div>
    </header>

    <!-- 主要內容區塊 -->
    <main class="container">
        
        <!-- 左側：新增帳務分錄 -->
        <section class="card-left">
            <!-- 上方收支統計顯示 -->
            <div class="summary-container">
                <div class="summary-box income">
                    <div class="summary-title">當期總收入</div>
                    <div class="summary-amount">+ $0</div>
                </div>
                <div class="summary-box expense">
                    <div class="summary-title">當期總支出</div>
                    <div class="summary-amount">- $0</div>
                </div>
            </div>

            <!-- 表單本體 -->
            <div class="form-container">
                <h2 class="form-title"><span class="icon-orange">✍️</span> 新增帳務分錄 (私人帳)</h2>
                
                <form onsubmit="return false;">
                    <div class="form-group">
                        <label>交易日期</label>
                        <input type="date" value="<?= date('Y-m-d');?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group col">
                            <label>會計科目</label>
                            <select>
                                <option>💵 現金資產</option>
                            </select>
                        </div>
                        <div class="form-group col">
                            <label>日常功能分類 (Category)</label>
                            <select>
                                <option>🍔 食 (飲食餐飲)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>交易金額</label>
                        <input type="number" value="1">
                    </div>

                    <div class="form-group">
                        <label>往來對象 (Partner)</label>
                        <select>
                            <option>某某有限公司</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>備註說明 (Memo)</label>
                        <input type="text" placeholder="輸入交易細節...">
                    </div>

                    <button type="button" class="btn-submit">確認記入日記簿</button>
                </form>
            </div>
        </section>

        <!-- 右側：歷史日記簿明細 -->
        <section class="card-right">
            <h2 class="right-title"><span class="icon-pink">📋</span> 歷史日記簿明細 (Ledger #1)</h2>
            <!-- 預留明細顯示空間 -->
            <div class="empty-state"></div>
        </section>

    </main>

</body>
</html>