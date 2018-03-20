<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$domain = $ini_array['server']['URL'];
$box_dir = dirname(__FILE__) . $ini_array['repository']['box_dir'];
$meta_dir = dirname(__FILE__) . $ini_array['repository']['json_dir'];
$box_url = $domain . str_replace(dirname(__FILE__), '', $box_dir);
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
  global $meta_dir;
  global $box_url;
  global $time;

  $checksum = sha1_file($newname);
  $box_name = basename($newname);
  //$json_name = str_replace('.box', '', $meta_dir . $box_name) . '.json';
  $json_name = $meta_dir . str_replace('/', '_', transform_input($_POST['boxname'])) . '.json';

  $content = array(
    'name' => transform_input($_POST['boxname']),
    'description' => transform_input($_POST['boxdescription']),
    'versions' => array(
      array(
        'version' => (string) $time,
        'providers' => array(
          array(
            'name' => transform_input($_POST['boxprovider']),
            'url' => $box_url . $box_name,
            'checksum_type' => 'sha1',
            'checksum' => $checksum
          )
        )
      )
    )
  );

  $json_data = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  file_put_contents($json_name, $json_data);

  $response['status'] = true;
  $response['message'] = 'Your box is successful uploaded';
  $response['name'] = $_POST['boxname'];
  $response['provider'] = $_POST['boxprovider'];
  $response['description'] = $_POST['boxdescription'];
  $response['file'] = $_FILES['boxfile']['name'];
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
