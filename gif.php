<?php

	//Leave all this stuff as it is
	date_default_timezone_set('America/Toronto');
	include 'GIFEncoder.class.php';
	include 'php52-fix.php';
	$time = $_GET['time'];
	$color = $_GET['color'];
	list($red, $green, $blue) = explode(",", $color);
	$bg = $_GET['bg'];
	list($bgred, $bggreen, $bgblue) = explode(",", $bg);
	$width = $_GET['width'];
	$height = $_GET['height'];
	$future_date = new DateTime(date('r',strtotime($time)));
	$time_now = time();
	$now = new DateTime(date('r', $time_now));
	$frames = array();	
	$delays = array();


	// Your image link
	$image = imagecreatetruecolor($width, $height);
	$background = imagecolorallocate($image, $bgred, $bggreen, $bgblue);
	imagefill($image, 0, 0, $background);

	$delay = 100;// milliseconds

	$font = array(
		'size' => 30, // Font size, in pts usually.
		'angle' => 0, // Angle of the text
		'file' => __DIR__ . DIRECTORY_SEPARATOR . fonts . DIRECTORY_SEPARATOR . 'JLREmeric-SemiBold.ttf', // Font path
		'color' => imagecolorallocate($image, $red, $green, $blue), // RGB Colour of the text
	);
	for($i = 0; $i <= 60; $i++){
		
		$interval = date_diff($future_date, $now);
		
		if($future_date < $now){
			// Create the first source image and add the text.
			$image = imagecreatetruecolor($width, $height);
			$background = imagecolorallocate($image, $bgred, $bggreen, $bgblue);
			imagefill($image, 0, 0, $background);
			//;
			$text = $interval->format('00   00   00   00');
			// Create our bounding box for the text
			$box = imagettfbbox($font['size'], $font['angle'], $font['file'], $text);
			// Measure the text
			$textwidth = abs($box[4] - $box[0]);
			// Center the text
			$x = ceil(($width - $textwidth) / 2); // lower left X coordinate for text
			
			imagettftext($image, $font['size'], $font['angle'], $x, $font['size'], $font['color'], $font['file'], $text);
			
			ob_start();
			imagecolortransparent($image, $background);
			imagegif($image);
			$frames[]=ob_get_contents();
			$delays[]=$delay;
			$loops = 1;
			ob_end_clean();
			break;
		} else {
			// Create the first source image and add the text.
			$image = imagecreatetruecolor($width, $height);
			$background = imagecolorallocate($image, $bgred, $bggreen, $bgblue);
			imagefill($image, 0, 0, $background);
			//;
			$text = $interval->format('%a   %H   %I   %S');
			// %a is weird in that it doesn’t give you a two digit number
			// check if it starts with a single digit 0-9
			// and prepend a 0 if it does
			if(preg_match('/^[0-9]\:/', $text)){
				$text = '0'.$text;
			}
			// Create our bounding box for the text
			$box = imagettfbbox($font['size'], $font['angle'], $font['file'], $text);
			// Measure the text
			$textwidth = abs($box[4] - $box[0]);
			// Center the text
			$x = ceil(($width - $textwidth) / 2); // lower left X coordinate for text
			
			imagettftext($image, $font['size'], $font['angle'], $x, $font['size'], $font['color'], $font['file'], $text);

			ob_start();
			imagecolortransparent($image, $background);
			imagegif($image);
			$frames[]=ob_get_contents();
			$delays[]=$delay;
			$loops = 0;
			ob_end_clean();
		}

		$now->modify('+1 second');
	}

	//expire this image instantly
	header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
	$gif = new AnimatedGif($frames,$delays,$loops);
	$gif->display();
