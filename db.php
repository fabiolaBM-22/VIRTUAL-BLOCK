<?php
// db.php

$host = '***********'; 
$dbname = '***********'; 
$username = '***********'; 
$password = '***********'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si falla
    die("Error crítico de conexión: " . $e->getMessage());
}
?>
