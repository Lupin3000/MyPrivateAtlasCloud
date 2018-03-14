<?php
session_start();

$ini_array = parse_ini_file("./config.ini", true);
$domain = $ini_array['server']['URL'];
$box_dir = $ini_array['repository']['box_dir'];
$meta_dir = $ini_array['repository']['json_dir'];
$response = array();

function transform_input($value='')
{
  $value = trim($value);
  $value = htmlspecialchars($value);
  return $value;
}

function create_update_json($newname)
{
  global $ini_array;
  global $domain;
  global $response;
  global $meta_dir;
  global $box_dir;

  $checksum = sha1_file($newname);
  $boxname = basename($newname);
  $jsonname = dirname(__FILE__) . $meta_dir . $boxname . '.json';

  $content = array(
    'name' => transform_input($_POST['boxname']),
    'description' => transform_input($_POST['boxdescription']),
    'versions' => array(
      array(
        'version' => time(),
        'providers' => array(
          array(
            'name' => $_POST['boxprovider'],
            'url' => $domain . '/boxes' . $box_dir . $boxname,
            'checksum_type' => 'sha1',
            'checksum' => $checksum
          )
        )
      )
    )
  );

  $json_data = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  file_put_contents($jsonname, $json_data);

  $response['status'] = true;
  $response['message'] = 'Your box is successful uploaded.';
  $response['name'] = $_POST['boxname'];
  $response['provider'] = $_POST['boxprovider'];
  $response['description'] = $_POST['boxdescription'];
  $response['file'] = $_FILES['boxfile']['name'];
}

function move_file($filename)
{
  global $response;
  global $box_dir;

  $newname = dirname(__FILE__) . $box_dir . $filename;

  if (move_uploaded_file($_FILES['boxfile']['tmp_name'], $newname))
  {
    create_update_json($newname);
  } else {
    $response['status'] = false;
    $response['message'] = 'Could not move upload to target dir.';
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

function check_request_method($request)
{
  global $response;

  if (strcmp($_SERVER['REQUEST_METHOD'], $request) == 0)
  {
    return true;
  } else {
    $response['status'] = false;
    $response['message'] = 'Bad request!';
    return false;
  }
}

function check_session()
{
  global $response;

  if (!isset($_SESSION['valid']) || !isset($_SESSION['user']))
  {
    $response['status'] = false;
    $response['message'] = 'You are not allowed to upload a box.';
    return false;
  } else {
    return true;
  }
}

if (check_session())
{
  if (check_request_method('POST'))
  {
    check_file();
  }
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($response);
?>
