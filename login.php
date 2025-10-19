<?php
// فایل: login.php
// وظیفه: صفحه لاگین ساده برای احراز هویت

session_start();

// تنظیمات لاگین
$valid_username = 'admin';
$valid_password_hash = password_hash('password123', PASSWORD_DEFAULT);  // پسورد دلخواه (تغییر بدید)

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = $_POST['username'] ?? '';
    $input_password = $_POST['password'] ?? '';
    
    if ($input_username === $valid_username && password_verify($input_password, $valid_password_hash)) {
        $_SESSION['logged_in'] = true;
        header('Location: view_leads.php');
        exit;
    } else {
        $error = 'نام کاربری یا رمز عبور اشتباه است.';
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ورود به پنل آمار - پروازه</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Vazirmatn', sans-serif; 
            background-color: #f4f4f4; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-container { 
            background: #fff; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            width: 300px; 
            text-align: center; 
        }
        input { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
			font-family: inherit;
			box-sizing: border-box;
        }
        button { 
            background: #007bff; 
            color: white; 
            border: none; 
            padding: 10px; 
            width: 100%; 
            border-radius: 4px; 
            cursor: pointer; 
			font-family: inherit;
        }
        button:hover { background: #0056b3; }
        .error { color: #dc3545; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>ورود به پنل آمار</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="نام کاربری" required>
            <input type="password" name="password" placeholder="رمز عبور" required>
            <button type="submit">ورود</button>
        </form>
    </div>
</body>
</html>