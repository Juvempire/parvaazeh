<?php
// =========================================================================
// نام فایل: view_leads.php
// وظیفه: نمایش محتوای فایل leads.csv در قالب جدول HTML (جدیدترین ها در بالا)
// =========================================================================

// تنظیم منطقه زمانی به تهران
date_default_timezone_set('Asia/Tehran');

// تنظیمات: نام فایل CSV
$csv_file = 'leads.csv';
$delimiter = ';'; // جداکننده مورد استفاده در fputcsv ما

// بررسی وجود فایل
if (!file_exists($csv_file)) {
    die("خطا: فایل leads.csv پیدا نشد یا خالی است.");
}

// تنظیم هدر برای اطمینان از نمایش صحیح کاراکترهای فارسی (UTF-8)
header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لیست لیدهای ثبت‌شده</title>
    <style>
        body { font-family: Tahoma, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: right; }
        th { background-color: #007bff; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .export-link { display: inline-block; padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; margin-left: 10px; }
        .refresh-link { display: inline-block; padding: 10px 15px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .date-cell { font-family: "Segoe UI", Tahoma, sans-serif; direction: ltr; text-align: center; }
        .shamsi-date { color: #d63384; font-weight: bold; }
        .miladi-date { color: #6c757d; font-size: 0.9em; }
        .count-badge { background: #17a2b8; color: white; padding: 5px 10px; border-radius: 15px; margin-right: 10px; }
        .new-badge { background: #dc3545; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; margin-left: 5px; }
        .row-number { background: #6c757d; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; margin-left: 5px; }
        tr:hover { background-color: #e9f7fe !important; }
    </style>
</head>
<body>
<div class="container">';

// باز کردن فایل برای خواندن و شمارش لیدها
$total_leads = 0;
$today_leads = 0;
$today_date = date('Y/m/d'); // تاریخ امروز به فرمت میلادی
$today_shamsi = ''; // تاریخ شمسی امروز

// محاسبه تاریخ شمسی امروز
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

// محاسبه تاریخ شمسی امروز
$shamsi_today = gregorian_to_jalali(date('Y'), date('m'), date('d'));
$today_shamsi = $shamsi_today[0] . '/' . $shamsi_today[1] . '/' . $shamsi_today[2];

if (($handle = fopen($csv_file, "r")) !== FALSE) {
    $row_count = 0;
    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        if ($row_count > 0) { // رد کردن سطر هدر
            $total_leads++;
            
            // بررسی آیا لید مربوط به امروز است
            if (isset($data[0])) {
                $date_cell = $data[0];
                // بررسی تاریخ میلادی (قسمت اول قبل از پرانتز)
                if (strpos($date_cell, '(') !== false) {
                    $date_parts = explode('(', $date_cell);
                    $miladi_date = trim($date_parts[0]);
                    
                    // اگر تاریخ میلادی با امروز مطابقت دارد
                    if ($miladi_date === $today_date) {
                        $today_leads++;
                    }
                }
            }
        }
        $row_count++;
    }
    fclose($handle);
}

echo '<h1><span class="count-badge">' . $total_leads . '</span>لیست لیدهای ثبت‌شده</h1>';

// لینک‌های اکشن
echo '<a href="view_leads.php" class="refresh-link">🔄 بروزرسانی</a>';
echo '<a href="' . $csv_file . '" download="' . $csv_file . '" class="export-link">📥 دانلود فایل CSV</a>';

// باز کردن فایل برای خواندن و نمایش داده‌ها
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    $all_rows = [];
    $header = [];
    $row_count = 0;
    
    // خواندن تمام سطرها از فایل
    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        if ($row_count == 0) {
            // ذخیره هدر
            $header = $data;
        } else {
            // ذخیره داده‌ها به همراه شماره سطر اصلی
            $all_rows[] = [
                'data' => $data,
                'original_index' => $row_count,
                'is_today' => false
            ];
            
            // بررسی آیا لید مربوط به امروز است
            if (isset($data[0])) {
                $date_cell = $data[0];
                if (strpos($date_cell, '(') !== false) {
                    $date_parts = explode('(', $date_cell);
                    $miladi_date = trim($date_parts[0]);
                    
                    // اگر تاریخ میلادی با امروز مطابقت دارد
                    if ($miladi_date === $today_date) {
                        $all_rows[count($all_rows) - 1]['is_today'] = true;
                    }
                }
            }
        }
        $row_count++;
    }
    fclose($handle);
    
    // معکوس کردن آرایه برای نمایش جدیدترین موارد در بالا
    $reversed_rows = array_reverse($all_rows);
    
    // نمایش جدول
    echo '<table>';
    
    // نمایش هدر
    echo '<thead><tr>';
    echo '<th>#</th>'; // ستون شماره
    foreach ($header as $col) {
        echo '<th>' . htmlspecialchars($col) . '</th>';
    }
    echo '</tr></thead><tbody>';
    
    // نمایش داده‌ها به ترتیب معکوس (جدیدترین اول)
    $display_index = 1;
    foreach ($reversed_rows as $row) {
        $safe_data = array_map('htmlspecialchars', $row['data']);
        
        echo '<tr>';
        
        // ستون شماره با نشانگر جدید برای لیدهای امروز
        echo '<td style="text-align: center;">';
        echo '<span class="row-number">' . $display_index . '</span>';
        if ($row['is_today']) {
            echo '<span class="new-badge">امروز</span>';
        }
        echo '</td>';
        
        $col_index = 0;
        foreach ($safe_data as $col) {
            // اگر این ستون تاریخ است (اولین ستون داده)
            if ($col_index == 0 && strpos($col, '(') !== false) {
                // جدا کردن تاریخ میلادی و شمسی
                $date_parts = explode('(', $col);
                $miladi = trim($date_parts[0]);
                $shamsi = isset($date_parts[1]) ? trim(str_replace(')', '', $date_parts[1])) : '';
                
                echo '<td class="date-cell">';
                echo '<span class="shamsi-date">' . $shamsi . '</span><br>';
                echo '<span class="miladi-date">' . $miladi . '</span>';
                echo '</td>';
            } else {
                echo '<td>' . $col . '</td>';
            }
            $col_index++;
        }
        echo '</tr>';
        $display_index++;
    }
    
    echo '</tbody></table>';
    
    // نمایش اطلاعات آماری
    echo '<div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; text-align: center;">';
    echo '<strong>تعداد کل رکوردها: ' . $total_leads . '</strong>';
    if ($today_leads > 0) {
        echo ' | <span style="color: #dc3545;">' . $today_leads . ' مورد امروز (' . $today_shamsi . ')</span>';
    }
    echo '</div>';
    
} else {
    echo '<p style="color: red;">خطا در باز کردن فایل.</p>';
}

// اگر هیچ داده‌ای وجود ندارد
if ($total_leads == 0) {
    echo '<p style="text-align: center; color: #666; padding: 20px; background: #f9f9f9; border-radius: 5px;">';
    echo 'هنوز هیچ داده‌ای ثبت نشده است.';
    echo '</p>';
}

echo '</div>
</body></html>';
?>