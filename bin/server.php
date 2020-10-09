<?php

namespace DSDPlus;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/eventprocessor.php";

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

    public $isDebug;

    public $lastCount = array(0, 0, 0, 0, 0, 0);

    // DSD Config
    public $DSDConfig = array();
    public $DDSEvents = array();
    public $DDSTotalInstances = 2;
    public $DSDPlusFolder = "/home/sdr/Desktop/DSDPlus v2.268/";

    // File Events (rtl_fm / sox)
    public $FileEvents = array();
    public $FileTotalInstances = 2;
    public $FileEventsPath = "/home/sdr/Desktop/Recordings/";

    // rtl_433
    public $Rtl433TotalInstances = 1;
    public $Rtl433Events = array();
    public $Rtl433Path = "/home/sdr/Desktop/Recordings/rtl_433/";



    public function __construct()
    {
        error_reporting(E_ALL); // Error engine - always TRUE!
        ini_set('ignore_repeated_errors', TRUE); // always TRUE
        ini_set('display_errors', TRUE); // Error display - FALSE only in production environment or real server

    }

    function start()
    {
        $this->isDebug = (gethostname() == 'Mother-Goose');

        if ($this->isDebug) {
            $this->DSDPlusFolder = "/home/ian/Documents/Projects/CEM/";
            $this->Rtl433Path = "/home/ian/Desktop/";
        }

        $address = ($this->isDebug ? "192.168.0.150" : "josieinthedark.ddns.net");

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

        $localCert = "/etc/apache2/ssl/server.crt";
        $localPk = "/etc/apache2/ssl/server.key";

        $app = new HttpServer(new Router(new UrlMatcher($routes, new RequestContext)));

        $secure_websockets = new \React\Socket\Server('0.0.0.0:8080', $loop);
        $secure_websockets = new \React\Socket\SecureServer($secure_websockets, $loop, [

            'local_cert'        => $localCert,
            'local_pk'          => $localPk,
            // Allow self signed certs (should be false in production)
            // 'allow_self_signed' => true,
            'verify_peer' => FALSE
        ]);

        $secure_websockets_server = new \Ratchet\Server\IoServer($app, $secure_websockets, $loop);


        // Load Groups
        $this->DSDConfig = $this->loadDSDConfig();


        $loop->addPeriodicTimer(5, function () {

            // Check for DSD Updates
            $iDSD = ($this->DDSTotalInstances - 1);
            for ($instance = 0; $instance <= $iDSD; $instance++) {
                $this->DDSEvents[$instance] = $this->getDSDPlusEvents($instance);
            }

            // LRRP
            $this->DDSEvents[2] = $this->getDSDPlusLRRP(2);

            // File Events
            $this->FileEvents[0] = $this->getFileEvents(3, "CN");
            $this->FileEvents[1] = $this->getFileEvents(4, "CYET");

            // rtl_433
            $this->Rtl433Events[0] = $this->getRtl433Events(5);
        });


        $secure_websockets_server->run();
    }

    function loadDSDConfig()
    {

        // array("groups" => $this->objServer->DSDConfig);


        // Groups
        $groups = [];
        $strFileContents = file_get_contents("$this->DSDPlusFolder/DSDPlus.groups", "r");
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
        $strFileContents = file_get_contents("$this->DSDPlusFolder/DSDPlus.radios", "r");
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


    function getDSDPlusLRRP($instance)
    {

        try {

            $events = array();

            $strFileContents = file_get_contents("$this->DSDPlusFolder/DSDPlus.LRRP", "r");
            $strFileContents = str_replace("       ", " ", $strFileContents);
            $strFileContents = str_replace("  ", " ", $strFileContents);
            $strFileContents = str_replace("\t", " ", $strFileContents);

            // Line by Line Fixes
            $lines = explode("\n", $strFileContents);
            $icnt = 0;

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

                if ($icnt >= $this->lastCount[$instance]) {
                    foreach ($this->eventProcessor->clients as $client) {

                        $client->send(json_encode([
                            "cmd"   => "DSDLRRP",
                            "event" => [$date, $time, $RID, $lat, $lng, $speed, $unk]
                            // "event" => json_encode($events)
                        ]));
                    }
                }
                $icnt++;
                array_push($events, [$date, $time, $RID, $lat, $lng, $speed, $unk]);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $this->lastCount[$instance] = $icnt;
        return $events;
    }

    function getDSDPlusEvents($instance)
    {

        try {

            $events = array();

            $dsdinstance = ($instance + 1);

            $path = "$this->DSDPlusFolder/VC-DSDPlus#${dsdinstance}.event";

            $strFileContents = file_get_contents($path, "r");

            $lines = [
                explode(
                    // "\r\n",
                    "\n",
                    $strFileContents
                )
            ];

            // Line by Line Fixes
            $lines = explode("\n", $strFileContents);
            $icnt = 0;

            foreach ($lines as $index => $line) {

                if ($line == null || stripos($line, " neighbor: ") || !str_contains($line, "Group call;")) {
                    continue;
                }

                $arTwoSplit = array_filter(explode('Group call;', $line));
                $line = str_replace('  ', ';', str_replace("   ", ";", $arTwoSplit[1]));
                $arLine = array_filter(explode(';', $line));

                if (count($arLine) < 4) {
                    continue;
                }

                $date = trim(str_replace("  ", ' ', $arTwoSplit[0]));
                $tg = trim(str_replace("TG=", '', $arLine[0]));
                $rid = trim(str_replace("RID=", '', $arLine[1]));
                $slot = trim(str_replace("Slot=", '', $arLine[2]));
                $duration = trim(str_replace("s", '', $arLine[3]));

                $message = [$date, $tg, $rid, $slot, $duration];

                // echo "cidx: " . $icnt . " - lc: " . $this->lastCount[$instance];
                // New Events
                if ($icnt >= $this->lastCount[$instance]) {
                    foreach ($this->eventProcessor->clients as $client) {

                        $client->send(json_encode([
                            "cmd"   => "DSDEvent",
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

    function getFileEvents($instance, $name)
    {
        $path = $this->FileEventsPath . $name . '/';
        $events = array();
        $icnt = 0;

        $files = scandir($path);
        natcasesort($files);

        echo "\n\n\n\n\n\n\n\nfile event File order: " . print_r($files);

        foreach ($files as $index => $file) {

            if ($file != '.' && $file != '..' && $file != '.htaccess') {

                $fs = filesize($path . $file);
                if ($fs > 10) {

                    $time = filemtime($path . $file);
                    $duration = round($fs / 24000, 0);
                    $val = [$name . '/' . $file, $time, $duration];

                    if ($icnt >= $this->lastCount[$instance]) {
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
            }
        }


        $this->lastCount[$instance] = $icnt;
        return $events;
    }

    function getRtl433Events($instance)
    {
        $events = array();
        $icnt = 0;

        $strFileContents = file_get_contents($this->Rtl433Path .  "rtl_345.json", "r");
        $strFileContents = str_replace("\n", ",", $strFileContents);
        $strFileContents = substr($strFileContents, 0, strlen($strFileContents) - 1);
        $strFileContents = '[' . $strFileContents . ']';
        $jsonContents = json_decode($strFileContents);

        $lastId = 0;
        $lastState = '';
        $lastTime = '';

        foreach ($jsonContents as $key => $value) {

            // echo "Item: " . print_r($value);
            $id = $value->id ?? '';
            $time = $value->time;
            $model = $value->model ?? '';
            $channel = $value->channel ?? '';
            $event = $value->event ?? '';
            $state = $value->state ?? '';
            $alarm = $value->alarm ?? '';
            $tamper = $value->tamper ?? '';
            $battery_ok = $value->battery_ok ?? '';
            $heartbeat = $value->heartbeat ?? '';
            $mod = $value->mod ?? '';
            $freq = $value->freq ?? '';
            $rssi = $value->rssi;
            $snr = $value->snr;
            $noise = $value->noise;

            $timeDiff = (strtotime($time) - strtotime($lastTime));

            // Skip if Mode id and state is the same and within 3 seconds
            if ($lastId == $id && $lastState == $state && $timeDiff < 4) {
                continue;
            }

            $lastId = $id;
            $lastState = $state;
            $lastTime = $time;

            $val = [$id, $time, $model, $channel, $event, $state, $alarm, $tamper, $battery_ok, $heartbeat, $mod, $freq, $rssi, $snr, $noise];

            if ($icnt >= $this->lastCount[$instance]) {

                foreach ($this->eventProcessor->clients as $client) {

                    $client->send(json_encode([
                        "cmd"   => "rtl433Event",
                        "instance" => 0,
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
}

// Load Class
$objDSDServer = new DSDServer();
$objDSDServer->start();