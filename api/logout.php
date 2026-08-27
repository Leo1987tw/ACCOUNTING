<?php
// 1. 💡 完美對齊：因為它與 db.php 都在 api/ 資料夾內，直接同層載入連線大腦
include_once "./db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. 清空當前使用者留在 $_SESSION 裡的所有身分變數（徹底抹除登入狀態）
    $_SESSION = array();

    // 3. 如果瀏覽器端是用 Cookie 存放 Session ID，一併發送過期 Cookie 將其彻底抹除歷史痕跡
    if (ini_get("session_use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // 4. 徹底摧毀伺服器端的 Session 記憶體實體，不留任何資安死角
    session_destroy();

    // 5. 呼叫您在 db.php 裡定義好的通用跳轉函數，安全退回到登入分流頁面
    to("../index.php");
} else {
    // 🔒 安全機制：阻擋非 POST 的惡意網址直接瀏覽或點擊
    to("../index.php");
}
?>