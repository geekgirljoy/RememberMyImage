<?php




function Scale($dataset, $min_scaled_value, $max_scaled_value){

    $min_value = min($dataset);
    $max_value = max($dataset);

    foreach($dataset as &$n){
        $n = ($max_scaled_value - $min_scaled_value) * ($n - $min_value) / ($max_value - $min_value) + $min_scaled_value;
    }
    
    return $dataset;
}



// Load ANN
$train_file = (dirname(__FILE__) . "/RememberMyImage.net");
if (!is_file($train_file))
    die("The file RememberMyImage.net has not been created! Please run Train.php to generate it" . PHP_EOL);
$ann = fann_create_from_file($train_file);
if ($ann) {

    $image_x = 10;
    $image_y = 10;

    $image = imagecreatetruecolor($image_x, $image_y);
     
		// for the height of the image
	for($y = 0; $y < $image_y; $y++){
		// for the width of the image
		for($x = 0; $x < $image_x; $x++){

			$result = fann_run($ann, fann_scale_input($ann, [$x,$y]));
			//$result = fann_run($ann, [$x,$y]);
			$result = Scale($result, 0, 255);
			
			imagesetpixel($image, $x,$y, imagecolorallocate($image, $result[0], $result[1], $result[2]));
			
		}
	}
		
	// destroy image resources
    imagepng($image, "test.png", 0);
    imagedestroy($image);

    // Destroy ANN
    fann_destroy($ann);
} else {
    die("Invalid file format" . PHP_EOL);
}
