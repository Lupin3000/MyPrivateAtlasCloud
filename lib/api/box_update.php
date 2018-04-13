<?php
session_start();

$ini_array = parse_ini_file('../config/application.ini', true);
$response = array();

function transform_input($value='')
{
  $value = trim($value);
  $value = htmlspecialchars($value);
  return $value;
}

function check_file_upload()
{
  global $response;

  $json_file = str_replace('/', '_', transform_input($_POST['boxname'])) . '.json';

  $response['status'] = false;
  $response['message'] = 'Debug/Develop: file upload check' . $json_file;
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
