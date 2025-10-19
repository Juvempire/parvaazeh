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

$from_record = $offset + 1;
$to_record = min($offset + $records_per_page, $total_leads);

// تولید کد HTML
echo '<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لیست لیدهای ثبت‌شده - صفحه ' . $current_page . '</title>
    <!-- لینک فونت Vazirmatn از Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: \'Vazirmatn\', sans-serif; 
            background-color: #f4f4f4; 
            padding: 20px; 
        }
        .container { max-width: 1200px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: right; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .new-badge { background: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; margin-right: 5px; }
        .date-cell { direction: ltr; text-align: right; }
        .shamsi-date { font-weight: bold; color: #007bff; }
        .miladi-date { color: #6c757d; font-size: 0.9em; }
        .page-info { text-align: center; color: #666; margin-top: 10px; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; margin: 0 4px; background: #f2f2f2; border-radius: 4px; text-decoration: none; color: #333; }
        .pagination a:hover { background: #ddd; }
        .pagination .current { background: #007bff; color: white; }
        .pagination .disabled { color: #ccc; }
        .row-number { font-weight: bold; }
        .refresh-link, .export-link { 
            display: inline-block; padding: 10px 15px; background: #007bff; color: white; 
            text-decoration: none; border-radius: 5px; margin: 10px 5px; 
        }
        .refresh-link:hover, .export-link:hover { background: #0056b3; }

        /* استایل برای دکمه کپی */
        .copy-btn { 
            background: #28a745; color: white; border: none; padding: 6px 12px; 
            border-radius: 4px; cursor: pointer; font-size: 0.9em; 
        }
        .copy-btn:hover { background: #218838; }

        /* استایل برای پیام موفقیت */
        .copy-message { 
            position: fixed; top: 20px; right: 20px; background: #28a745; color: white; 
            padding: 10px 20px; border-radius: 5px; display: none; z-index: 1000; 
        }

        /* استایل برای sticky header */
        thead th {
            position: sticky;
            top: 0;
            background: #f2f2f2;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>لیست لیدهای ثبت‌شده (جدیدترین اول)</h1>
        
        <a href="?page=' . $current_page . '" class="refresh-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" style="margin-left: 5px; vertical-align: middle;">
                <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
            </svg>
            بروزرسانی
        </a>

        <a href="' . $csv_file . '" download="' . $csv_file . '" class="export-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" style="margin-left: 5px; vertical-align: middle;">
                <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
            </svg>
            دانلود فایل CSV
        </a>

        <div class="page-info">
            نمایش ' . $from_record . ' تا ' . $to_record . ' از ' . $total_leads . ' رکورد
        </div>

        ' . (!empty($paginated_rows) ? '<table>
            <thead>
                <tr>
                    <th>#</th>
                    ' . implode('', array_map(function($col) { return '<th>' . htmlspecialchars($col) . '</th>'; }, $header)) . '
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>' : '<p style="text-align: center; color: #666; padding: 40px;">هیچ داده‌ای یافت نشد.</p>') . '

        ' . (!empty($paginated_rows) ? implode('', array_map(function($row, $display_index) {
            $safe_data = array_map('htmlspecialchars', $row['data']);
            $output = '<tr>';
            $output .= '<td style="text-align: center;">';
            $output .= '<span class="row-number">' . $display_index . '</span>';
            if ($row['is_today']) {
                $output .= '<span class="new-badge">امروز</span>';
            }
            $output .= '</td>';
            
            $col_index = 0;
            foreach ($safe_data as $col) {
                if ($col_index == 0 && strpos($col, '(') !== false) {
                    $date_parts = explode('(', $col);
                    $miladi = trim($date_parts[0]);
                    $shamsi = isset($date_parts[1]) ? trim(str_replace(')', '', $date_parts[1])) : '';
                    
                    $output .= '<td class="date-cell">';
                    $output .= '<span class="shamsi-date">' . $shamsi . '</span><br>';
                    $output .= '<span class="miladi-date">' . $miladi . '</span>';
                    $output .= '</td>';
                } else {
                    $output .= '<td>' . $col . '</td>';
                }
                $col_index++;
            }
            
            $output .= '<td><button class="copy-btn" onclick="copyRow(this)">کپی</button></td>';
            $output .= '</tr>';
            return $output;
        }, $paginated_rows, range($from_record, $to_record))) : '') . '
        
        ' . (!empty($paginated_rows) ? '</tbody></table>' : '') . '
        
        ' . ($total_pages > 1 ? '<div class="pagination">' .
            ($current_page > 1 ? '<a href="?page=' . ($current_page - 1) . '">« قبلی</a>' : '<span class="disabled">« قبلی</span>') .
            ($start_page = max(1, $current_page - 2)) .
            ($end_page = min($total_pages, $current_page + 2)) .
            ($start_page > 1 ? '<a href="?page=1">1</a>' . ($start_page > 2 ? '<span>...</span>' : '') : '') .
            implode('', array_map(function($i) use ($current_page) {
                return $i == $current_page ? '<span class="current">' . $i . '</span>' : '<a href="?page=' . $i . '">' . $i . '</a>';
            }, range($start_page, $end_page))) .
            ($end_page < $total_pages ? ($end_page < $total_pages - 1 ? '<span>...</span>' : '') . '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>' : '') .
            ($current_page < $total_pages ? '<a href="?page=' . ($current_page + 1) . '">بعدی »</a>' : '<span class="disabled">بعدی »</span>') .
        '</div>' : '') . '
        
        <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; text-align: center;">
            <strong>تعداد کل رکوردها: ' . $total_leads . '</strong>' . 
            ($today_leads > 0 ? ' | <span style="color: #dc3545;">' . $today_leads . ' مورد امروز (' . $today_shamsi . ')</span>' : '') . 
        '</div>
        
        <div id="copyMessage" class="copy-message">ردیف با موفقیت کپی شد!</div>
    </div>
    
    <script>
        function copyRow(button) {
            const row = button.closest(\'tr\');
            const cells = row.querySelectorAll(\'td:not(:last-child)\');  // همه سلول‌ها جز عملیات
            
            let rowText = \'\';
            for (let i = 1; i < cells.length; i++) {  // از i=1 برای رد # (سلول 0)
                let cell = cells[i];
                let text;
                if (i === 1) {  // سلول تاریخ (فقط شمسی)
                    text = cell.querySelector(\'.shamsi-date\')?.innerText.trim() || cell.innerText.trim();
                } else {
                    text = cell.innerText.trim();
                }
                rowText += text + (i < cells.length - 1 ? \' | \' : \'\');
            }
            
            navigator.clipboard.writeText(rowText).then(() => {
                const message = document.getElementById(\'copyMessage\');
                message.style.display = \'block\';
                setTimeout(() => { message.style.display = \'none\'; }, 2000);
            }).catch(err => {
                console.error(\'خطا در کپی: \', err);
            });
        }
    </script>
</body>
</html>';