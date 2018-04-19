<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$domain = $ini_array['server']['URL'];
$html_path = $ini_array['repository']['html_path'];
$meta_dir = $ini_array['repository']['json_dir'];
$box_dir = $ini_array['repository']['box_dir'];
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
  global $domain;
  global $html_path;
  global $meta_dir;
  global $box_dir;

  $json_file = str_replace('/', '_', transform_input($_GET['name'])) . '.json';
  $json_path = $meta_dir . $json_file;
  $json_url = $domain . str_replace($html_path, '', $json_path);
  $file_data = file_get_contents($json_path);
  $json_data = json_decode($file_data, true);
  $response['deleted'] = array();

  foreach ($json_data['versions'] as $item) {
    $box = basename(parse_url($item['providers'][0]['url'], PHP_URL_PATH));
    $box_path = $box_dir . $box;
    array_push($response['deleted'], array('box' => $item['providers'][0]['url'],
                                           'version' => $item['version']));

    if (is_file($box_path)) {
      unlink($box_path);
    }
  }

  if (is_file($json_path)) {
    unlink($json_path);
  }

  $response['json'] = $json_url;
  $response['status'] = true;
  $response['message'] = 'All files successful deleted';
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
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Max-Age: 3600');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response);
?>
