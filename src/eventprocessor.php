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
        echo "- CEM Process Started.\n";
    }

    // 
    // New WebSocket Connection Opened
    // 
    public function onOpen(ConnectionInterface $conn)
    {

        // echo "\nNew connection! ($conn->resourceId) - $conn->remoteAddress";

        try {

            // If Authentication Required - Confirm Username and Password
            if ($this->objServer->config->AuthRequired) {

                $queryParameters = array();
                $strPayload = $conn->httpRequest->getUri()->getQuery();
                parse_str($strPayload, $queryParameters);

                if (empty($queryParameters['payload'])) {
                    echo "\e[0;31m\n- Socket Connection Rejected - Invalid/No Payload - ($conn->resourceId) - $conn->remoteAddress\e[0m\n";
                    $conn->close();
                    return;
                }

                $strPayload = base64_decode($queryParameters['payload']);
                parse_str($strPayload, $queryParameters);

                if (strpos($strPayload, "token") === true) {
                    echo "\e[0;31m\n- Socket Connection Rejected - Token not Specified - ($conn->resourceId) - $conn->remoteAddress\e[0m\n";
                    $conn->close();
                    return;
                }

                if (empty($queryParameters['username']) | empty($queryParameters['password'])) {
                    echo "\e[0;31m\n- Socket Connection Rejected - Invalid/No Username/Password - ($conn->resourceId) - $conn->remoteAddress\e[0m\n";
                    $conn->close();
                    return;
                }

                $username = $queryParameters['username'];
                $password = $queryParameters['password'];

                // Close Connection and return if username and password do not match
                if ($username != $this->objServer->config->AuthUsername | $password != $this->objServer->config->AuthPassword) {
                    echo "\e[0;31m\n- Socket Connection Rejected - Wrong Username or Password - ($conn->resourceId) - $conn->remoteAddress\e[0m\n";
                    $conn->close();
                    return;
                } else {
                    echo "\e[0;32m\n- Socket Connection Accepted - Login Succeeded - ($conn->resourceId) - $conn->remoteAddress\e[0m\n";
                }
            }


            $this->clients->attach($conn);


            // DSD Config - Groups
            $conn->send(json_encode([
                "cmd"   => "DSDConfig",
                "event" => $this->objServer->DSDConfig
            ]));


            // Recent DSD+ Events
            $iDSD = ($this->objServer->DDSTotalInstances - 1);
            for ($instance = 0; $instance <= $iDSD; $instance++) {

                if ($instance < count($this->objServer->DDSEvents)) {

                    if (($this->objServer->DDSEvents[$instance])) {

                        $events = $this->objServer->DDSEvents[$instance];

                        $conn->send(json_encode([
                            "cmd"   => "DSDEvents",
                            "instance" => $instance,
                            "events"   => $events,
                        ]));
                    }
                }
            }

            // DSD Recent LRRP
            if (($this->objServer->DDSLRRPEvents)) {

                // $eventCount = count($this->objServer->DDSLRRPEvents) - 1;
                // if ($eventCount > $this->objServer->config->RecentEvents) {
                //     $eventCount = $this->objServer->config->RecentEvents;
                // }
                // $recentEvents = array_slice($this->objServer->DDSLRRPEvents, -$eventCount, $eventCount);
                $recentEvents = array_slice($this->objServer->DDSLRRPEvents, -$this->objServer->config->RecentEvents, $this->objServer->config->RecentEvents);


                $conn->send(json_encode([
                    "cmd"   => "DSDLRRPs",
                    "events" => $recentEvents
                ]));
            }


            // Aircraft dump1090
            if (($this->objServer->AircraftEvents)) {

                $recentEvents = array_slice($this->objServer->AircraftEvents, -$this->objServer->config->RecentEvents, $this->objServer->config->RecentEvents);

                $conn->send(json_encode([
                    "cmd"   => "AircraftEvents",
                    "events" => $recentEvents
                ]));
            }


            // New Client - Send recent File Events
            $iFile = ($this->objServer->FileTotalInstances - 1);
            for ($instance = 0; $instance <= $iFile; $instance++) {

                if ($instance < count($this->objServer->FileEvents)) {

                    if (($this->objServer->FileEvents[$instance])) {

                        $eventCount = count($this->objServer->FileEvents[$instance]) - 1;
                        if ($eventCount > $this->objServer->config->RecentEvents) {
                            $eventCount = $this->objServer->config->RecentEvents;
                        }
                        $recentEvents = array_slice($this->objServer->FileEvents[$instance], -$eventCount, $eventCount);

                        $conn->send(json_encode([
                            "cmd"   => "FileEvents",
                            "instance" => $instance,
                            "events"   => $recentEvents,
                        ]));
                    }
                }
            }


            // New Client - Send recent rtl_433 Events
            $iRtl433 = ($this->objServer->Rtl433TotalInstances - 1);
            for ($instance = 0; $instance <= $iRtl433; $instance++) {

                if ($instance < count($this->objServer->Rtl433Events)) {

                    if (($this->objServer->Rtl433Events[$instance])) {

                        $eventCount = count($this->objServer->Rtl433Events[$instance]) - 1;
                        if ($eventCount > $this->objServer->config->RecentEvents) {
                            $eventCount = $this->objServer->config->RecentEvents;
                        }
                        $recentEvents = array_slice($this->objServer->Rtl433Events[$instance], -$eventCount, $eventCount);

                        $conn->send(json_encode([
                            "cmd"   => "rtl433Events",
                            "instance" => $instance,
                            "events"   => $recentEvents,
                        ]));
                    }
                }
            }
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
