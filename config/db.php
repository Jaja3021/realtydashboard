<?php
// Reads Railway's MySQL plugin env vars when present, falls back to local XAMPP defaults.
$DB_HOST = getenv('MYSQLHOST') ?: 'localhost';
$DB_PORT = getenv('MYSQLPORT') ?: '3306';
$DB_NAME = getenv('MYSQLDATABASE') ?: 'realty_dashboard';
$DB_USER = getenv('MYSQLUSER') ?: 'root';
$DB_PASS = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database connection failed. Import db.sql via phpMyAdmin first. (' . $e->getMessage() . ')');
}

$PROPERTY_TYPES = ['House and Lot', 'Condominium', 'Lot Only', 'Townhouse', 'Commercial'];
