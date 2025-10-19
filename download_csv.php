<?php
session_start();

// چک کردن لاگین (مثل view_leads.php)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// مسیر فایل CSV (اگر خارج از public_html هست، مسیر کامل بده)
$csv_file = 'leads.csv';

if (file_exists($csv_file)) {
    // تنظیم هدرها برای دانلود
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($csv_file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($csv_file));
    
    // خواندن و ارسال فایل
    readfile($csv_file);
    exit;
} else {
    echo 'فایل یافت نشد.';
}
?>