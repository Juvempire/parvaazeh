<?php
// =========================================================================
// نام فایل: save_lead.php
// وظیفه: ذخیره اطلاعات فرم و هدایت کاربر به صفحه تشکر (Thank You Page)
// =========================================================================

// تنظیم منطقه زمانی به تهران
date_default_timezone_set('Asia/Tehran');

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
    
    // ۲. آماده‌سازی داده برای CSV (اکنون با زمان تهران)
    $date_miladi = date('Y/m/d');
    $time = date('H:i:s');
    
    // تبدیل تاریخ میلادی به شمسی
    $date_shamsi = gregorian_to_jalali(date('Y'), date('m'), date('d'));
    $date_shamsi_formatted = $date_shamsi[0] . '/' . $date_shamsi[1] . '/' . $date_shamsi[2];
    
    // ترکیب تاریخ میلادی و شمسی در یک سلول
    $date_combined = $date_miladi . ' (' . $date_shamsi_formatted . ')';
    
    $lead_data = [$date_combined, $time, $source, $visa_type, $name, $phone];
    
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

/**
 * تابع تبدیل تاریخ میلادی به شمسی
 */
function gregorian_to_jalali($gy, $gm, $gd) {
    $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * ((int)($days / 12053)));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    if ($days > 365) {
        $jy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + (int)($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return array($jy, $jm, $jd);
}
?>