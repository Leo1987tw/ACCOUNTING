<?php
// 1. 載入上一層目錄的連線大腦與全域實例化物件（自動發動 Session 與時區鎖定）
include_once "./db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 💡 數據對接：精準接收您前台 input 表單的帳號與設定密碼
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 後端終極安全防線：嚴格進行空值與密碼長度防呆攔截
    if (empty($username) || empty($password) || strlen($password) < 6) {
        // 萬一有惡意繞過前台驗證的情況，立刻帶回錯誤引數退回註冊頁
        to("../index.php?do=register&error=invalid_data");
    }

    // 2. 雙重防禦複查：確保帳號沒有在表單提交的最後一秒被其他人搶先註冊
    // find() 自動包含了 `deleted_at` IS NULL 防護，保證停用帳號不會干擾註冊 [INDEX]
    $exists = $User->find([
        'user_name' => $username
    ]);

    if ($exists) {
        to("../index.php?do=register&error=username_taken");
    } else {
        // 🔐 3. 密碼安全單向雜湊加密 (Password Hashing)
        // 確保即使資料庫不幸外洩，駭客也完全無法還原與破解原始明文密碼 [INDEX]
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 4. 💡 數據對齊：100% 依據您 DDL 結構圖中的 10 個欄位規格進行 save() 智慧型寫入！
        $User->save([
            'user_name'     => $username,
            'password_hash' => $hashedPassword, // 🎯 欄位精準對齊您資料表結構的第 3 格！
            'real_name'     => '',              // 預設為空 (varchar)
            'email'         => null,            // 💡 依結構圖：此欄位空值屬性為「是」，可安全傳入 null [INDEX]
            'role'          => 'admin',         // 依結構圖：完全符合 enum 規範定義的類型 [INDEX]
            'is_active'     => 1                // 依結構圖：型態 tinyint(1) [INDEX]
        ]);

        // 5. 註冊成功！全自動帶入 do=login 引導新成員進行首次登入 [INDEX]
        to("../index.php?do=login&success=registered");
    }
} else {
    // 阻擋非 POST 的非法瀏覽與惡意請求
    to("../index.php");
}
?>