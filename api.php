<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/src/config.php";

$config = new AppConfig();

function ReadDSDWaveFile($instance, $strDate, $strTime)
{
    global $config;
    $DSDPlus = $config->DSDPlusFolder;

    $strSearch = "${DSDPlus}/VC-Record#${instance}/$strDate/{$strTime}*.wav";

    // Initial Search
    $returnedFiles = glob($strSearch);

    // If no files found, adjust the time by +1 and search again
    if (count($returnedFiles) < 1) {

        $strTime = ($strTime + 1);
        if ($strTime < 100000) {
            $strTime = '0' . $strTime;
        }
        $strSearch = "${DSDPlus}/VC-Record#${instance}/$strDate/{$strTime}*.wav";
        $returnedFiles = glob($strSearch);
    }

    // If no files found, adjust the time by -2 and search again
    // this will sometimes find a file that is logged in the events file with a second difference to the actual filename
    if (count($returnedFiles) < 1) {

        $strTime = ($strTime - 2);
        if ($strTime < 100000) {
            $strTime = '0' . $strTime;
        }

        $strSearch = "${DSDPlus}/VC-Record#${instance}/$strDate/{$strTime}*.wav";
        $returnedFiles = glob($strSearch);
    }


    if (count($returnedFiles) > 0) {

        $fileName = $returnedFiles[0];

        header("Content-Type: audio/x-wav", true);
        header('Accept-Ranges: bytes');
        header('Content-length: ' . filesize($fileName));
        print file_get_contents($fileName);
    }
}

function ReadWaveFile($file)
{
    global $config;

    $file = $config->FileEventsPath . $file;

    header("Content-Type: audio/x-wav", true);
    header('Accept-Ranges: bytes');
    header('Content-length: ' . filesize($file));
    print file_get_contents($file);
}


// 
// Logic Switch to determine GET paramaters and retreive file recordings
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
