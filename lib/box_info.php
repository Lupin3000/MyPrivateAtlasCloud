<?php
session_start();

$ini_array = parse_ini_file('./config/application.ini', true);
$domain = $ini_array['server']['URL'];
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

  $response['status'] = true;
  $response['message'] = 'Box information';

  $url = transform_input($_GET['url']);
  $path = dirname(__FILE__) . str_replace($domain, '', $url);
  $file_data = file_get_contents($path);
  $json_data = json_decode($file_data, true);

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
                              'url' => $box_url,
                              'version' => $box_version,
                              'checksum' => $box_checksum,
                              'checksum_type' => $box_checksum_type));
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user'])))
{
  if ((strcmp($_SERVER['REQUEST_METHOD'], 'GET') == 0) && (isset($_GET['url'])))
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
header('Content-type: application/json');
echo json_encode($response);
?>
