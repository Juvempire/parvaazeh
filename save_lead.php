<?php
// =========================================================================
// نام فایل: save_lead.php
// وظیفه: ذخیره اطلاعات فرم و هدایت کاربر به صفحه تشکر (Thank You Page)
// =========================================================================

// تنظیمات: نام فایل CSV و هدرهای آن
$csv_file = 'leads.csv';
$csv_headers = ['تاریخ', 'ساعت', 'صفحه منبع', 'نوع ویزا', 'نام و نام خانوادگی', 'شماره تماس']; 

// آدرس صفحه تشکر
$thank_you_url = 'thank-you.html'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ۱. جمع‌آوری و تمیز کردن داده‌ها
    $name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : 'بدون نام';
    $phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : 'بدون شماره';
    $visa_type = isset($_POST['visa_type']) ? trim(strip_tags($_POST['visa_type'])) : 'بدون نوع';
    $source = isset($_POST['source']) ? trim(strip_tags($_POST['source'])) : 'نامشخص';
    
    // ۲. آماده‌سازی داده برای CSV
    $date = date('Y/m/d');
    $time = date('H:i:s');
    $lead_data = [$date, $time, $source, $visa_type, $name, $phone];
    
    // ۳. باز کردن فایل و ذخیره‌سازی
    $file_handle = fopen($csv_file, 'a');
    
    if ($file_handle) {
        
        // اگر فایل خالی باشد، هدرها را می‌نویسد.
        if (filesize($csv_file) == 0) {
            fputcsv($file_handle, $csv_headers, ';'); 
        }
        
        // نوشتن داده جدید در انتهای فایل
        fputcsv($file_handle, $lead_data, ';');
        
        fclose($file_handle);
        
        // هدایت به صفحه تشکر
        header("Location: $thank_you_url");
        exit;
        
    } else {
        // خطای دسترسی
        header("Location: /?status=error");
        exit;
    }
} else {
    // اگر کسی مستقیماً به این فایل مراجعه کرد، به صفحه اصلی هدایت شود
    header("Location: /");
    exit;
}
?>