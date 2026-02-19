<?php
session_start();

// Si no estamos en la página de login y el usuario no está logueado, redirigir
if (basename($_SERVER['PHP_SELF']) != 'login.php' && (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true)) {
    header('Location: login.php');
    exit;
}