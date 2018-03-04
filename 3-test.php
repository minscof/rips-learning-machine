<?php

namespace MachineLearning;

use MachineLearning\Util\Input;

include 'Util/Input.php';

$ann = fann_create_from_file('Data/training_result');
if (!$ann) {
    die("ANN could not be created");
}

$input = new Input();

$samples[] = [];

foreach ($samples as $sample) {
    $result = fann_run($ann, $input->getInputFromSample($sample));

    printf("Sample : %s\n", $sample);
    printf("Result : %f\n", $result);
    
    $maxs = array_keys($result, max($result));

    if ($result[$maxs[0]] > 0.9) {
        $location = LOCATIONS[$maxs[0]];
        echo ("location is $location  \n");
    }

    echo ("\n");
}

fann_destroy($ann);
