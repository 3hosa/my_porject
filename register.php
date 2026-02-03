<?php
session_start();
require 'includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($username && $email && $password && $confirm_password) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $email]);
                $message = "<div class='success-message'>✅ تم التسجيل بنجاح! يمكنك الآن <a href='login.php'>تسجيل الدخول</a></div>";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = "<div class='error-message'>❌ اسم المستخدم أو البريد الإلكتروني موجود بالفعل</div>";
                } else {
                    $message = "<div class='error-message'>❌ خطأ في التسجيل: " . $e->getMessage() . "</div>";
                }
            }
        } else {
            $message = "<div class='error-message'>❌ كلمات المرور غير متطابقة</div>";
        }
    } else {
        $message = "<div class='error-message'>❌ يرجى ملء جميع الحقول</div>";
    }
}
?>

<style>
/* اجعل الجسم أو الحاوية كلها flex وتتمركز */
body {
    display: flex;
    justify-content: center; /* تمركز أفقياً */
    align-items: center;     /* تمركز عمودياً */
    min-height: 100vh;       /* ارتفاع كامل الشاشة */
    background: #f0f2f5;     /* خلفية فاتحة */
    margin: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* بطاقة التسجيل */
.register-card {
    background: #fff;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
}
.register-card h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
}
.register-card input {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    box-sizing: border-box;
    transition: .3s;
}
.register-card input:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0,123,255,0.5);
}
.register-card button {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: .3s;
}
.register-card button:hover {
    background-color: #0056b3;
}
.success-message {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}
.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}
.register-card p {
    text-align: center;
    margin-top: 20px;
}
.register-card a {
    color: #007bff;
    text-decoration: none;
}
.register-card a:hover {
    text-decoration: underline;
}


</style>
<div class="register-card">

 
<div class="login-container">
    <h2 class="section-title">التسجيل</h2>

    <?= $message ?>

    <form class="login-form" method="post" action="" data-no-pjax>
        <label for="username">اسم المستخدم</label>
        <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>

        <label for="email">البريد الإلكتروني</label>
        <input type="email" id="email" name="email" placeholder="أدخل البريد الإلكتروني" required>

        <label for="password">كلمة المرور</label>
        <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>

        <label for="confirm_password">تأكيد كلمة المرور</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="أدخل تأكيد كلمة المرور" required>

        <button type="submit" class="btn">التسجيل</button>
    </form>

    <p class="register-link">لديك حساب بالفعل؟ <a href="login.php">سجل الدخول</a></p>
</div>

</div>
 