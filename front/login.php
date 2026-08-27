<!-- login.php -->
<form action="./api/login.php" method="POST" class="container">
    <fieldset>
        <legend>使用者登入</legend>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">❌ 帳號密碼錯誤，或該管理員已被停用</div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="username">帳號：</label>
            <input type="text" name="username" id="username" required placeholder="請輸入您的帳號">
        </div>

        <div class="form-group">
            <label for="password">密碼：</label>
            <input type="password" name="password" id="password" required placeholder="請輸入您的密碼">
        </div>

        <div class="button-group">
            <button type="submit" class="btn-active">登入</button>
            <button type="button" onclick="location.href='./index.php?do=register';">前往註冊</button>
        </div>
    </fieldset>
</form>