<?php
session_start();

$iniArray = parse_ini_file('../config/application.ini', true);
$domain = $iniArray['server']['URL'];
$htmlpath = $iniArray['repository']['html_path'];
$metadir = $iniArray['repository']['json_dir'];
$globpattern = $metadir . '*.json';
$response = array();

/**
 * truncate input
 *
 * @param string $string
 * @param int $length
 * @param string $append
 *
 * @return string
 */
function truncate($string, $length = 100, $append = "...") {
	$string = trim($string);
	if (strlen($string) > $length) {
		$string = wordwrap($string, $length);
		$string = explode("\n", $string, 2);
		$string = $string[0] . $append;
	}
	return $string;
}

/**
 * create json
 *
 *
 * @param string $domain
 * @param string $metadir
 * @param string $globpattern
 */
function jsonBoxList($domain, $metadir, $globpattern) {
	global $response;
	global $htmlpath;

	$response['status'] = true;
	$response['message'] = 'List of current boxes';
	$response['boxes'] = array();

	foreach (array_filter(glob($globpattern), 'is_file') as $entry) {
		$filedata = file_get_contents($entry);
		$jsondata = json_decode($filedata, true);
		$jsonurl = $domain . str_replace($htmlpath, '', $metadir) . basename($entry);

		$boxname = $jsondata['name'];
		$boxdescription = truncate($jsondata['description'], 25, '...');
		$boxprovider = $jsondata['versions'][0]['providers'][0]['name'];

		array_push($response['boxes'], array('json' => $jsonurl,
																					'name' => $boxname,
																					'description' => $boxdescription,
																					'provider' => $boxprovider));
	}
}

if ((isset($_SESSION['valid'])) && (isset($_SESSION['user']))) {
	if (strcmp($_SERVER['REQUEST_METHOD'], 'GET') === 0) {
		jsonBoxList($domain, $metadir, $globpattern);
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
