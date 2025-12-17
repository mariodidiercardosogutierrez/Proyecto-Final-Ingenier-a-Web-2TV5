<?php
$host = "localhost";
$user = "MauricioR";
$pass = "Ramos2916##";
$dbname = "ingenieriaweb";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>

