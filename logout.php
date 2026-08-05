<?php
session_start();
unset($_SESSION['user']);

if (isset($_GET['returnUrl'])) {
    header('Location: '.$_GET['returnUrl']);
    exit();
} else {
    header('Location: index.php');
    exit();
}