<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$domain = $ini_array['server']['URL'];
$html_path = $ini_array['repository']['html_path'];
$box_dir = $ini_array['repository']['box_dir'];
$meta_dir = $ini_array['repository']['json_dir'];
$time = time();
$response = array();

function transform_input($value='')
{
  $value = trim($value);
  $value = htmlspecialchars($value);
  return $value;
}

function create_update_json($newname)
{
  global $response;
  global $html_path;
  global $meta_dir;
  global $box_dir;
  global $domain;
  global $time;

  $checksum = sha1_file($newname);
  $box_file = basename($newname);
  $json_file = str_replace('/', '_', transform_input($_POST['boxname'])) . '.json';
  $json_path = $meta_dir . $json_file;
  $box_url = $domain . str_replace($html_path, '', $box_dir) . $box_file;

  $content = array(
    'name' => transform_input($_POST['boxname']),
    'description' => transform_input($_POST['boxdescription']),
    'versions' => array(
      array(
        'version' => (string) $time,
        'providers' => array(
          array(
            'name' => transform_input($_POST['boxprovider']),
            'url' => $box_url,
            'checksum_type' => 'sha1',
            'checksum' => $checksum
          )
        )
      )
    )
  );

  $json_data = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  file_put_contents($json_path, $json_data);

  $response['status'] = true;
  $response['message'] = 'Your box is successful uploaded';
  $response['name'] = transform_input($_POST['boxname']);
  $response['provider'] = transform_input($_POST['boxprovider']);
  $response['description'] = transform_input($_POST['boxdescription']);
  $response['box'] = $box_file;
  $response['json'] = $json_file;
}

function move_file($filename)
{
  global $response;
  global $box_dir;
  global $time;

  $newname = $box_dir . str_replace('.box', '_' . $time . '.box', $filename);

  if (move_uploaded_file($_FILES['boxfile']['tmp_name'], $newname))
  {
    create_update_json($newname);
  } else {
    $response['status'] = false;
    $response['message'] = 'Could not move upload to target dir';
  }
}

function check_file()
{
  global $response;

  if((!empty($_FILES["boxfile"])) && ($_FILES['boxfile']['error'] == 0))
  {
    $filename = basename($_FILES['boxfile']['name']);
    $ext = substr($filename, strrpos($filename, '.') + 1);
    $mime = mime_content_type($_FILES['boxfile']['tmp_name']);

    if (($ext === "box") && ($mime === "application/x-gzip"))
    {
      move_file($filename);
    } else {
      $response['status'] = false;
      $response['message'] = 'You upload a Vagrant box?';
    }
  } else {
    $response['status'] = false;
    $response['message'] = 'Something went wrong with upload.';
  }
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user'])))
{
  if (strcmp($_SERVER['REQUEST_METHOD'], 'POST') == 0)
  {
    check_file();
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
