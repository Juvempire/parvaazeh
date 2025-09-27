<?php
// =========================================================================
// نام فایل: save_lead.php
// وظیفه: ذخیره اطلاعات فرم و هدایت کاربر به صفحه تشکر (Thank You Page)
// =========================================================================

// تنظیمات: نام فایل CSV و هدرهای آن
$csv_file = 'leads.csv';
$csv_headers = ['تاریخ', 'ساعت', 'صفحه منبع', 'نام و نام خانوادگی', 'شماره تماس']; 

// **** خط جدید: تعریف آدرس صفحه تشکر ****
$thank_you_url = 'thank-you.html'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ۱. جمع‌آوری و تمیز کردن داده‌ها
    $name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : 'N/A';
    $phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : 'N/A';
    // دریافت نام صفحه از فیلد مخفی source
    $source = isset($_POST['source']) ? trim(strip_tags($_POST['source'])) : 'Unknown';
    
    // ۲. آماده‌سازی داده برای CSV
    $date = date('Y/m/d');
    $time = date('H:i:s');
    $lead_data = [$date, $time, $source, $name, $phone];
    
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
        
        // ************************************************
        // ********** تغییر کلیدی: هدایت به صفحه تشکر **********
        // ************************************************
        header("Location: $thank_you_url");
        exit;
        
    } else {
        // خطای دسترسی (در این حالت کاربر به صفحه اصلی بازگردانده می‌شود)
        header("Location: /?status=error");
        exit;
    }
} else {
    // اگر کسی مستقیماً به این فایل مراجعه کرد، به صفحه اصلی هدایت شود
    header("Location: /");
    exit;
}
?>