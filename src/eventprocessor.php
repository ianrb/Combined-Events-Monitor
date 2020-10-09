<?php

namespace DSDPlus;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class EventProcessor implements MessageComponentInterface
{
    public $clients;
    public $objServer;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        echo "DSD Server Started.\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // echo "New connection! ({$conn->resourceId})\n";
        try {

            $this->clients->attach($conn);


            // DSD Config - Groups
            $conn->send(json_encode([
                "cmd"   => "DSDConfig",
                "event" => $this->objServer->DSDConfig
            ]));


            // New Client - Send recent DSD+ Events
            $iDSD = ($this->objServer->DDSTotalInstances - 1);
            for ($instance = 0; $instance <= $iDSD; $instance++) {

                // $recentEvents = $this->objServer->DDSEvents[$instance];
                $recentEvents = array_slice($this->objServer->DDSEvents[$instance], -200, 200);

                $jsObj = json_encode([
                    "cmd"   => "DSDEvents",
                    "instance" => $instance,
                    "events"   => $recentEvents,
                ]);

                $conn->send($jsObj);
            }


            // New Client - Send recent File Events
            $iFile = ($this->objServer->FileTotalInstances - 1);
            for ($instance = 0; $instance <= $iFile; $instance++) {

                $recentEvents = array_slice($this->objServer->FileEvents[$instance], -200, 200);

                $conn->send(json_encode([
                    "cmd"   => "FileEvents",
                    "instance" => $instance,
                    "events"   => $recentEvents,
                ]));
            }


            // New Client - Send recent rtl_433 Events
            $recentEvents = array_slice($this->objServer->Rtl433Events[0], -200, 200);
            $conn->send(json_encode([
                "cmd"   => "rtl433Events",
                "instance" => 0,
                "events"   => $recentEvents,
            ]));
        } catch (\Exception $e) {
            echo "Error onOpen: " .   $e->getMessage() . "\n";
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
    }


    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }


    public function onMessage(ConnectionInterface $from, $msg)
    {
        if (!isset($from->token) || !$from->token)
            return;

        $objMsg = json_decode($msg, true);
        $cmd = $objMsg['cmd'];
    }
}
