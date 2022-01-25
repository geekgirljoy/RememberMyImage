<?php

$desired_error = 0.01;
$max_epochs = 1000000;
$current_epoch = 0;
$epochs_between_saves = 10; // Minimum number of epochs between saves
$epochs_since_last_save = 0;
$filename = dirname(__FILE__) . "/scaled.data";

// Initialize psudo mse (mean squared error) to a number greater than the desired_error
// this is what the network is trying to minimize.
$psudo_mse_result = $desired_error * 10000; // 1
$best_mse = $psudo_mse_result; // keep the last best seen MSE network score here

// Initialize ANN
$layers = [6, 200, 3];
$ann = fann_create_standard_array(count($layers), $layers);

if ($ann) {
    echo 'Training ANN... ' . PHP_EOL;
 
    // Configure the ANN
    fann_set_training_algorithm ($ann , FANN_TRAIN_INCREMENTAL);
    fann_set_learning_rate($ann, 0.0001); 
    fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC); // tanh
    fann_set_activation_function_output($ann, FANN_SIGMOID);
 
    // Read training data
    $train_data = fann_read_train_from_file($filename);
    
	// Scale the data range to -1 to 1
	fann_set_input_scaling_params($ann , $train_data, 0, 1);
	fann_set_output_scaling_params($ann , $train_data, 0, 1);
 
    // Check if psudo_mse_result is greater than our desired_error
    // if so keep training so long as we are also under max_epochs
    while(($psudo_mse_result > $desired_error) && ($current_epoch <= $max_epochs)){
        $current_epoch++;
        $epochs_since_last_save++; 
 
        // See: http://php.net/manual/en/function.fann-train-epoch.php
        // Train one epoch with the training data stored in data.
        //
        // One epoch is where all of the training data is considered
        // exactly once.
        //
        // This function returns the MSE error as it is calculated
        // either before or during the actual training. This is not the
        // actual MSE after the training epoch, but since calculating this
        // will require to go through the entire training set once more.
        // It is more than adequate to use this value during training.
        $psudo_mse_result = fann_train_epoch ($ann , $train_data );
        echo 'Epoch ' . $current_epoch . ' : ' . $psudo_mse_result . PHP_EOL; // report
     
     
        // If we haven't saved the ANN in a while...
        // and the current network is better then the previous best network
        // as defined by the current MSE being less than the last best MSE
        // Save it!
        if(($epochs_since_last_save >= $epochs_between_saves) && ($psudo_mse_result < $best_mse)){
         
            $best_mse = $psudo_mse_result; // we have a new best_mse
         
            // Save a Snapshot of the ANN
            fann_save($ann, dirname(__FILE__) . "/RememberMyImage.net");
            echo 'Saved ANN.' . PHP_EOL; // report the save
            $epochs_since_last_save = 0; // reset the count
        }
 
    } // While we're training

    echo 'Training Complete! Saving Final Network.'    . PHP_EOL;
 
    // Save the final network
    fann_save($ann, dirname(__FILE__) . "/RememberMyImage.net"); 
    fann_destroy($ann); // free memory
}
echo 'All Done!' . PHP_EOL;
?>