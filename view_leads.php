<?php
// =========================================================================
// نام فایل: view_leads.php
// وظیفه: نمایش محتوای فایل leads.csv در قالب جدول HTML
// =========================================================================

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
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .export-link { display: inline-block; padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .date-cell { font-family: "Segoe UI", Tahoma, sans-serif; direction: ltr; text-align: center; }
        .shamsi-date { color: #d63384; font-weight: bold; }
        .miladi-date { color: #6c757d; font-size: 0.9em; }
        .count-badge { background: #17a2b8; color: white; padding: 5px 10px; border-radius: 15px; margin-right: 10px; }
    </style>
</head>
<body>
<div class="container">';

echo '<h1><span class="count-badge" id="totalCount">0</span>لیست لیدهای ثبت‌شده</h1>';

// لینک برای دانلود مستقیم CSV
echo '<a href="' . $csv_file . '" download="' . $csv_file . '" class="export-link">دانلود فایل CSV</a>';

// باز کردن فایل برای خواندن
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    echo '<table>';
    $row_count = 0;
    $data_count = 0;
    
    // حلقه برای خواندن هر سطر
    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        // فیلتر کردن مقادیر برای جلوگیری از نمایش کدهای احتمالی
        $safe_data = array_map('htmlspecialchars', $data);

        if ($row_count == 0) {
            // سطر اول: هدر جدول
            echo '<thead><tr>';
            foreach ($safe_data as $col) {
                echo '<th>' . $col . '</th>';
            }
            echo '</tr></thead><tbody>';
        } else {
            // سایر سطرها: داده‌های جدول
            echo '<tr>';
            $col_index = 0;
            foreach ($safe_data as $col) {
                // اگر این ستون تاریخ است (اولین ستون)، فرمت مخصوص بده
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
            $data_count++;
        }
        $row_count++;
    }
    
    echo '</tbody></table>';
    fclose($handle);
    
    // نمایش تعداد کل رکوردها
    echo '<script>document.getElementById("totalCount").textContent = "' . $data_count . '";</script>';
    
} else {
    echo '<p style="color: red;">خطا در باز کردن فایل.</p>';
}

// اگر هیچ داده‌ای وجود ندارد
if ($data_count == 0 && $row_count > 0) {
    echo '<p style="text-align: center; color: #666; padding: 20px;">هیچ داده‌ای ثبت نشده است.</p>';
}

echo '</div>
</body></html>';
?>