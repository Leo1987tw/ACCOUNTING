<?php
// 1. 💡 完美對齊：因為它與 db.php 都在 api/ 資料夾內，直接同層載入連線大腦
include_once "./db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收前端遞交的登入欄位
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 後端基本空值防護
    if (empty($username) || empty($password)) {
        to("../index.php?do=login&error=1");
    }

    // 2. 💡 智慧修正：運用您 DB 類別內建的「純陣列構造查詢」，完美避免 trim() 陣列型態崩潰！
    // find() 全自動在底層幫您鎖定只查詢沒被軟刪除（deleted_at IS NULL）的正常啟用帳號 [INDEX]
    $user = $User->find([
        'user_name' => $username,
        'is_active' => 1
    ]);

    // 3. 運用 password_verify 核對安全雜湊密碼
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // 🔐 登入成功：將核心身分鑰匙與狀態寫入 Session 緩存中
        $_SESSION['login']     = true; // 💡 完美對齊您在 header.php 的登入判斷條件！
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = !empty($user['real_name']) ? $user['real_name'] : $user['user_name'];
        $_SESSION['user_role'] = $user['role'];

        // 完美跳轉回智慧日記簿主頁面
        to("../index.php");
    } else {
        // 驗證失敗：帶回錯誤代碼退回，讓前端外部樣式印出紅色警告
        to("../index.php?do=login&error=1");
    }
} else {
    // 🔒 安全機制：阻擋非 POST 的惡意請求
    to("../index.php");
}
?>