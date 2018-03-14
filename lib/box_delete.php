<?php
session_start();

$ini_array = parse_ini_file('./config/config.ini', true);
$box_dir = dirname(__FILE__) . $ini_array['repository']['box_dir'];
$meta_dir = dirname(__FILE__) . $ini_array['repository']['json_dir'];
$response = array();

function transform_input($value='')
{
  $value = trim($value);
  $value = htmlspecialchars($value);
  return $value;
}

function json_box_delete($box_dir, $meta_dir)
{
  global $response;

  $url = transform_input($_GET['url']);
  $json_url = parse_url($url, PHP_URL_PATH);
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

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user'])))
{
  if ((strcmp($_SERVER['REQUEST_METHOD'], 'GET') == 0) && (isset($_GET['url'])))
  {
    json_box_delete($box_dir, $meta_dir);
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
