<?php
$host = 'sql101.infinityfree.com';
$dbname = 'if0_39311099_ecommerce';
$user = 'if0_39311099';
$password = '8RrcJgI1NfXOQ';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>