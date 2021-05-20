<?php
require_once __DIR__ . "/src/config.php";

header("Access-Control-Allow-Origin: *");

$config = new AppConfig();

if (true | $config->isDebug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// If Keep Alive
if (isset($_GET['keepalive'])) {
    return;
    exit;
}


function ReadDSDWaveFile($instance, $date, $file)
{
    global $config;

    $instance++;
    $path = $config->DSDPlusFolder . "VC-Record#${instance}/${date}/${file}";

    header("Content-Type: audio/x-wav", true);
    header('Accept-Ranges: bytes');
    header('Content-length: ' . filesize($path));
    readfile_chunked($path);
}

function ReadWaveFile($file)
{
    global $config;

    $file = $config->FileEventsPath . $file;

    header("Content-Type: audio/x-wav", true);
    header('Accept-Ranges: bytes');
    header('Content-length: ' . filesize($file));
    readfile_chunked($file);
}

function readfile_chunked($filename, $retbytes = TRUE)
{
    $buffer = "";
    $cnt = 0;
    $handle = fopen($filename, "rb");
    if ($handle === false) {
        return false;
    }
    while (!feof($handle)) {
        $buffer = fread($handle, 1048576);
        echo $buffer;
        ob_flush();
        flush();
        if ($retbytes) {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt;
        // return num. bytes delivered like readfile() does.
    }
    return $status;
}


// 
// Logic Switch to determine GET parameters and retreive file recordings
// 
if (isset($_GET['cmd'])) {


    $cmd = $config->get_sanatized_varible('cmd');

    switch ($cmd) {

        case "GetDSDWaveFile":
            $instance = $config->get_sanatized_varible('instance');
            $strDate = $config->get_sanatized_varible('date');
            $strFile = $config->get_sanatized_varible('file');
            ReadDSDWaveFile($instance, $strDate, $strFile);
            return;


        case "GetWaveFile":
            $strFile = $config->get_sanatized_varible('file');
            ReadWaveFile($strFile);
            return;

        default:
            echo "Command Not Found";
            break;
    }
}
