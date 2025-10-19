<?php
// فایل: logout.php
// وظیفه: خروج از session

session_start();
session_destroy();
header('Location: login.php');
exit;
?>