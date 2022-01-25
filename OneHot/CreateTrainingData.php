<?php
include 'functions.php';

$dataset = array();
$pixel_number = 0;

// For all the (case insensitive) image files (jpg, jpeg, png) in the "Images" subfolder (relitive to this file's location) as $image_path
foreach (glob( __DIR__ . DIRECTORY_SEPARATOR ."Images"  . DIRECTORY_SEPARATOR
                                                          . '*.{[jJ][pP][gG],
                                                                [jJ][pP][eE][gG],
                                                                [pP][nN][gG]
                                                                }', GLOB_BRACE) as $image_path) {
    
    // Get the file type
    $image_file_type = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
    
    // If jpg or jpeg
    if( $image_file_type == 'jpg' || $image_file_type == 'jpeg'){
        $image = imagecreatefromjpeg($image_path);
    }
    // If png
    elseif($image_file_type == 'png'){
        $image = imagecreatefrompng($image_path);
    }
    /*
    // If other image type
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
    
    $polar_center_x = ($image_width+1) / 2;
    $polar_center_y = ($image_height+1) / 2;
    
    // for the height of the image
    for($y = 0; $y < $image_height; $y++){        
        // for the width of the image
        for($x = 0; $x < $image_width; $x++){
            
            // obtain the pixel color information at position Col X, Row Y 
            $pixel_color = imagecolorat($image, $x, $y);
            
            // get the color values as ints
            $red = ($pixel_color >> 16) & 0xFF;
            $green = ($pixel_color >> 8) & 0xFF;
            $blue = $pixel_color & 0xFF;
            
			// Create 1 Hot Vector Input foir this pixel
			$one_hot_input_vector = "";
			if($pixel_number > 0){ // if this isn't the first pixel
				$one_hot_input_vector = str_repeat('-1 ', $pixel_number); // Prepend -1's
			}
			$one_hot_input_vector .= '1 '; // place 1 at the correct location in the vector string
			if($pixel_number < $image_width * $image_height){// if this isn't the last pixel
				$one_hot_input_vector .= str_repeat('-1 ', ($image_width * $image_height) - 1 - $pixel_number); // Append -1's
			}

			$pixel_number++; // next pixel/vector location

            // store the image information in our dataset array
            $dataset[] = array($one_hot_input_vector, $red, $green, $blue);
        }
     }    
}
//var_dump($pixel_number);
//die();

// if our dataset array is not empty
if(!empty($dataset)){
    
    // create a file to store the unscaled tranining data
    $f = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'unscaled.data', 'w');
    
    // count the number of training examples
    $number_of_training_examples = count($dataset);
    
    // write the FANN training file header 
    $number_of_inputs = $pixel_number;
    $number_of_outputs = 3;
    fwrite($f, "$number_of_training_examples $number_of_inputs $number_of_outputs" . PHP_EOL);
    
    // for all the training images in our dataset
    foreach($dataset as $pixel_data){
        
            /////////////////////////////////////////////////////////////////
            // Write the training data to file here    //
            /////////////////////////////////////////////////////////////////
            
            // input: One Hot Vector string of length = image width * image height
            fwrite($f,  trim($pixel_data[0]) . PHP_EOL);
            
            // output: R_int G_int B_int
            fwrite($f,  $pixel_data[1] . ' ' . $pixel_data[2] . ' ' . $pixel_data[3] . PHP_EOL);
            
            /////////////////////////////////////////////////////////////////
            // / Write the training data to file here //
            /////////////////////////////////////////////////////////////////
    }
    
    fclose($f);
}

// Read raw (un-scaled) training data from file
$train_data = fann_read_train_from_file('unscaled.data');

// Scale to a range of 0 to 1
//fann_scale_train_data($train_data, 0, 1);
//fann_scale_input_train_data($train_data, 0, 1);
fann_scale_output_train_data($train_data, 0, 1);

// Save the new scaled traning data as a file
fann_save_train($train_data, 'scaled.data');