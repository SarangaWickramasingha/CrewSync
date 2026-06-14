<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

if($conn){
    echo "Database Connected Successfully";
}else {
    echo "Database Connection Failed";
}