<?php
// 1. 載入上一層目錄的連線大腦與全域實例化物件（自動發動 Session 與時區鎖定）
include_once "./db.php";

// 💡 企業級防呆：強制清除先前可能不小心產生的緩存與任何隱形換行空白字元，防止破壞 JSON 格式
if (ob_get_length()) ob_clean();

// 宣告回傳格式為標準的 JSON 數據，供前台 JavaScript 順暢解析
header('Content-Type: application/json; charset=utf-8');

// 安全接收前台傳過來的 GET 引數
$username = trim($_GET['username'] ?? '');

// 預設回傳狀態：該帳號尚未被註冊（可用）
$response = ['exists' => false];

if (!empty($username)) {
    // 2. 💡 智慧核對：直接調用您的全域物件 $User->find() 執行高速度比對！
    // 這裡我們直接傳入陣列，會自動轉成防範 SQL 注入的預處理指令
    // 而且它會自動過濾 `deleted_at IS NULL`，已被軟刪除的殭屍帳號不會干擾查重 [INDEX]
    $user = $User->find([
        'user_name' => $username
    ]);

    // 若從資料庫中撈得出資料，代表此帳號已被他人搶佔
    if ($user) {
        $response['exists'] = true;
    }
}

// 3. 將結果包裝成 JSON 印出，一秒接通您的前台監聽器！
echo json_encode($response);
exit();
?>