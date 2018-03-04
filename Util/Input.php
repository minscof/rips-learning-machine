<?php

namespace MachineLearning\Util;

/**
 * Class Input
 * @package MachineLearning\Util
 */
class Input
{
    
    const ANTENNA1 = "raspberrypi";
    const ANTENNA2 = "Jeedom";
    const ANTENNA3 = "cuisine";
    const ANTENNAS = [ANTENNA1,ANTENNA2,ANTENNA3];
    const NUM_INPUT = 3*count(ANTENNAS);
    

    /**
     * Generate input for a sample.
     *
     * @param string $sample
     *
     * @return array
     */
    public function getInputFromSample($sample)
    {
        $input = [];

        // 1 : rssi Antenna1 @ t0
        $input[] = $sample[ANTENNA1][0]['rssi'];

        // 2 : rssi Antenna1 @ t0-&t
        $input[] = $sample[ANTENNA1][1]['rssi'];
        
        // 3 : rssi Antenna1 @ t0-2&t
        $input[] = $sample[ANTENNA1][2]['rssi'];
        
        // 4 : rssi Antenna2 @ t0
        $input[] = $sample[ANTENNA2][0]['rssi'];
        
        // 5 : rssi Antenna2 @ t0-&t
        $input[] = $sample[ANTENNA2][1]['rssi'];
        
        // 6 : rssi Antenna2 @ t0-2&t
        $input[] = $sample[ANTENNA2][2]['rssi'];
        
        // 7 : rssi Antenna3 @ t0
        $input[] = $sample[ANTENNA3][0]['rssi'];
        
        // 8 : rssi Antenna3 @ t0-&t
        $input[] = $sample[ANTENNA3][1]['rssi'];
        
        // 9 : rssi Antenna3 @ t0-2&t
        $input[] = $sample[ANTENNA3][2]['rssi'];
        
        return $input;
    }
}
