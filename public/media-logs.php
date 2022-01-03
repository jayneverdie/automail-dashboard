<?php

error_reporting(0); // Set E_ALL for debuging

// load composer autoload before load elFinder autoload If you need composer
//require './vendor/autoload.php';

// elFinder autoload
require '../media/php/autoload.php';
// ===============================================

// Enable FTP connector netmount
elFinder::$netDrivers['ftp'] = 'FTP';
// ===============================================

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string    $attr    attribute name (read|write|locked|hidden)
 * @param  string    $path    absolute file path
 * @param  string    $data    value of volume option `accessControlData`
 * @param  object    $volume  elFinder volume driver object
 * @param  bool|null $isDir   path is directory (true: directory, false: file, null: unknown)
 * @param  string    $relpath file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
			 && strlen($relpath) !== 1           // but with out volume root
		? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
		:  null;                                 // else elFinder decide it itself
}

// if ($_GET['path'] === 'all') {
// 	$_media_path = '';
// 	$_attr = [
// 		'pattern' => '/.*/',
// 		'read' => true,
// 		'write' => true,
// 		'locked'  => false,
// 		'hidden' => false
// 	];
// } else {
	$_media_path = $_GET['path'] . '/';
	$_attr = [
		[
			'pattern' => '/'. $_GET['access'] . '/',
			'read' => true,
			'write' => false,
			'locked'  => true,
			'hidden' => false
		]
	];
// }

// var_dump($_attr);
// exit;

$opts = array(
	// 'debug' => true,
	'roots' => array(
		// Items volume
		array(
			'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
			'path'          => 'temp/' . $_media_path,               // path to files (REQUIRED)
			'URL'           => '/temp/' . $_media_path, // URL to files (REQUIRED)
			'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
			'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
			'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
			'uploadAllow'   => array('application/pdf', 'image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel','text/html'), // Mimetype `image` and `text/plain` allowed to upload
			'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
			'accessControl' => 'access',
			'attributes' => $_attr
		)
	)
);

$connector = new elFinderConnector(new elFinder($opts));
$connector->run();
