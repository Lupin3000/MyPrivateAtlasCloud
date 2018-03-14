<?php
session_start();

$ini_array = parse_ini_file("./config.ini", true);
$box_dir = $ini_array['repository']['box_dir'];
$meta_dir = $ini_array['repository']['json_dir'];
$response = array();

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
  $json_file = $meta_dir . str_replace('/boxes/meta/', '', $json_url);
  $box_file = $box_dir . str_replace('/meta/', '', str_replace('.json', '', $json_file));

  if(is_file('.' . $json_file)) {
    unlink('.' . $json_file);
  }
  if(is_file('.' . $box_file)) {
    unlink('.' . $box_file);
  }

  $response['json'] = $json_file;
  $response['box'] = $box_file;
  $response['status'] = true;
  $response['message'] = 'Your box is deleted.';
}

if (!isset($_SESSION['valid']) || !isset($_SESSION['user']))
{
  $response['status'] = false;
  $response['message'] = 'You are not allowed to delete boxes.';
} else {
  delete_json();
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($response);
?>
