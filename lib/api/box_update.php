<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$response = array();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response);
?>
