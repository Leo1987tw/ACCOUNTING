<?php
// 1. 載入同層目錄的連線大腦與全域實例化物件
include_once "./db.php";

// 💡 強制清除先前可能產生的緩存，並宣告回傳格式為標準 JSON
if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

// 安全控管：確保使用者處於登入狀態才能記入日記簿
if (!isset($_SESSION['login'])) {
    echo json_encode(['status' => 'error', 'message' => '連線已逾時，請重新登入系統！']);
    exit;
}

try {
    // 2. 接收並安全轉化前台表單數據
    $transaction_date = $_POST['transaction_date'] ?? date('Y-m-d');
    $account_id       = (int)($_POST['account_id'] ?? 0);
    $category_id      = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $amount           = (float)($_POST['amount'] ?? 0);
    $partner_id       = !empty($_POST['partner_id']) ? (int)$_POST['partner_id'] : null;
    $description      = trim($_POST['description'] ?? '');

    // 預設關聯主檔（私用版預設皆綁定 1 號預設帳本、1 號幣別與 1 號會計期間）
    $ledger_id   = 1;
    $period_id   = 1;
    $currency_id = 1; 
    $user_id     = $_SESSION['user_id'] ?? 1;

    // 3. 核心大腦升級：向全域物件提取底層 PDO 連線實例，手動發動 Transaction 事務安全鎖！
    $pdo = $Tx->getPdo();
    $pdo->beginTransaction();

    // 4. 利用全域物件呼叫 save() 智慧生成傳票主檔 (transactions)
    $voucher_number = 'VOUCH-' . date('YmdHis') . '-' . rand(100, 999); // 生成全站唯一傳票憑證流水號
    
    $txRowId = $Tx->save([
        'ledger_id'            => $ledger_id,
        'accounting_period_id' => $period_id,
        'transaction_date'     => $transaction_date,
        'voucher_number'       => $voucher_number,
        'status'               => 'posted', // 預設為已過帳生效
        'total_amount'         => $amount,
        'description'          => $description,
        'created_by'           => $user_id
    ]);

    // 5. 智慧寫入複式簿記明細帳 (transaction_lines)
    $TxLine->save([
        'transaction_id'        => $txRowId,
        'account_id'            => $account_id,
        'category_id'           => $category_id,
        'partner_id'            => $partner_id,
        'currency_id'           => $currency_id,
        'exchange_rate'         => 1.000000,
        'debit_amount'          => $amount, // 預設記入借方
        'credit_amount'         => 0.00,
        'foreign_debit_amount'  => $amount,
        'foreign_credit_amount' => 0.00,
        'memo'                  => $description
    ]);

    // 6. 全自動寫入無情稽核審計日誌 (audit_logs)
    $log_json = json_encode([
        'voucher' => $voucher_number, 
        'amount'  => $amount, 
        'desc'    => $description
    ], JSON_UNESCAPED_UNICODE);
    
    $Log->save([
        'ledger_id'  => $ledger_id,
        'user_id'    => $user_id,
        'action'     => 'CREATE_TRANSACTION',
        'table_name' => 'transactions',
        'row_id'     => $txRowId,
        'new_value'  => $log_json,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);

    // 7. 萬無一失！正式提交資料庫交易事務過帳生效！
    $pdo->commit();
    
    echo json_encode([
        'status'  => 'success', 
        'message' => '傳票號 [' . $voucher_number . '] 已順利完成複式記帳並安全存檔！'
    ]);

} catch (Exception $e) {
    // 💡 終極安全網：中間只要有任何一格出錯，全盤撤回（Rollback），保證資料庫絕不留下髒資料
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'status'  => 'error', 
        'message' => '會計過帳引擎發生衝突，原因：' . $e->getMessage()
    ]);
}
exit();