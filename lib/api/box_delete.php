<?php
session_start();

require_once 'code/class_readconfig.php';

$configArrayObject = new LoadConfig('../config/application.ini');
$iniArray = $configArrayObject->getConfigArray();
$domain = $iniArray['server']['URL'];
$htmlpath = $iniArray['repository']['html_path'];
$metadir = $iniAarray['repository']['json_dir'];
$boxdir = $iniAarray['repository']['box_dir'];
$response = array();

/**
 * transform input variables
 *
 * @param string $value
 * @return string
 */
function transformInput($value = '') {
	$value = trim($value);
	$value = htmlspecialchars($value);
	return $value;
}

/**
 * create json
 *
 */
function jsonBoxDelete() {
	global $response;
	global $domain;
	global $htmlpath;
	global $metadir;
	global $boxdir;

	$jsonfile = str_replace('/', '_', transformInput($_GET['name'])) . '.json';
	$jsonpath = $metadir . $jsonfile;
	$jsonurl = $domain . str_replace($htmlpath, '', $jsonpath);
	$filedata = file_get_contents($jsonpath);
	$jsondata = json_decode($filedata, true);
	$response['deleted'] = array();

	foreach ($jsondata['versions'] as $item) {
		$box = basename(parse_url($item['providers'][0]['url'], PHP_URL_PATH));
		$boxpath = $boxdir . $box;
		array_push($response['deleted'], array('box' => $item['providers'][0]['url'],
																						'version' => $item['version']));

		if (is_file($boxpath)) {
			unlink($boxpath);
		}
	}

	if (is_file($jsonpath)) {
		unlink($jsonpath);
	}

	$response['json'] = $jsonurl;
	$response['status'] = true;
	$response['message'] = 'All files successful deleted';
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user']))) {
	if ((strcmp($_SERVER['REQUEST_METHOD'], 'GET') === 0) && (isset($_GET['name']))) {
		jsonBoxDelete();
	} else {
		$response['status'] = false;
		$response['message'] = 'Bad request';
	}
} else {
	$response['status'] = false;
	$response['message'] = 'Access to content prohibited';
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Max-Age: 3600');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response);
