<!-- register.php -->
<form action="./api/register.php" method="POST" id="regForm" onsubmit="return validateForm()" class="container">
    <fieldset>
        <legend>使用者註冊</legend>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">❌ 註冊失敗：核心必填欄位不可為空！</div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="username">帳號：</label>
            <input type="text" name="username" id="username" required placeholder="請輸入您的帳號" onblur="checkUsername()">
            <span id="username-msg"></span>
        </div>

        <div class="form-group">
            <label for="password">密碼：</label>
            <input type="password" name="password" id="password" required placeholder="密碼長度至少 6 個字元" oninput="checkPasswordMatch()">
        </div>

        <div class="form-group">
            <label for="password2">再次確認密碼：</label>
            <input type="password" name="password2" id="password2" required placeholder="再次輸入您的密碼" oninput="checkPasswordMatch()">
            <span id="password-msg"></span>
        </div>

        <div class="button-group">
            <button type="submit" id="submitBtn" class="btn-disabled" disabled style="transition: all 0.3s ease;">註冊</button>
            <button type="button" onclick="location.href='./index.php?do=login';">返回登入</button>
        </div>
    </fieldset>
</form>

<script>
    // 💡 智慧校正：確保前台防線標記變數完全對齊
    let isRegUsernameValid = false;
    let isRegPasswordValid = false;

    /**
     * 1. 帳號即時重複性 AJAX 檢核
     */
    function checkUsername() {
        const usernameInput = document.getElementById('username');
        const msgSpan = document.getElementById('username-msg');
        if (!usernameInput || !msgSpan) return;

        const username = usernameInput.value.trim();

        if (username === '') {
            msgSpan.innerHTML = '';
            msgSpan.className = '';
            isRegUsernameValid = false;
            toggleRegisterSubmitButton(); // 🎯 修正呼叫名稱
            return;
        }

        let targetUrl = `./api/check_username.php?username=${encodeURIComponent(username)}`;
        if (window.location.pathname.includes('/front/')) {
            targetUrl = `../api/check_username.php?username=${encodeURIComponent(username)}`;
        }

        fetch(targetUrl)
            .then(response => response.json())
            .then(data => {
                msgSpan.className = ''; 
                
                if (data.exists) {
                    msgSpan.className = 'msg-error';
                    msgSpan.innerHTML = '❌ 此帳號已被註冊，請換一個';
                    isRegUsernameValid = false;
                } else {
                    msgSpan.className = 'msg-success';
                    msgSpan.innerHTML = '✓ 此帳號可以使用';
                    isRegUsernameValid = true; 
                }
                toggleRegisterSubmitButton(); // 🎯 修正呼叫名稱，保證變色！
            })
            .catch(err => {
                console.error('AJAX 查重通訊失敗:', err);
                msgSpan.className = 'msg-error';
                msgSpan.innerHTML = '⚠️ 查重通訊異常，請確保 api 目錄正常';
            });
    }

    /**
     * 2. 密碼與二次確認即時同步核對
     */
    function checkPasswordMatch() {
        const passwordInput = document.getElementById('password');
        const password2Input = document.getElementById('password2');
        const msgSpan = document.getElementById('password-msg');
        if (!passwordInput || !password2Input || !msgSpan) return;

        const p1 = passwordInput.value;
        const p2 = password2Input.value;

        if (p2 === '') {
            msgSpan.innerHTML = '';
            msgSpan.className = '';
            isRegPasswordValid = false;
            toggleRegisterSubmitButton(); // 🎯 修正呼叫名稱
            return;
        }

        if (p1.length < 6) {
            msgSpan.className = 'msg-error';
            msgSpan.innerHTML = '❌ 安全防線：密碼長度至少需要 6 個字元！';
            isRegPasswordValid = false;
            toggleRegisterSubmitButton(); // 🎯 修正呼叫名稱
            return;
        }

        if (p1 === p2) {
            msgSpan.className = 'msg-success';
            msgSpan.innerHTML = '✓ 密碼輸入一致';
            isRegPasswordValid = true;
        } else {
            msgSpan.className = 'msg-error';
            msgSpan.innerHTML = '❌ 兩次輸入的密碼不符';
            isRegPasswordValid = false;
        }
        toggleRegisterSubmitButton(); // 🎯 修正呼叫名稱，保證變色！
    }

    /**
     * 3. 🎯 核心定義：按鈕啟用/鎖定視覺動態切換函數 (精準命名)
     */
    function toggleRegisterSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        if (!submitBtn) return;

        if (isRegUsernameValid && isRegPasswordValid) {
            submitBtn.disabled = false;
            submitBtn.className = 'btn-active';   // 解鎖亮起莫蘭迪綠
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'btn-disabled'; // 還原霧面防呆灰
        }
    }

    /**
     * 4. 表單送出前總防線
     */
    function validateForm() {
        if (!isRegUsernameValid) {
            alert('❌ 請先輸入有效且未被佔用的帳號！');
            return false;
        }
        if (!isRegPasswordValid) {
            alert('❌ 請確認兩次密碼輸入完全一致且大於 6 個字元！');
            return false;
        }
        return true; 
    }

    // 5. 初始畫面載入就緒按鈕鎖定
    document.addEventListener("DOMContentLoaded", function() {
        toggleRegisterSubmitButton();
    });
</script>