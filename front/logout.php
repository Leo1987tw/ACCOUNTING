<form action="./api/logout.php" method="POST" class="container">
    <fieldset>
        <legend>使用者登出</legend>
        
        <div class="logout-text-box">
            <h3>請問您確定要登出嗎？</h3>
            <p>登出時系統將自動保存與同步您今日的帳務審計日誌。</p>
        </div>

        <div class="button-group">
            <button type="submit" class="btn-danger">登出</button>
            <button type="button" onclick="location.href='./index.php';">返回首頁</button>
        </div>
    </fieldset>
</form>