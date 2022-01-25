
<?php
function XYCoordsToCartesian($x, $y,$image_width, $image_height){
    $center_x = ($image_width) / 2;
    $center_y = ($image_height) / 2;
    
    $x2 = $image_width - $x;
    $y2 = $image_height - $y;
        
    // cartesian cords 
    $x = $x - $center_x;
    $y = $y2 - $center_y;
    return array($x,$y);// $x & $y are the XY index positions converted to cartesian
}

function CartesianToPolar($Cartesian){
    $x = $Cartesian[0];
    $y = $Cartesian[1];
    
    $r = hypot($x,$y); // sqrt(pow($x,2)+pow($y,2));
    $theta = 0;
    
    /*
    Q2 | Q1
    ----+-----
    Q3 | Q4
    
    [-,+] | [+,+]
     -----+-----
    [-,+]  | [+,-]
    
    */
    if ($x > 0 && $y > 0){ // Q1 Cartesian
           $theta =  atan($y/$x);
     }
    elseif ($x < 0 && $y > 0){ // Q2 Cartesian
           $theta =  PI() - atan($y/$x);
    }
    elseif ($x < 0 && $y < 0){ // Q3 Cartesian
        $theta =  PI() + atan($y/$x);
    }
    elseif ($x > 0 && $y < 0){ // Q4 Cartesian
        $theta =  (PI() * 2) - atan($y/$x);
    }
    
    return array($r, $theta);
}


function Scale($dataset, $min_scaled_value, $max_scaled_value){

    $min_value = min($dataset);
    $max_value = max($dataset);

    foreach($dataset as &$n){
        $n = ($max_scaled_value - $min_scaled_value) * ($n - $min_value) / ($max_value - $min_value) + $min_scaled_value;
    }
    
    return $dataset;
}
