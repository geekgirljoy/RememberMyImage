<?php

include 'functions.php';

// Load ANN
$train_file = (dirname(__FILE__) . "/RememberMyImage.net");
if (!is_file($train_file))
    die("The file RememberMyImage.net has not been created! Please run Train.php to generate it" . PHP_EOL);
$ann = fann_create_from_file($train_file);
if ($ann) {

    $image_x = 10;
    $image_y = 10;

    $image = imagecreatetruecolor($image_x, $image_y);

    $pixel_number = 0;
	
    // for the height of the image
    for($y = 0; $y < $image_y; $y++){
        // for the width of the image
        for($x = 0; $x < $image_x; $x++){
            
			// Create 1 Hot Vector Input foir this pixel
			$one_hot_input_vector = "";
			if($pixel_number > 0){ // if this isn't the first pixel
				$one_hot_input_vector = str_repeat('-1 ', $pixel_number); // Prepend -1's
			}
			$one_hot_input_vector .= '1 '; // place 1 at the correct location in the vector string
			if($pixel_number < $image_x * $image_y){// if this isn't the last pixel
				$one_hot_input_vector .= str_repeat('-1 ', ($image_x * $image_y) - 1 - $pixel_number); // Append -1's
			}
			$pixel_number++; // next pixel/vector location
			
           $Inputs = explode(' ', trim($one_hot_input_vector));
  
            $result = fann_run($ann, $Inputs);
            $result = Scale($result, 0, 255);

            imagesetpixel($image, $x,$y, imagecolorallocate($image, round($result[0]), round($result[1]), round($result[2])));
        }
    }
        
    // destroy image resources
    imagepng($image, "test.png", 0);
    imagedestroy($image);

    // Destroy ANN
    fann_destroy($ann);
} 
else {
    die("Invalid file format" . PHP_EOL);
}
