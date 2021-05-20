<?php

namespace DSDPlus;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/config.php";
require_once __DIR__ . "/../src/eventprocessor.php";

use AppConfig;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\Router;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class DSDServer
{
    public $eventProcessor;

    public $config;
    public $isDebug = false;

    // dsd,dsd,lrrp,file,file,dump1090
    public $lastCount = array(0, 0, 0, 0, 0, 0, 0);

    // DSD Config
    public $DSDConfig = array();
    public $DDSEvents = array();
    public $DDSLRRPEvents = array();
    public $DDSTotalInstances = 2;

    // FlightFeeder
    public $AircraftEvents = array();


    // File Events (rtl_fm / sox)
    public $FileEvents = array();
    public $FileTotalInstances = 2;
    public $FileEventsDirectories = array();

    // rtl_433
    public $Rtl433TotalInstances = 2;
    public $Rtl433Events = array();


    private $bRunning = false;
    private $bFirstLoad = true;


    public function __construct()
    {
        error_reporting(E_ALL); // Error engine - always TRUE!
        ini_set('ignore_repeated_errors', TRUE); // always TRUE
        ini_set('display_errors', TRUE); // Error display - FALSE only in production environment or real server
    }

    function start()
    {

        // ini_set('default_socket_timeout', 4);

        $this->config = new AppConfig(false);

        $address = $this->config->ServerAddress;

        $this->eventProcessor = new EventProcessor;
        $this->eventProcessor->objServer = $this;

        $routes = new RouteCollection;
        $loop = \React\EventLoop\Factory::create();

        $decorated = new WsServer($this->eventProcessor);
        $decorated->enableKeepAlive($loop);
        $routes->add('events', new Route(
            '/events',
            array('_controller' => $decorated),
            array('Origin' => $address),
            array(),
            $address,
            array(),
            array('GET')
        ));

        $app = new HttpServer(new Router(new UrlMatcher($routes, new RequestContext)));

        $secure_websockets = new \React\Socket\Server('0.0.0.0:8080', $loop);
        $secure_websockets = new \React\Socket\SecureServer($secure_websockets, $loop, [

            'local_cert'        => $this->config->SSLCertificate,
            'local_pk'          => $this->config->SSLKey,
            // Allow self signed certs (should be false in production)
            'allow_self_signed' => $this->config->isDebug,
            'verify_peer' => false,
            'verify_host' => true
        ]);

        $secure_websockets_server = new \Ratchet\Server\IoServer($app, $secure_websockets, $loop);


        // Create File Event Array from sub-directories in Recordings folder
        if (is_dir($this->config->FileEventsPath)) {
            $files = scandir($this->config->FileEventsPath);
            foreach ($files as $index => $dir) {
                if (is_dir($this->config->FileEventsPath . $dir) && ($dir != '' && $dir != '.' && $dir != '..' && $dir != '.htaccess')) {
                    array_push($this->FileEventsDirectories, $dir);
                }
            }
        }


        // Load Groups
        $this->DSDConfig = $this->loadDSDConfig();




        $loop->addPeriodicTimer($this->config->UpdateFrequency, function () {

            if ($this->bRunning) {
                echo "\n- Skipping Timer (prior call still processing)";
                return;
            }

            $this->bRunning = true;

            if ($this->isDebug) {
                echo "\n\n---------------\n\nRunning Periodic Timer";
                echo "\n- DSD+ Events";
            }

            // Check for DSD Updates
            $iDSD = ($this->DDSTotalInstances - 1);
            for ($instance = 0; $instance <= $iDSD; $instance++) {
                $this->DDSEvents[$instance] = $this->getDSDPlusEvents($instance);
            }

            // LRRP
            if ($this->isDebug) {
                echo "\n- DSD+ LRRP";
            }

            $this->DDSLRRPEvents = $this->getDSDPlusLRRP($this->DDSTotalInstances);

            // File Events
            if ($this->isDebug) {
                echo "\n- File Events";
            }
            $iFile = ($this->FileTotalInstances - 1);
            for ($instance = 0; $instance <= $iFile; $instance++) {
                $this->FileEvents[$instance] = $this->getFileEvents(($instance + 3), $this->FileEventsDirectories[$instance]);
            }

            // rtl_433
            if ($this->isDebug) {
                echo "\n- rtl_433 Events";
            }

            // rtl_433 345 MHz
            $this->Rtl433Events[0] = $this->getRtl433Events(5, 345);

            // rtl_433 433 MHz
            $this->Rtl433Events[1] = $this->getRtl433Events(6, 433);

            // Dump1090
            if ($this->isDebug) {
                echo "\n- dump1090 Events";
            }
            $this->AircraftEvents = $this->getDump1090(7);

            if ($this->isDebug) {
                echo "\n Complete";
            }

            $this->bRunning = $this->bFirstLoad = false;
        });


        $secure_websockets_server->run();
    }

    // Implemented/Not Used - DSD+ Already Renames Log - This could be used for JS/Client Side UI
    function loadDSDConfig()
    {
        // Groups
        $groups = [];
        $strFileContents = file_get_contents($this->config->DSDPlusFolder . "DSDPlus.groups", "r");
        $lines = explode("\n", $strFileContents);
        foreach ($lines as $index => $line) {
            if ($line == null || strncmp($line, ';', 1) == 0) {
                continue;
            }
            $arLine = explode(',', $line);

            if (count($arLine) > 6) {
                $group = str_replace('"', '', trim($arLine[2]));
                $alias =  str_replace('"', '', trim($arLine[7]));
                if (strlen($alias) > 0) {
                    array_push($groups,  [$group, $alias]);
                }
            }
        }

        // Radios
        $radios = [];
        $strFileContents = file_get_contents($this->config->DSDPlusFolder . "DSDPlus.radios", "r");
        $lines = explode("\n", $strFileContents);
        foreach ($lines as $index => $line) {
            if ($line == null || strncmp($line, ';', 1) == 0) {
                continue;
            }
            $arLine = explode(',', $line);

            if (count($arLine) > 6) {
                $rid = str_replace('"', '', trim($arLine[3]));
                $alias =  str_replace('"', '', trim($arLine[8]));
                if (strlen($alias) > 0) {
                    array_push($radios, [$rid, $alias]);
                }
            }
        }


        return array("groups" => $groups, "radios" => $radios);
    }


    // Read tail of event file line by line and build array of valid events
    function getDSDPlusEvents($instance)
    {
        // $dsddate = date("Ymd");

        $files = [];
        $dsdinstance = ($instance + 1);
        $path = $this->config->DSDPlusFolder . "VC-Record#${dsdinstance}/";

        $icnt = 0;
        $events = array([]);

        for ($i = -1; $i <= 1; $i++) {

            $dsddate = date("Ymd", strtotime("$i days", time()));
            $subpath = $path . $dsddate . '/';

            // echo "\nRunning : $i - $dsddate - $subpath";

            if (file_exists($subpath)) {

                $files = scandir($subpath);

                foreach ($files as $index => $file) {

                    // skip directories, invalid files
                    if (strpos($file, "~") > -1  | $file == '' | $file == '.' | $file == '..' | $file == '.htaccess') {
                        continue;
                    }

                    $fs = filesize($subpath . $file);
                    $time = filemtime($subpath . $file);
                    $duration = round($fs / 24000, 0);

                    if (empty($time) || $duration < 1) {
                        continue;
                    }

                    // echo "\n\ntime: $file";
                    // echo "\ntime: $time";
                    // echo "\ntime isset: " . isset($time);

                    $arfile = explode('_', str_replace(".wav", "", $file));

                    $slot = $arfile[5];
                    $tg = $arfile[7];
                    $rid = $arfile[8];

                    $val = [$dsddate, $time, $file, $slot, $tg, $rid, $duration];

                    if (!$this->bFirstLoad && $icnt >= $this->lastCount[$instance]) {
                        foreach ($this->eventProcessor->clients as $client) {
                            $client->send(json_encode([
                                "cmd"   => "DSDEvent",
                                "instance" => $instance,
                                "event" => $val
                            ]));
                        }
                    }
                    $icnt++;

                    array_push($events, $val);
                }
            }
        }


        // natcasesort($events);

        foreach ($events as $index => $event) {
        }

        $this->lastCount[$instance] = $icnt;


        // TESTING Bug Fix - If return array is larger than RecentEvents variable - slice only x
        if ($events) {
            $eventCount = count($events) - 1;
            if ($eventCount > $this->config->RecentEvents) {
                $eventCount = $this->config->RecentEvents;
            }
            $recentEvents = array_slice($events, -$eventCount, $eventCount);
            return $recentEvents;
        }

        return null;
    }


    // Read LRRP file line by line to build an array of valid events
    function getDSDPlusLRRP($instance)
    {
        try {

            $path = $this->config->DSDPlusFolder . "DSDPlus.LRRP";

            if (!file_exists($path)) {
                echo "\nSkipping non-existent file/directory: $path";
                return;
            }

            $iLineCount = shell_exec("wc -l " . " '$path'");
            $iLineCount = intval($iLineCount);

            $icnt = ($iLineCount - $this->config->RecentEvents) - 1;

            $events = array();
            // $strFileContents = file_get_contents($path, "r");            
            $strFileContents = shell_exec("tail -n " . $this->config->RecentEvents . " '$path'");

            $strFileContents = str_replace("       ", " ", $strFileContents);
            $strFileContents = str_replace("  ", " ", $strFileContents);
            $strFileContents = str_replace("\t", " ", $strFileContents);

            // Line by Line Fixes
            $lines = explode("\n", $strFileContents);

            // echo "\n lrrp inst cnt" . $this->lastCount[$instance];

            foreach ($lines as $index => $line) {

                if ($line == null) {
                    continue;
                }

                $arLine = explode(' ', $line);

                $date = $arLine[0];
                $time = $arLine[1];
                $RID = $arLine[2];
                $lat = $arLine[3];
                $lng = $arLine[4];
                $speed = $arLine[5];
                $unk = $arLine[6];

                $message = [$date, $time, $RID, $lat, $lng, $speed, $unk];

                if (!$this->bFirstLoad && $icnt >= $this->lastCount[$instance]) {

                    foreach ($this->eventProcessor->clients as $client) {

                        $client->send(json_encode([
                            "cmd"   => "DSDLRRP",
                            "event" => $message
                        ]));
                    }
                }
                $icnt++;
                array_push($events, $message);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $this->lastCount[$instance] = $icnt;
        return $events;
    }

    // Read faup1080 --stdout via file line by line to build an array of valid events
    // Shouldn't have formatted the FlightFeeder...
    function getDump1090($instance)
    {

        try {

            $path = "http://192.168.0.101/aircraft.txt";

            // Fix long socket connection from dump1090 file_get_contents task
            $ctx = stream_context_create(array(
                'http' =>
                array(
                    'timeout' => 7
                )
            ));


            $strFileContents = file_get_contents($path, false, $ctx);
            // file_put_contents("", $strFileContents);


            $events = array([]);

            // $iLineCount = shell_exec("wc -l " . " '$path'");
            // $iLineCount = intval($iLineCount);

            // $icnt = ($iLineCount - $this->config->RecentEvents) - 1;


            $icnt = 0;

            $lines = explode("\n", $strFileContents);

            foreach ($lines as $index => $line) {

                $arLine = array_filter(explode("\t", $line));
                $clock = $hexid = $ident = $squawk = $latitude = $longitude = $speed = $vrate = $track = $navheading = '';

                foreach ($arLine as $index2 => $line2) {
                    // echo "\n" . $arLine[$index2 + 1];
                    if (str_contains($line2, "clock")) {
                        $clock = $arLine[$index2 + 1];
                    }
                    if (str_contains($line2, "hexid")) {
                        $hexid = $arLine[$index2 + 1];
                    }
                    if (str_contains($line2, "ident")) {
                        $ident = $arLine[$index2 + 1];
                        $ident = substr($ident, 1, strlen($ident));
                        $arp = explode(" ", $ident);
                        $ident = $arp[0];
                    }
                    if (str_contains($line2, "squawk")) {
                        $arp = explode(" ", $arLine[$index2 + 1]);
                        $squawk = $arp[0];
                    }
                    if (str_contains($line2, "alt")) {
                        $arp = explode(" ", $arLine[$index2 + 1]);
                        $alt = $arp[0];
                    }
                    if (str_contains($line2, "position")) {
                        $position = $arLine[$index2 + 1];
                        $position = substr($position, 1, strlen($position));
                        $arp = explode(" ", $position);
                        $latitude = $arp[0];
                        $longitude = $arp[1];
                    }
                    if (str_contains($line2, "speed")) {
                        $arp = explode(" ", $arLine[$index2 + 1]);
                        $speed = $arp[0];
                    }
                    if (str_contains($line2, "vrate")) {
                        $arp = explode(" ", $arLine[$index2 + 1]);
                        $vrate = $arp[0];
                    }
                    if (str_contains($line2, "track")) {
                        $arp = explode(" ", $arLine[$index2 + 1]);
                        $track = $arp[0];
                    }
                    if (str_contains($line2, "nav_heading")) {
                        $arp = explode(" ", $arLine[$index2 + 1]);
                        $navheading = $arp[0];
                    }
                }


                // Skip messages without clock,hexid,ident
                if (empty($clock) | empty($hexid) | empty($ident)) {
                    continue;
                }

                // Skip messages older than 5 minutes
                $age = (time() - $clock);
                // echo "\n\n\nAge: $age \n\n " . gettype($age);
                // echo "\n\n\nia: $age \n\n " . is_numeric($age);
                if (is_numeric($age) && $age > 1200) {
                    continue;
                }

                $message = [$clock, $hexid, $ident, $squawk, $alt, $vrate, $latitude, $longitude, $speed, $track, $navheading];
                // print_r($message);

                // New Events
                if (!$this->bFirstLoad && $icnt >= $this->lastCount[$instance]) {
                    foreach ($this->eventProcessor->clients as $client) {

                        $client->send(json_encode([
                            "cmd"   => "AircraftEvent",
                            "instance" => $instance,
                            "event" => $message
                        ]));
                    }
                }

                $icnt++;
                array_push($events, $message);
            }
        } catch (Exception $ex) {
            $msg = $ex->getMessage();
            echo $msg;
        }


        $this->lastCount[$instance] = $icnt;
        return $events;
    }

    // Read recording directories file by file to build an array of valid events
    function getFileEvents($instance, $name)
    {
        $path = $this->config->FileEventsPath . $name . '/';

        if (!file_exists($path)) {
            echo "\nSkipping non-existent file/directory: $path";
            return;
        }

        $icnt = 0;
        $events = array([]);
        $files = scandir($path);
        // Sort low to high
        natcasesort($files);
        // Remove the last file as this is still un-closed from sox
        array_pop($files);

        foreach ($files as $index => $file) {

            // skip directories, invalid files
            if ($file == '' | $file == '.' | $file == '..' | $file == '.htaccess') {
                continue;
            }

            $fs = filesize($path . $file);
            $time = filemtime($path . $file);


            // echo "\n\ntime: $file";
            // echo "\ntime: $time";
            // echo "\ntime isset: " . isset($time);

            if (empty($time)) {
                continue;
            }

            $duration = round($fs / 24000, 0);
            $val = [$name . '/' . $file, $time, $duration];

            if (!$this->bFirstLoad && $icnt >= $this->lastCount[$instance]) {
                foreach ($this->eventProcessor->clients as $client) {
                    $client->send(json_encode([
                        "cmd"   => "FileEvent",
                        "instance" => ($instance - 3),
                        "event" => $val
                    ]));
                }
            }
            $icnt++;
            array_push($events, $val);
        }


        $this->lastCount[$instance] = $icnt;
        return $events;
    }

    // Read rtl_433 JSON events file
    function getRtl433Events($instance, $freq)
    {
        $path = $this->config->Rtl433Path .  "rtl_$freq.json";

        if (!file_exists($path)) {
            echo "\nSkipping non-existent file/directory: $path";
            return;
        }

        $iLineCount = shell_exec("wc -l " . " '$path'");
        $iLineCount = intval($iLineCount);

        $icnt = ($iLineCount - $this->config->RecentEvents) - 1;

        $events = array([]);
        $strFileContents = shell_exec("tail -n " . $this->config->RecentEvents . " '$path'");
        $strFileContents = str_replace("\n", ",", $strFileContents);
        $strFileContents = substr($strFileContents, 0, strlen($strFileContents) - 1);
        $strFileContents = '[' . $strFileContents . ']';
        $jsonContents = json_decode($strFileContents);

        foreach ($jsonContents as $key => $value) {

            if (!$this->bFirstLoad && $icnt >= $this->lastCount[$instance]) {


                foreach ($this->eventProcessor->clients as $client) {

                    $client->send(json_encode([
                        "cmd"   => "rtl433Event",
                        "instance" => ($instance - 5),
                        "event" => $value
                    ]));
                }
            }

            $icnt++;
            array_push($events, $value);
        }


        $this->lastCount[$instance] = $icnt;

        return $events;
    }
}

// 
// 
// Load Class and Start
$objDSDServer = new DSDServer();
$objDSDServer->start();
