<?php
// =========================================================================
// نام فایل: view_leads.php
// وظیفه: نمایش محتوای فایل leads.csv در قالب جدول HTML (جدیدترین ها در بالا)
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

echo '<h1><span class="count-badge" id="totalCount">0</span>لیست لیدهای ثبت‌شده</h1>';

// لینک‌های اکشن
echo '<a href="view_leads.php" class="refresh-link">🔄 بروزرسانی</a>';
echo '<a href="' . $csv_file . '" download="' . $csv_file . '" class="export-link">📥 دانلود فایل CSV</a>';

// باز کردن فایل برای خواندن
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
                'original_index' => $row_count
            ];
        }
        $row_count++;
    }
    fclose($handle);
    
    // معکوس کردن آرایه برای نمایش جدیدترین موارد در بالا
    $reversed_rows = array_reverse($all_rows);
    $total_data_count = count($all_rows);
    
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
        
        // ستون شماره با نشانگر جدید برای موارد اخیر
        echo '<td style="text-align: center;">';
        echo '<span class="row-number">' . $display_index . '</span>';
        if ($display_index <= 5) {
            echo '<span class="new-badge">جدید</span>';
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
    echo '<strong>تعداد کل رکوردها: ' . $total_data_count . '</strong>';
    if ($total_data_count > 0) {
        echo ' | <span style="color: #dc3545;">' . min(5, $total_data_count) . ' مورد جدید</span>';
    }
    echo '</div>';
    
} else {
    echo '<p style="color: red;">خطا در باز کردن فایل.</p>';
}

// اگر هیچ داده‌ای وجود ندارد
if ($total_data_count == 0 && $row_count > 0) {
    echo '<p style="text-align: center; color: #666; padding: 20px; background: #f9f9f9; border-radius: 5px;">';
    echo 'هنوز هیچ داده‌ای ثبت نشده است.';
    echo '</p>';
}

echo '</div>
</body></html>';
?>