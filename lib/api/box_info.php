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
  $all_versions = array();
  $response['boxes'] = array();

  foreach ($json_data['versions'] as $item) {
    $all_versions[] = $item['version'];
    array_push($response['boxes'], array('version' => $item['version'],
                                         'provider' => $item['providers'][0]['name'],
                                         'url' => $item['providers'][0]['url'],
                                         'checksum_type' => $item['providers'][0]['checksum_type'],
                                         'checksum' => $item['providers'][0]['checksum']));
  }

  $latest_v = max($all_versions);

  $response['name'] = $json_data['name'];
  $response['description'] = $json_data['description'];
  $response['json_url'] = $json_url;
  $response['latestversion'] = $latest_v;

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
