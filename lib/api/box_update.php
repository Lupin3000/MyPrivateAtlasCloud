<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$domain = $ini_array['server']['URL'];
$versions = $ini_array['repository']['versions'];
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

function update_json($box_path, $json_path)
{
  global $response;
  global $versions;
  global $box_dir;
  global $html_path;
  global $domain;
  global $time;

  $checksum = sha1_file($box_path);
  $box_file = basename($box_path);
  $box_url = $domain . str_replace($html_path, '', $box_dir) . $box_file;
  $file_data = file_get_contents($json_path);
  $json_data = json_decode($file_data, true);

  $json_data['description'] = transform_input($_POST['boxdescription']);
  $new_box_obj = array(
    'version' => (string) $time,
    'providers' => array(
      array(
        'name' => transform_input($_POST['boxprovider']),
        'url' => $box_url,
        'checksum_type' => 'sha1',
        'checksum' => $checksum
      )
    )
  );
  array_push($json_data['versions'], $new_box_obj);

  $response['deleted'] = array();
  if ((int) count($json_data['versions']) > (int) $versions)
  {
    $box_min_url = min($json_data['versions'])['providers'][0]['url'];
    $box_min_version = min($json_data['versions'])['version'];
    $box_min_path = $box_dir . basename(parse_url($box_min_url, PHP_URL_PATH));
    $array_key = array_search($box_min_version, array_column($json_data['versions'], 'version'));
    if (is_file($box_min_path))
    {
      unlink($box_min_path);
      array_splice($json_data['versions'], $array_key, 1);
      array_push($response['deleted'], array('version' => $box_min_version,
                                             'box' => basename(parse_url($box_min_url, PHP_URL_PATH))));
    }
  }

  // write to file and close
  $json_data = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  file_put_contents($json_path, $json_data);

  $response['status'] = true;
  $response['message'] = 'Your box is successful updated';
  $response['name'] = transform_input($_POST['boxname']);
  $response['provider'] = transform_input($_POST['boxprovider']);
  $response['description'] = transform_input($_POST['boxdescription']);
  $response['box'] = $box_file;
  $response['json'] = basename($json_path);
}

function move_file($filename, $json_path)
{
  global $response;
  global $box_dir;
  global $time;

  $box_path = $box_dir . str_replace('.box', '_' . $time . '.box', $filename);

  if (move_uploaded_file($_FILES['boxfile']['tmp_name'], $box_path))
  {
    update_json($box_path, $json_path);
  } else {
    $response['status'] = false;
    $response['message'] = 'Could not move upload to target dir';
  }
}

function check_file_upload()
{
  global $response;
  global $meta_dir;

  if((!empty($_FILES["boxfile"])) && ($_FILES['boxfile']['error'] == 0))
  {
    $filename = basename($_FILES['boxfile']['name']);
    $ext = substr($filename, strrpos($filename, '.') + 1);
    $mime = mime_content_type($_FILES['boxfile']['tmp_name']);
    $json_file = str_replace('/', '_', transform_input($_POST['boxname'])) . '.json';
    $json_path = $meta_dir . $json_file;

    if (($ext === "box") && ($mime === "application/x-gzip"))
    {
      if (file_exists($json_path))
      {
        move_file($filename, $json_path);
      } else {
        $response['status'] = false;
        $response['message'] = 'Vagrant box not found!';
      }
    } else {
      $response['status'] = false;
      $response['message'] = 'You upload a Vagrant box?';
    }
  } else {
    $response['status'] = false;
    $response['message'] = 'Something went wrong with upload.';
  }
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user']))) {
  if (strcmp($_SERVER['REQUEST_METHOD'], 'POST') == 0 && (isset($_POST['boxname'])) && (isset($_POST['boxdescription'])))
  {
    check_file_upload();
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
