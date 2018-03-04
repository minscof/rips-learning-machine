<?php

namespace MachineLearning\Util;

use DirectoryIterator;
use RegexIterator;

include 'Input.php';
include 'Output.php';



function getFilteredFileList($dir, $regex)
{
    $retval = [];
    
    // add trailing slash if missing
    if(substr($dir, -1) != "/") $dir .= "/";
    
    // open directory for reading
    $d = new DirectoryIterator($dir) or die("getFilteredFileList: Failed opening directory $dir for reading");
    $iterator = new RegexIterator($d, $regex, RegexIterator::MATCH);
    foreach($iterator as $fileinfo) {
        // skip hidden files
        if($fileinfo->isDot()) continue;
        $retval[] = [
            'name' => "{$dir}{$fileinfo}",
            'type' => ($fileinfo->getType() == "dir") ? "dir" : mime_content_type($fileinfo->getRealPath()),
            'size' => $fileinfo->getSize(),
            'lastmod' => $fileinfo->getMTime()
            ];
    }
    
    return $retval;
}



/**
 * Class Training
 * @package MachineLearning\Util
 */
class Training
{
    
    private function process_new_sample($mac,$timestamp,$rssi,$antenna,&$datas) {
        
        define(DELTATIMEREF, 2);
        static $datas = [];
        
        /*
         * $datas : array key = timestamp, value = $data
         *
         *
         *  $data = array key = antenna value = [rssi,rssi1,rssi2]
         *
         *
         *
         */
        foreach ($datas as $timestampKey => &$data ) {
            $deltat = $timestamp - $timestampKey;
            if ($deltat > 3*DELTATIMEREF) {
                //time expired, complete empty data with -200 value
                foreach ($data as $antennaKey => &$rssiTab) {
                    while (count($rssiTab) != 3) {
                        $rssiTab[] = ['rssi' => -200];
                    };
                }
                $samples[] = ['input' => $data, 'output' => $location];
                unset($data);
            } elseif ($deltat > 2*DELTATIMEREF) {
                if (empty($data[$antenna][0])) $data[$antenna][0] = ["time" => "t0", 'rssi' => -200];
                if (empty($data[$antenna][1])) $data[$antenna][1] = ["time" => "t1", 'rssi' => -200];
                if (empty($data[$antenna][2])) $data[$antenna][2] = ["time" => "t2", 'rssi' => $rssi];
            } elseif ($deltat > DELTATIMEREF) {
                if (empty($data[$antenna][0])) $data[$antenna][0] = ["time" => "t0", 'rssi' => -200];
                if (empty($data[$antenna][1])) $data[$antenna][1] = ["time" => "t1", 'rssi' => $rssi];
            } else {
                if (empty($data[$antenna][0])) $data[$antenna][0] = ["time" => "t0", 'rssi' => $rssi];
            }
        }
        
        //initialize new record
        $datas[$timestamp][$antenna][0] = ["time" => "t0", "rssi" => $rssi];
        
    }
    
    /**
     * Read captures RSSI file.
     *   one file by location captureRSSIlocation.txt
     *   mac,timestamp,rssi,antenna
     *   f0:eb:1f:b1:ea:b7,1520013813,-87,Jeedom
     */
    private function read_samples()
    {
        
        
        
        $dirlist = getFilteredFileList("Data/", "/^captureRSSI*\.txt$/");
        
        foreach ($dirlist as $filename)  {
            preg_match('/^captureRSSI(.*)\.txt$//', $filename, $matches);
            $location = $matches[0];
            if  (!in_array($location, ANTENNAS)) {
                echo "no capture file found";
                break;
            }
            
            $file = new \SplFileObject($filename, 'r');
            $samples = [];
            $macSample = '';
            while (!$file->eof()) {
                list($mac,$timestamp,$rssi,$antenna) = explode(',',$file->fgets());
                if ($macSample == '') {
                    $macSample = $mac;
                } else {
                    if ($macSample != $mac) {
                        echo "skip mac =".$mac;
                        continue;
                    }
                }
                process_new_sample($mac,$timestamp,$rssi,$antenna,$samples);
                
            };
            $file = null;
            
        }
        
        
        
        
        
    }

    /**
     * Create training file.
     */
    public function create()
    {
        $input = new Input();
        $lines = [];
        $numSamples = 0;
        foreach (self::$samples as $sample) {
            $numSamples++;
            $lines[] = $input->getInputFromSample($sample['input']);
            $lines[] = [$sample['output']];
        }

        $file = new \SplFileObject('Data/training', 'w');
        $file->fwrite(implode(' ', [$numSamples, Input::NUM_INPUT, Output::NUM_OUTPUT]));
        $file->fwrite(PHP_EOL);

        foreach ($lines as $line) {
            $file->fwrite(implode(' ', $line));
            $file->fwrite(PHP_EOL);
        }

        $file = null;
    }
}
