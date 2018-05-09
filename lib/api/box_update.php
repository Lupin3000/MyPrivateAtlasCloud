<?php
session_start();

require_once 'code/class_readconfig.php';

$configArrayObject = new LoadConfig('../config/application.ini');
$iniArray = $configArrayObject->getConfigArray();
$domain = $iniArray['server']['URL'];
$versions = $iniArray['repository']['versions'];
$htmlpath = $iniArray['repository']['html_path'];
$boxdir = $iniArray['repository']['box_dir'];
$metadir = $iniArray['repository']['json_dir'];
$time = time();
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
 * update json
 *
 * @param string $boxpath
 * @param string $jsonpath
 */
function updateJson($boxpath, $jsonpath) {
	global $response;
	global $versions;
	global $boxdir;
	global $htmlpath;
	global $domain;
	global $time;

	$checksum = sha1_file($boxpath);
	$boxfile = basename($boxpath);
	$boxurl = $domain . str_replace($htmlpath, '', $boxdir) . $boxfile;
	$filedata = file_get_contents($jsonpath);
	$jsondata = json_decode($filedata, true);
	$jsonurl = $domain . str_replace($htmlpath, '', $jsonpath);

	$jsondata['description'] = transformInput($_POST['boxdescription']);
	$newboxobj = array(
		'version' => (string) $time,
		'providers' => array(
			array(
				'name' => transformInput($_POST['boxprovider']),
				'url' => $boxurl,
				'checksum_type' => 'sha1',
				'checksum' => $checksum
			)
		)
	);
	array_push($jsondata['versions'], $newboxobj);

	$response['deleted'] = array();
	if ((int) count($jsondata['versions']) > (int) $versions) {
		$boxminurl = min($jsondata['versions'])['providers'][0]['url'];
		$boxminversion = min($jsondata['versions'])['version'];
		$boxminpath = $boxdir . basename(parse_url($boxminurl, PHP_URL_PATH));
		$arraykey = array_search($boxminversion, array_column($jsondata['versions'], 'version'));
		if (is_file($boxminpath)) {
			unlink($boxminpath);
			array_splice($jsondata['versions'], $arraykey, 1);
			array_push($response['deleted'], array('version' => $boxminversion,
																							'box' => $boxminurl));
		}
	}

	// write to file and close
	$jsondata = json_encode($jsondata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	file_put_contents($jsonpath, $jsondata);

	$response['status'] = true;
	$response['message'] = 'Your box is successful updated';
	$response['name'] = str_replace('_', '/', transformInput($_POST['boxname']));
	$response['provider'] = transformInput($_POST['boxprovider']);
	$response['description'] = transformInput($_POST['boxdescription']);
	$response['box'] = $boxurl;
	$response['json'] = $jsonurl;
}

/**
 * move uploaded file
 *
 * @param string $filename
 * @param string $jsonpath
 */
function moveFile($filename, $jsonpath) {
	global $response;
	global $boxdir;
	global $time;

	$boxpath = $boxdir . str_replace('.box', '_' . $time . '.box', $filename);

	if (move_uploaded_file($_FILES['boxfile']['tmp_name'], $boxpath)) {
		updateJson($boxpath, $jsonpath);
	} else {
		$response['status'] = false;
		$response['message'] = 'Could not move upload to target dir';
	}
}

/**
 * check file upload
 *
 */
function checkFileUpload() {
	global $response;
	global $metadir;

	if ((!empty($_FILES["boxfile"])) && ($_FILES['boxfile']['error'] === 0)) {
		$filename = strtolower(basename($_FILES['boxfile']['name']));
		$ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
		$mime = mime_content_type($_FILES['boxfile']['tmp_name']);
		$jsonfile = str_replace('/', '_', transformInput($_POST['boxname'])) . '.json';
		$jsonpath = $metadir . $jsonfile;

		if (($ext === "box") && ($mime === "application/x-gzip")) {
			if (file_exists($jsonpath)) {
				moveFile($filename, $jsonpath);
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
	if (strcmp($_SERVER['REQUEST_METHOD'], 'POST') === 0 && (isset($_POST['boxname'])) && (isset($_POST['boxdescription']))) {
		checkFileUpload();
	} else {
		$response['status'] = false;
		$response['message'] = 'Bad request';
	}
} else {
	$response['status'] = false;
	$response['message'] = 'Access to content prohibited';
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 3600');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response);
