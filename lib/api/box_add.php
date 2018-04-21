<?php
session_start();

$iniArray = parse_ini_file('../config/application.ini', true);
$domain = $iniArray['server']['URL'];
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
 * create json
 *
 * @param string $boxpath
 * @param string $jsonpath
 */
function createJson($boxpath, $jsonpath) {
	global $response;
	global $htmlpath;
	global $boxdir;
	global $domain;
	global $time;

	$checksum = sha1_file($boxpath);
	$boxfile = basename($boxpath);
	$boxurl = $domain . str_replace($htmlpath, '', $boxdir) . $boxfile;
	$jsonurl = $domain . str_replace($htmlpath, '', $jsonpath);

	$content = array(
		'name' => transformInput($_POST['boxname']),
		'description' => transformInput($_POST['boxdescription']),
		'versions' => array(
			array(
				'version' => (string) $time,
				'providers' => array(
					array(
						'name' => transformInput($_POST['boxprovider']),
						'url' => $boxurl,
						'checksum_type' => 'sha1',
						'checksum' => $checksum
					)
				)
			)
		)
	);

	$jsondata = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	file_put_contents($jsonpath, $jsondata);

	$response['status'] = true;
	$response['message'] = 'Your box is successful uploaded';
	$response['name'] = transformInput($_POST['boxname']);
	$response['provider'] = transformInput($_POST['boxprovider']);
	$response['description'] = transformInput($_POST['boxdescription']);
	$response['box'] = $boxurl;
	$response['json'] = $jsonurl;
}

/**
 * create json
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
		createJson($boxpath, $jsonpath);
	} else {
		$response['status'] = false;
		$response['message'] = 'Could not move upload to target dir';
	}
}

/**
 * create json
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
				$response['status'] = false;
				$response['message'] = 'Vagrant box already exists!';
			} else {
				moveFile($filename, $jsonpath);
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
	if (strcmp($_SERVER['REQUEST_METHOD'], 'POST') === 0 && (isset($_POST['boxname'])) && (isset($_POST['boxprovider']))) {
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
