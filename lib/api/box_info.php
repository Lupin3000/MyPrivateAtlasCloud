<?php
session_start();

require_once 'code/class_readconfig.php';

$configArrayObject = new LoadConfig('../config/application.ini');
$iniArray = $configArrayObject->getConfigArray();
$domain = $iniArray['server']['URL'];
$htmlpath = $iniArray['repository']['html_path'];
$metadir = $iniArray['repository']['json_dir'];
$boxdir = $iniArray['repository']['box_dir'];
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
 * format filesize by given path
 *
 * @param string $path
 * @return string
 */
function filesizeFormatted($path) {
	$size = filesize($path);
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$power = $size > 0 ? floor(log($size, 1024)) : 0;
	return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

/**
 * create json
 *
 * @param string $domain
 */
function jsonBoxInfo($domain) {
	global $response;
	global $htmlpath;
	global $metadir;
	global $boxdir;
	global $domain;

	$jsonfile = str_replace('/', '_', transformInput($_GET['name'])) . '.json';
	$jsonpath = $metadir . $jsonfile;
	$filedata = file_get_contents($jsonpath);
	$jsondata = json_decode($filedata, true);
	$jsonurl = $domain . str_replace($htmlpath, '', $metadir) . $jsonfile;
	$allversions = array();
	$response['versions'] = array();

	foreach ($jsondata['versions'] as $item) {
		$allversions[] = $item['version'];
		$box = basename(parse_url($item['providers'][0]['url'], PHP_URL_PATH));
		$boxpath = $boxdir . $box;

		array_push($response['versions'], array('size' => filesizeFormatted($boxpath),
																						'created' => date("F d Y H:i:s", filemtime($boxpath)),
																						'version' => $item['version'],
																						'provider' => $item['providers'][0]['name'],
																						'box' => $item['providers'][0]['url'],
																						'checksum_type' => $item['providers'][0]['checksum_type'],
																						'checksum' => $item['providers'][0]['checksum']));
	}

	$latestv = max($allversions);

	$response['name'] = $jsondata['name'];
	$response['description'] = $jsondata['description'];
	$response['json'] = $jsonurl;
	$response['latestversion'] = $latestv;

	$response['status'] = true;
	$response['message'] = 'Box information';
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user']))) {
	if ((strcmp($_SERVER['REQUEST_METHOD'], 'GET') === 0) && (isset($_GET['name']))) {
		jsonBoxInfo($domain);
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
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response);
