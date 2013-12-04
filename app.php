<?php

include "lib/php-facedetection/FaceDetector.php";

// Figure out file name
if ( isset( $argv ) && is_array( $argv ) ) {
	$url = $argv[1];
} else {
	$url = $_SERVER['QUERY_STRING'];
}

$url_parts = parse_url( $url );
if ( empty( $url_parts['host'] ) ) {
	http_response_code( 400 );
	die( 'Ussage: /?http://www.example.com/path/to/image.jpeg' );
}

// Assume HTTP
if ( empty( $url_parts['scheme'] ) ) {
	$url = "http://{$url}";
}

// Get image
$image_string = file_get_contents( $url );

// Check image
$image = null;
$image_info = getimagesizefromstring( $image_string );
if ( is_array( $image_info ) && $image_info[0] > 0 && $image_info[1] > 0 ) {
	$image = imagecreatefromstring( $image_string );
}

// Delete our temp img
unset( $image_string );

// Check for errors
if ( empty( $image ) ) {
	http_response_code( 400 );
	die( 'Could not process image.' );
}

// Beanie me!
$detector = new svay\FaceDetector('lib/php-facedetection/detection.dat');
$detector->faceDetect( $image );
$face_stats = $detector->getFace();

if ( is_array( $face_stats ) && $face_stats['w'] > 5 ) {
	// We've got a face
	imagecopyresized(
		$image,
		imagecreatefrompng( 'bluebeanie.png' ),
		$face_stats['x'],
		$face_stats['y'] - 345 / 554 * $face_stats['w'] / 2,
		0,
		0,
		$face_stats['w'],
		345 / 554 * $face_stats['w'],
		554,
		345
	);
}

// Output
header( "Content-Type: {$image_info['mime']}" );
switch( $image_info['mime'] ) {
	case 'image/gif':
		imagegif( $image );
		break;
	case 'image/jpeg':
		imagejpeg( $image );
		break;
	case 'image/png':
		imagepng( $image );
		break;
}