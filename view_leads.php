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
    </style>

  </head>
<body>
<div class="container">';

echo '<h1>لیست لیدهای ثبت‌شده</h1>';

// لینک برای دانلود مستقیم CSV
echo '<a href="' . $csv_file . '" download="' . $csv_file . '" class="export-link">دانلود فایل CSV</a>';


// باز کردن فایل برای خواندن
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    echo '<table>';
    $row_count = 0;
    
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
            foreach ($safe_data as $col) {
                echo '<td>' . $col . '</td>';
            }
            echo '</tr>';
        }
        $row_count++;
    }
    
    echo '</tbody></table>';
    fclose($handle);
} else {
    echo '<p style="color: red;">خطا در باز کردن فایل.</p>';
}

echo '</div>
  </body></html>';
?>