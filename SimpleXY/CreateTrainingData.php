<?php

$dataset = array();
$img_number = 0;
$iw = 0; // max image x
$ih = 0; // max image y

// For all the (case insensitive) image files (jpg, jpeg, png) in the "Images" subfolder (relitive to this file's location) as $image_path
foreach (glob( __DIR__ . DIRECTORY_SEPARATOR ."Images"  . DIRECTORY_SEPARATOR
                                                          . '*.{[jJ][pP][gG],
                                                                [jJ][pP][eE][gG],
																[pP][nN][gG]
																}', GLOB_BRACE) as $image_path) {
	
	// Get the file type
	$image_file_type = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
	
	// If jpg or jpeg
	if( $image_file_type == 'jpg' || strtolower($image_file_type) == 'jpeg'){
		$image = imagecreatefromjpeg($image_path);
	}
	// If png 
	elseif($image_file_type == 'png'){
		$image = imagecreatefrompng($image_path);
	}
	/*
	// If other image type (case insensitive)
	elseif($image_file_type == 'extention'){
		//$image = imagecreatefromavif($image_path);
		//$image = imagecreatefrombmp($image_path);
		//$image = imagecreatefromgd2($image_path);
		//$image = imagecreatefromgd2part($image_path);
		//$image = imagecreatefromgd($image_path);
		//$image = imagecreatefromgif($image_path);
		//$image = imagecreatefromstring($image_path);
		//$image = imagecreatefromtga($image_path);
		//$image = imagecreatefromwbmp($image_path);
		//$image = imagecreatefromwebp($image_path);
		//$image = imagecreatefromxbm($image_path);
		//$image = imagecreatefromxpm($image_path);
		//$image = imagecreatetruecolor($image_path);
	}
	*/
	
	list($image_width, $image_height) = getimagesize($image_path);
    
	$p = 0;
    // for the height of the image
    for($y = 0; $y < $image_height; $y++){
		// for the width of the image
		for($x = 0; $x < $image_width; $x++){
			
			// obtain the pixel color information
			$pixel_color = imagecolorat($image, $x, $y);
			
			// get the color values as ints
			$r = ($pixel_color >> 16) & 0xFF;
			$g = ($pixel_color >> 8) & 0xFF;
			$b = $pixel_color & 0xFF;
			
			$inputs = "";
			if($p > 0){
			    $inputs = str_repeat("0 ", $p);
			}
			 $inputs .= '1'
			 if($p < $image_width * $image_height){
				   $inputs .= str_repeat(" 0", ($image_width * $image_height)-$p);
			 }
					
			// store the image information in our dataset array
			//$dataset[$img_number][] = array($inputs, $r, $g,$b);
			$dataset[$img_number][] = array($x,$y, $r, $g,$b);
			
			$p++;
		}
	 }	
}

// if our dataset array is not empty
if(!empty($dataset)){
	
	// create a file to store the unscaled tranining data
	$f = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'unscaled.data', 'w');
	
	// count the number of training examples
	$number_of_training_examples = 0;
	foreach($dataset as $d){
		//foreach($d as $e){
		//}
		$number_of_training_examples += count($d);
	}
	
	// write the FANN training file header 
	$number_of_inputs = 2;
	$number_of_outputs = 3;
	fwrite($f, "$number_of_training_examples $number_of_inputs $number_of_outputs" . PHP_EOL);
	
	// for all the training images in our dataset
	foreach($dataset as $image){
		// for all the pixel information
		foreach($image as $pixel_data){
			
			/////////////////////////////////////////////////////////////
			// Write the training data to file here   //
			/////////////////////////////////////////////////////////////
			
			// input: x_cord y_cord
		    fwrite($f,  $pixel_data[0] . ' ' . $pixel_data[1] . PHP_EOL);
			
			// output: R_int G_int B_int
			fwrite($f,  $pixel_data[2] . ' ' . $pixel_data[3] . ' ' . $pixel_data[4] . PHP_EOL);
			
			/////////////////////////////////////////////////////////////
			// / Write the training data to file here //
			/////////////////////////////////////////////////////////////
		}
	}
	
	fclose($f);
}

// Read raw (un-scaled) training data from file
$train_data = fann_read_train_from_file('unscaled.data');

// Scale to a range of 0 to 1
fann_scale_train_data($train_data, 0, 1);

// Save the new scaled traning data as a file
fann_save_train($train_data, 'scaled.data');
