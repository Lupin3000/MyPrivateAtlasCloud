<?php
session_start();

$response = array();
$ini_array = parse_ini_file('./config/config.ini', true);
$domain = $ini_array['server']['URL'];

function show_box_info()
{
  global $response;
  global $domain;

  $response['status'] = true;
  $response['message'] = 'Box information';

  $url = htmlspecialchars(trim($_GET['url']));
  $path = dirname(__FILE__) . str_replace($domain, '', $url);

  $file_data = file_get_contents($path);
  $json_data = json_decode($file_data, true);

  $box_name = $json_data['name'];
  $box_description = $json_data['description'];
  $box_provider = $json_data['versions'][0]['providers'][0]['name'];
  $box_version = $json_data['versions'][0]['version'];
  $box_checksum = $json_data['versions'][0]['providers'][0]['checksum'];
  $box_checksum_type = $json_data['versions'][0]['providers'][0]['checksum_type'];
  $box_url  = $json_data['versions'][0]['providers'][0]['url'];

  array_push($response, array('name' => $box_name,
                              'description' => $box_description,
                              'provider' => $box_provider,
                              'url' => $box_url,
                              'version' => $box_version,
                              'checksum' => $box_checksum,
                              'checksum_type' => $box_checksum_type));
}

if (!isset($_SESSION['valid']) || !isset($_SESSION['user']))
{
  $response['status'] = false;
  $response['message'] = 'You are not allowed to show box info';
} else {
  if (!empty($_GET['url']))
  {
    show_box_info();
  } else {
    $response['status'] = false;
    $response['message'] = 'Wrong request';
  }
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($response);
?>
