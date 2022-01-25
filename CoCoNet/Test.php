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

    // for the height of the image
    for($y = 0; $y < $image_y; $y++){
        // for the width of the image
        for($x = 0; $x < $image_x; $x++){
            
            $Cartesian = XYCoordsToCartesian($x,$y, $image_x, $image_y); // find this XY position in relation to a center 0,0 cartesian point
            $Polar = CartesianToPolar($Cartesian); // Compute the polar coord from the Cartesian coord 

           $Inputs = array($x, $y, $image_x - $x, $image_y - $y, $Polar[0], $Polar[1]);
  
            $result = fann_run($ann, fann_scale_input($ann,  $Inputs));
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
