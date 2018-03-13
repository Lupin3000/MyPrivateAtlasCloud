<?php
session_start();

$response = array();
$ini_array = parse_ini_file("../config.ini", true);
$domain = $ini_array['server']['URL'];
$meta_dir = $ini_array['repository']['json_dir'];

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

  if ($handle = opendir('.' . $meta_dir))
  {
    $response['status'] = true;
    $response['message'] = 'Your boxes for usage.';
    while (false !== ($entry = readdir($handle)))
    {
      if ($entry != "." && $entry != "..")
      {
        $json = file_get_contents('.' . $meta_dir . $entry);
        $data = json_decode($json, true);
        $file = $domain . '/boxes' . $meta_dir . $entry;
        $short_desc = truncate($data['description'], 25, '...');
        $provider = $data['versions'][0]['providers'][0]['name'];
        $url  = $data['versions'][0]['providers'][0]['url'];
        $version = $data['versions'][0]['version'];
        $checksum = $data['versions'][0]['providers'][0]['checksum'];
        $checksum_type = $data['versions'][0]['providers'][0]['checksum_type'];
        array_push($response, array('file'=>$file,
                                    'name'=>$data['name'],
                                    'description_l'=>$data['description'],
                                    'description_s'=>$short_desc,
                                    'provider'=>$provider,
                                    'url'=>$url,
                                    'version'=>$version,
                                    'checksum'=>$checksum,
                                    'checksum_type'=>$checksum_type));
      }
    }
    closedir($handle);
  } else {
    $response['status'] = false;
    $response['message'] = 'Internal error, could not open meta directory';
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
