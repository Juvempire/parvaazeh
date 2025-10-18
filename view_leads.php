<?php
// =========================================================================
// نام فایل: view_leads.php
// وظیفه: نمایش محتوای فایل leads.csv در قالب جدول HTML با Pagination (20 رکورد در صفحه)
// =========================================================================

// تنظیم منطقه زمانی به تهران
date_default_timezone_set('Asia/Tehran');

// تنظیمات: نام فایل CSV
$csv_file = 'leads.csv';
$delimiter = ';'; // جداکننده مورد استفاده در fputcsv ما
$records_per_page = 20; // تعداد رکورد در هر صفحه

// بررسی وجود فایل
if (!file_exists($csv_file)) {
    die("خطا: فایل leads.csv پیدا نشد یا خالی است.");
}

// تنظیم هدر برای اطمینان از نمایش صحیح کاراکترهای فارسی (UTF-8)
header('Content-Type: text/html; charset=utf-8');

// محاسبه شماره صفحه
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

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

$shamsi_today = gregorian_to_jalali(date('Y'), date('m'), date('d'));
$today_shamsi = $shamsi_today[0] . '/' . $shamsi_today[1] . '/' . $shamsi_today[2];
$today_date = date('Y/m/d');

// خواندن تمام داده‌ها از فایل
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    $all_rows = [];
    $header = [];
    $row_count = 0;
    $total_leads = 0;
    $today_leads = 0;
    
    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        if ($row_count == 0) {
            $header = $data;
        } else {
            $is_today = false;
            if (isset($data[0])) {
                $date_cell = $data[0];
                if (strpos($date_cell, '(') !== false) {
                    $date_parts = explode('(', $date_cell);
                    $miladi_date = trim($date_parts[0]);
                    if ($miladi_date === $today_date) {
                        $is_today = true;
                        $today_leads++;
                    }
                }
            }
            
            $all_rows[] = [
                'data' => $data,
                'original_index' => $row_count,
                'is_today' => $is_today
            ];
            $total_leads++;
        }
        $row_count++;
    }
    fclose($handle);
    
    // معکوس کردن برای جدیدترین اول
    $reversed_rows = array_reverse($all_rows);
    
    // محاسبه Pagination
    $total_pages = ceil($total_leads / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    $paginated_rows = array_slice($reversed_rows, $offset, $records_per_page);
    
} else {
    die('خطا در باز کردن فایل.');
}

echo '<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لیست لیدهای ثبت‌شده - صفحه ' . $current_page . '</title>
    <style>
        body { font-family: Tahoma, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: right; }
        th { background-color: #007bff; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .export-link { display: inline-block; padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; margin-right: 10px; }
        .refresh-link { display: inline-block; padding: 10px 15px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .date-cell { font-family: "Segoe UI", Tahoma, sans-serif; direction: ltr; text-align: center; }
        .shamsi-date { color: #d63384; font-weight: bold; }
        .miladi-date { color: #6c757d; font-size: 0.9em; }
        .count-badge { background: #17a2b8; color: white; padding: 5px 10px; border-radius: 15px; margin-left: 10px; }
        .new-badge { background: #dc3545; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; margin-right: 5px; }
        .row-number { background: #6c757d; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; margin-left: 5px; }
        tr:hover { background-color: #e9f7fe !important; }
        .update-icon, .download-icon { color: #fff !important;}
        
        /* Pagination Styles */
        .pagination { 
            display: flex; 
            justify-content: center; 
            margin: 20px 0; 
            flex-wrap: wrap; 
        }
        .pagination a, .pagination span { 
            padding: 8px 12px; 
            margin: 0 2px; 
            text-decoration: none; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            color: #007bff; 
        }
        .pagination a:hover { 
            background-color: #007bff; 
            color: white; 
        }
        .pagination .current { 
            background-color: #007bff; 
            color: white; 
            border-color: #007bff; 
        }
        .pagination .disabled { 
            color: #6c757d; 
            cursor: not-allowed; 
            background-color: #f8f9fa; 
        }
        .page-info { 
            text-align: center; 
            margin: 15px 0; 
            color: #666; 
            font-size: 0.9em; 
        }
    </style>
</head>
<body>
<div class="container">';

echo '<h1><span class="count-badge">' . $total_leads . '</span>لیست لیدهای ثبت‌شده</h1>';

// لینک‌های اکشن
echo '<a href="view_leads.php?page=' . $current_page . '" class="refresh-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" style="margin-left: 5px; vertical-align: middle;">
        <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
      </svg>
      بروزرسانی
      </a>';

echo '<a href="' . $csv_file . '" download="' . $csv_file . '" class="export-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" style="margin-left: 5px; vertical-align: middle;">
        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
      </svg>
      دانلود فایل CSV
      </a>';

// نمایش اطلاعات صفحه
$from_record = $offset + 1;
$to_record = min($offset + $records_per_page, $total_leads);
echo '<div class="page-info">
        نمایش ' . $from_record . ' تا ' . $to_record . ' از ' . $total_leads . ' رکورد
      </div>';

// نمایش جدول
if (!empty($paginated_rows)) {
    echo '<table>';
    echo '<thead><tr>';
    echo '<th>#</th>';
    foreach ($header as $col) {
        echo '<th>' . htmlspecialchars($col) . '</th>';
    }
    echo '</tr></thead><tbody>';
    
    $display_index = $from_record;
    foreach ($paginated_rows as $row) {
        $safe_data = array_map('htmlspecialchars', $row['data']);
        
        echo '<tr>';
        echo '<td style="text-align: center;">';
        echo '<span class="row-number">' . $display_index . '</span>';
        if ($row['is_today']) {
            echo '<span class="new-badge">امروز</span>';
        }
        echo '</td>';
        
        $col_index = 0;
        foreach ($safe_data as $col) {
            if ($col_index == 0 && strpos($col, '(') !== false) {
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
} else {
    echo '<p style="text-align: center; color: #666; padding: 40px;">هیچ داده‌ای یافت نشد.</p>';
}

// Pagination Links
if ($total_pages > 1) {
    echo '<div class="pagination">';
    
    // Previous
    if ($current_page > 1) {
        echo '<a href="?page=' . ($current_page - 1) . '">« قبلی</a>';
    } else {
        echo '<span class="disabled">« قبلی</span>';
    }
    
    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    if ($start_page > 1) {
        echo '<a href="?page=1">1</a>';
        if ($start_page > 2) echo '<span>...</span>';
    }
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<span class="current">' . $i . '</span>';
        } else {
            echo '<a href="?page=' . $i . '">' . $i . '</a>';
        }
    }
    
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) echo '<span>...</span>';
        echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>';
    }
    
    // Next
    if ($current_page < $total_pages) {
        echo '<a href="?page=' . ($current_page + 1) . '">بعدی »</a>';
    } else {
        echo '<span class="disabled">بعدی »</span>';
    }
    
    echo '</div>';
}

// نمایش اطلاعات آماری
echo '<div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; text-align: center;">';
echo '<strong>تعداد کل رکوردها: ' . $total_leads . '</strong>';
if ($today_leads > 0) {
    echo ' | <span style="color: #dc3545;">' . $today_leads . ' مورد امروز (' . $today_shamsi . ')</span>';
}
echo '</div>';

echo '</div>
</body></html>';
?>