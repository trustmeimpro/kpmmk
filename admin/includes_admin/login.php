<?php
session_start();
include_once '../../includes/database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM login_admin WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ../dashboard_admin.php');
        exit();
    } else {
        $error = 'Username atau password salah!';
        header('Location: ../login_form.php?error=' . urlencode($error));
        exit();
    }
} else {
    header('Location: ../login_form.php');
    exit();
}
