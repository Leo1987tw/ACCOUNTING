<?php
// ============================================================================
// 1. 🌟 安全分流：判定目前使用者是否已經成功登入
// ============================================================================
if (!isset($_SESSION['login'])) {
    // 💡 未登入狀態：展現大氣、有溫度的「歡迎來到我的帳簿」系統迎賓主看板 [INDEX]
    ?>
    <div class="landing-welcome-container">
        
        <!-- 迎賓核心大標題區 -->
        <div class="welcome-hero-section">
            <h1>📖 歡迎來到我的帳簿</h1>
            <p>這是您專屬的私人財務大廳。本系統內建企業級複式簿記大腦與全自動軟刪除資安防護網，為您精準守護每一筆日常收支與審計日誌軌跡。</p>
        </div>

        <!-- 巨大迎賓導覽按鈕區（400x150px 莫蘭迪高質感大 Button） -->
        <div class="dashboard-buttons-grid">
            <a href="./index.php?do=login" class="huge-dashboard-btn">
                <span>🔐 進入記帳大廳</span>
            </a>
            <a href="./index.php?do=register" class="huge-dashboard-btn register-tone">
                <span>📝 申請管理權限</span>
            </a>
        </div>

    </div>
    <?php
    // 阻斷後續資料庫查詢，確保未登入者絕對無法偷看任何歷史記帳數據
    exit();
}

// ============================================================================
// 2. 🔓 已登入狀態：後端數據準備（透過 db.php 預先實例化好的全域物件撈取無軟刪除資料）
// ============================================================================

// 📌 撈取沒被軟刪除的科目、分類、往來對象
$accounts   = $Account->all(null, "ORDER BY `code` ASC");
$categories = $Category->all();
$partners   = $Partner->all();

// 📌 加總當期收支（借貸金額總計）
$summarySql = "SELECT SUM(`debit_amount`) as `total_debit`, SUM(`credit_amount`) as `total_credit` 
               FROM `transaction_lines` 
               WHERE `deleted_at` IS NULL";
$summaryResult = $TxLine->q($summarySql) ?? [];

$totalDebit  = $summaryResult['total_debit'] ?? 0;
$totalCredit = $summaryResult['total_credit'] ?? 0;

// 📌 多表聯合查詢：最新 20 筆歷史日記簿明細 [INDEX]
$historySql = "SELECT t.`transaction_date`, a.`name` as `acc_name`, c.`name` as `cat_name`, 
                      t.`description`, p.`name` as `part_name`, tl.`debit_amount`
               FROM `transaction_lines` tl
               JOIN `transactions` t ON tl.`transaction_id` = t.`id`
               JOIN `accounts` a ON tl.`account_id` = a.`id`
               LEFT JOIN `categories` c ON tl.`category_id` = c.`id`
               LEFT JOIN `partners` p ON tl.`partner_id` = p.`id`
               WHERE t.`deleted_at` IS NULL AND tl.`deleted_at` IS NULL
               ORDER BY t.`id` DESC LIMIT 20";
$historyLines = $TxLine->q($historySql);
?>

<!-- ============================================================================
     3. 已登入前端 UI：雙欄智慧記帳卡片面板
     ============================================================================ -->
<div class="ledger-container">

    <!-- 🟢 左側表單卡片區 -->
    <section class="card-left">
        <div class="summary-container">
            <div class="summary-box">
                <div class="summary-title">當期總收入 (貸方)</div>
                <div class="summary-box-income">+ $<?= number_format($totalCredit, 0) ?></div>
            </div>
            <div class="summary-box">
                <div class="summary-title">當期總支出 (借方)</div>
                <div class="summary-box-expense">- $<?= number_format($totalDebit, 0) ?></div>
            </div>
        </div>

        <h2 class="form-main-title">✍️ 新增帳務分錄</h2>
        
        <form id="ledgerForm" onsubmit="return false;">
            <div class="form-group">
                <label>交易日期</label>
                <input type="date" name="transaction_date" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="row-group">
                <div class="form-group">
                    <label>會計科目</label>
                    <select name="account_id" required>
                        <option value="">-- 請選擇科目 --</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['code'] . ' ' . $acc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>功能分類</label>
                    <select name="category_id">
                        <option value="">-- 請選擇分類 --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>交易金額</label>
                <input type="number" name="amount" min="1" placeholder="請輸入整數金額" required>
            </div>

            <div class="form-group">
                <label>往來對象</label>
                <select name="partner_id">
                    <option value="">-- 無特定往來對象 --</option>
                    <?php foreach ($partners as $part): ?>
                        <option value="<?= $part['id'] ?>"><?= htmlspecialchars($part['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>摘要說明</label>
                <input type="text" name="description" placeholder="輸入細節..." required>
            </div>

            <button type="button" class="btn-submit" id="saveLedgerBtn">確認記入日記簿</button>
        </form>
    </section>

    <!-- 🔵 右側歷史明細區 -->
    <section class="card-right">
        <h2 class="panel-main-title">📋 歷史日記簿明細 (最新 20 筆)</h2>
        
        <div class="table-scroll-container">
            <?php if (empty($historyLines)): ?>
                <div class="empty-text">暫無明細資料</div>
            <?php else: ?>
                <table class="ledger-table">
                    <thead>
                        <tr>
                            <th class="col-date">日期</th>
                            <th class="col-account">科目/分類</th>
                            <th class="col-desc">摘要/對象</th>
                            <th class="col-amount">金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historyLines as $line): ?>
                            <tr>
                                <td class="cell-date"><?= $line['transaction_date'] ?></td>
                                <td class="cell-account">
                                    <span class="account-name"><?= htmlspecialchars($line['acc_name']) ?></span>
                                    <span class="category-badge"><?= htmlspecialchars($line['cat_name'] ?? '未分類') ?></span>
                                </td>
                                <td class="cell-desc">
                                    <span class="desc-text"><?= htmlspecialchars($line['description']) ?></span>
                                    <?php if ($line['part_name']): ?>
                                        <span class="partner-text">🤝 對象: <?= htmlspecialchars($line['part_name']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-amount">$<?= number_format($line['debit_amount'], 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

</div>