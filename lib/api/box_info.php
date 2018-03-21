<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$domain = $ini_array['server']['URL'];
$html_path = $ini_array['repository']['html_path'];
$meta_dir = $ini_array['repository']['json_dir'];
$response = array();

function transform_input($value='')
{
  $value = trim($value);
  $value = htmlspecialchars($value);
  return $value;
}

function json_box_info($domain)
{
  global $response;
  global $html_path;
  global $meta_dir;
  global $domain;

  $json_file = str_replace('/', '_', transform_input($_GET['name'])) . '.json';
  $json_path = $meta_dir . $json_file;
  $file_data = file_get_contents($json_path);
  $json_data = json_decode($file_data, true);
  $json_url = $domain . str_replace($html_path, '', $meta_dir) . $json_file;
  $box_name = $json_data['name'];
  $box_description = $json_data['description'];
  $box_provider = $json_data['versions'][0]['providers'][0]['name'];
  $box_version = $json_data['versions'][0]['version'];
  $box_checksum = $json_data['versions'][0]['providers'][0]['checksum'];
  $box_checksum_type = $json_data['versions'][0]['providers'][0]['checksum_type'];
  $box_url = $json_data['versions'][0]['providers'][0]['url'];

  array_push($response, array('name' => $box_name,
                              'description' => $box_description,
                              'provider' => $box_provider,
                              'box_url' => $box_url,
                              'json_url' => $json_url,
                              'version' => $box_version,
                              'checksum' => $box_checksum,
                              'checksum_type' => $box_checksum_type));

  $response['status'] = true;
  $response['message'] = 'Box information';
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user'])))
{
  if ((strcmp($_SERVER['REQUEST_METHOD'], 'GET') == 0) && (isset($_GET['name'])))
  {
    json_box_info($domain);
  } else {
    $response['status'] = false;
    $response['message'] = 'Bad request';
  }
} else {
  $response['status'] = false;
  $response['message'] = 'Access to content prohibited';
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response);
?>
