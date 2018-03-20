<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$html_path = $ini_array['repository']['html_path'];
$box_dir = $ini_array['repository']['box_dir'];
$meta_dir = $ini_array['repository']['json_dir'];
$response = array();

function transform_input($value='')
{
  $value = trim($value);
  $value = htmlspecialchars($value);
  return $value;
}

function json_box_delete()
{
  global $response;
  global $html_path;
  global $meta_dir;
  global $box_dir;

  $json_file = str_replace('/', '_', transform_input($_GET['name'])) . '.json';
  $json_path = $meta_dir . $json_file;
  $file_data = file_get_contents($json_path);
  $json_data = json_decode($file_data, true);
  $box_file = $json_data['versions'][0]['providers'][0]['url'];
  $box_path = $box_dir . basename(parse_url($box_file, PHP_URL_PATH));

  if (is_file($json_path)) {
    unlink($json_path);
  }
  if (is_file($box_path)) {
    unlink($box_path);
  }

  $response['json_path'] = $json_path;
  $response['box_path'] = $box_path;
  $response['status'] = true;
  $response['message'] = 'Box successful deleted';
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user'])))
{
  if ((strcmp($_SERVER['REQUEST_METHOD'], 'GET') == 0) && (isset($_GET['name'])))
  {
    json_box_delete();
  } else {
    $response['status'] = false;
    $response['message'] = 'Bad request';
  }
} else {
  $response['status'] = false;
  $response['message'] = 'Access to content prohibited';
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($response);
?>
