<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$DSDPlus = "/home/sdr/Desktop/DSDPlus v2.268/";

function readWaveStream($fileName)
{
    global $DSDPlus;
    $fileName = "${DSDPlus}${fileName}";

    header("Content-Type: audio/x-wav", true);
    header('Accept-Ranges: bytes');
    header('Content-length: ' . filesize($fileName));
    print file_get_contents($fileName);
}


function ReadDSDWaveFile($instance, $strDate, $strTime)
{
    global $DSDPlus;

    $strSearch = "${DSDPlus}/VC-Record#${instance}/$strDate/{$strTime}*.wav";


    $returnedFiles = glob($strSearch);

    if (count($returnedFiles) < 1) {

        $strTime = ($strTime + 1);
        if ($strTime < 100000) {
            $strTime = '0' . $strTime;
        }

        // echo $strTime;
        // exit();
        $strSearch = "${DSDPlus}/VC-Record#${instance}/$strDate/{$strTime}*.wav";
        $returnedFiles = glob($strSearch);
    }

    if (count($returnedFiles) < 1) {

        $strTime = ($strTime - 2);
        if ($strTime < 100000) {
            $strTime = '0' . $strTime;
        }

        // echo $strTime;
        // exit();
        $strSearch = "${DSDPlus}/VC-Record#${instance}/$strDate/{$strTime}*.wav";
        $returnedFiles = glob($strSearch);
    }


    if (count($returnedFiles) < 1) {
        return;
    }

    // print_r($returnedFiles);
    // exit();

    $fileName = $returnedFiles[0];

    header("Content-Type: audio/x-wav", true);
    header('Accept-Ranges: bytes');
    header('Content-length: ' . filesize($fileName));
    print file_get_contents($fileName);
}

function ReadWaveFile($file)
{
    $file = '/home/sdr/Desktop/Recordings/' . $file;

    header("Content-Type: audio/x-wav", true);
    header('Accept-Ranges: bytes');
    header('Content-length: ' . filesize($file));
    print file_get_contents($file);
}


// 
// 
// 
if (isset($_GET['cmd'])) {

    $cmd = $_GET['cmd'];

    switch ($cmd) {


        case "ReadDSDWaveFile":
            $instance = $_GET['instance'];
            $strDate = $_GET['date'];
            $strTime = $_GET['time'];
            ReadDSDWaveFile($instance, $strDate, $strTime);
            return;


        case "ReadWaveFile":
            $strFile = $_GET['file'];
            ReadWaveFile($strFile);
            return;

        default:
            echo "Command Not Found";
            break;
    }
}
