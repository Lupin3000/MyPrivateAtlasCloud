<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$domain = $ini_array['server']['URL'];
$html_path = $ini_array['repository']['html_path'];
$meta_dir = $ini_array['repository']['json_dir'];
$glob_pattern = $meta_dir . '*.json';
$response = array();

function truncate($string, $length=100, $append="...")
{
  $string = trim($string);
  if (strlen($string) > $length)
  {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }
  return $string;
}

function json_box_list($domain, $meta_dir, $glob_pattern)
{
  global $response;
  global $html_path;

  $response['status'] = true;
  $response['message'] = 'The list of current boxes';

  foreach (array_filter(glob($glob_pattern), 'is_file') as $entry)
  {
    $file_data = file_get_contents($entry);
    $json_data = json_decode($file_data, true);

    $json_url = $domain . $meta_dir . basename($entry);
    $json_url = str_replace($html_path, '', $json_url);
    $box_name = $json_data['name'];
    $box_description = truncate($json_data['description'], 25, '...');
    $box_provider = $json_data['versions'][0]['providers'][0]['name'];
    $box_url  = $json_data['versions'][0]['providers'][0]['url'];
    $box_version = $json_data['versions'][0]['version'];
    $box_checksum = $json_data['versions'][0]['providers'][0]['checksum'];
    $box_checksum_type = $json_data['versions'][0]['providers'][0]['checksum_type'];

    array_push($response, array('json_url' => $json_url,
                                'box_url' => $box_url,
                                'name' => $box_name,
                                'description' => $box_description,
                                'provider' => $box_provider));
  }
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user'])))
{
  if (strcmp($_SERVER['REQUEST_METHOD'], 'GET') == 0)
  {
    json_box_list($domain, $meta_dir, $glob_pattern);
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
