<?php

require_once "config/Env.php";
Env::load();
require_once "config/database.php";

$db = Database::getInstance();
$conn = $db->getConnection();

if ($conn) {
    echo "Database Connected Successfully";
} else {
    echo "Database Connection Failed";
}