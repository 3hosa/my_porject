<?php
session_start();
require 'includes/db.php';

$message = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'index.php';

    if ($username_or_email && $password) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username_or_email, $username_or_email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: ' . $redirect);
                exit;
            } else {
                $message = "<div class='error-message'>❌ اسم المستخدم أو البريد الإلكتروني أو كلمة المرور غير صحيحة</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='error-message'>❌ خطأ في تسجيل الدخول: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='error-message'>❌ يرجى ملء جميع الحقول</div>";
    }
}
?>

<style>
/* تنسيقات صفحة تسجيل الدخول المحسنة */
body {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

.login-card {
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
    position: relative;
    overflow: hidden;
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.login-card h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #1e293b;
    font-size: 28px;
}

.login-card input {
    width: 100%;
    padding: 14px;
    margin: 10px 0 20px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 16px;
    box-sizing: border-box;
    transition: all 0.3s;
}

.login-card input:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.login-card button {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
    margin-top: 10px;
}

.login-card button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    border-right: 4px solid #059669;
}

.error-message {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    border-right: 4px solid #dc2626;
}

.login-card p {
    text-align: center;
    margin-top: 25px;
    color: #64748b;
}

.login-card a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.login-card a:hover {
    text-decoration: underline;
}

.info-message {
    background: #dbeafe;
    color: #1e40af;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 0.9em;
}
</style>

<div class="login-card">
    <h2>تسجيل الدخول</h2>

    <?php if (isset($_GET['redirect']) && strpos($_GET['redirect'], 'products.php') !== false): ?>
    <div class="info-message">
        ⚠️ يجب تسجيل الدخول لإضافة المنتجات إلى السلة
    </div>
    <?php endif; ?>

    <?= $message ?>

    <form method="post" action="" data-no-pjax>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect); ?>">
        
        <label for="username_or_email">اسم المستخدم أو البريد الإلكتروني</label>
        <input type="text" id="username_or_email" name="username_or_email" placeholder="أدخل اسم المستخدم أو البريد الإلكتروني" required>

        <label for="password">كلمة المرور</label>
        <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>

        <button type="submit" class="btn">تسجيل الدخول</button>
    </form>

    <p>ليس لديك حساب؟ <a href="register.php">سجل الآن</a></p>
</div>