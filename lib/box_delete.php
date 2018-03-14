<?php
session_start();

$response = array();
$ini_array = parse_ini_file('./config/config.ini', true);
$box_dir = dirname(__FILE__) . $ini_array['repository']['box_dir'];
$meta_dir = dirname(__FILE__) . $ini_array['repository']['json_dir'];

function transform_input($value='')
{
  $value = trim($value);
  $value = htmlspecialchars($value);
  return $value;
}

function delete_json()
{
  global $response;
  global $box_dir;
  global $meta_dir;

  $json_url = parse_url(transform_input($_GET['url']), PHP_URL_PATH);
  $json_file = $meta_dir . basename($json_url);
  $box_file = $box_dir . str_replace('.json', '.box', basename($json_url));

  if(is_file($json_file)) {
    unlink($json_file);
  }
  if(is_file($box_file)) {
    unlink($box_file);
  }

  $response['json_path'] = $json_file;
  $response['box_path'] = $box_file;
  $response['status'] = true;
  $response['message'] = 'Box successful deleted';
}

if (!isset($_SESSION['valid']) || !isset($_SESSION['user']))
{
  $response['status'] = false;
  $response['message'] = 'You are not allowed to delete boxes';
} else {
  delete_json();
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($response);
?>
