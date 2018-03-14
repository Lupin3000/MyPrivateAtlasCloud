<?php
session_start();

$response = array();
$ini_array = parse_ini_file('./config/config.ini', true);
$domain = $ini_array['server']['URL'];
$meta_dir = $ini_array['repository']['json_dir'];
$glob_pattern = dirname(__FILE__) . $meta_dir . '*.json';

function truncate($string, $length=100, $append="...")
{
  $string = trim($string);
  if(strlen($string) > $length)
  {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }
  return $string;
}

function box_json()
{
  global $response;
  global $domain;
  global $meta_dir;
  global $glob_pattern;

  $response['status'] = true;
  $response['message'] = 'Your current box list.';

  foreach (array_filter(glob($glob_pattern), 'is_file') as $entry)
  {
    $file_data = file_get_contents($entry);
    $json_data = json_decode($file_data, true);

    $json_url = $domain . $meta_dir . basename($entry);
    $box_name = $json_data['name'];
    $box_desc_long = $json_data['description'];
    $box_desc_short = truncate($json_data['description'], 25, '...');
    $box_provider = $json_data['versions'][0]['providers'][0]['name'];
    $box_url  = $json_data['versions'][0]['providers'][0]['url'];
    $box_version = $json_data['versions'][0]['version'];
    $box_checksum = $json_data['versions'][0]['providers'][0]['checksum'];
    $box_checksum_type = $json_data['versions'][0]['providers'][0]['checksum_type'];

    array_push($response, array('file' => $json_url,
                                'name' => $box_name,
                                'description_l' => $box_desc_long,
                                'description_s' => $box_desc_short,
                                'provider' => $box_provider,
                                'url' => $box_url,
                                'version' => $box_version,
                                'checksum' => $box_checksum,
                                'checksum_type' => $box_checksum_type));
  }
}

if (!isset($_SESSION['valid']) || !isset($_SESSION['user']))
{
  $response['status'] = false;
  $response['message'] = 'You are not allowed to list boxes.';
} else {
  box_json();
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($response);
?>
