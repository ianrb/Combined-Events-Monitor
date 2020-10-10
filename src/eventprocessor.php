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

            // If Authentication Required is true and Username or Password do match - redirect to login
            if ($this->objServer->config->AuthRequired) {

                $queryParmaters = array();
                $strPayload = $conn->httpRequest->getUri()->getQuery();
                parse_str($strPayload, $queryParmaters);

                if (empty($queryParmaters['payload'])) {
                    echo "\n- Socket Connection Rejected - Invalid/No Payload\n";
                    return;
                }

                $strPayload = base64_decode($queryParmaters['payload']);
                parse_str($strPayload, $queryParmaters);

                if (strpos($strPayload, "token") === true) {
                    echo "\n\n no token";
                    return;
                }

                $username = $queryParmaters['username'];
                $password = $queryParmaters['password'];

                // Close Connection and return if username and password do not match
                if ($username != $this->objServer->config->AuthUsername | $password != $this->objServer->config->AuthPassword) {
                    echo "\n- Socket Connection Rejected - Wrong Username or Password\n";
                    $conn->close();
                    return;
                } else {
                    echo "\n- Socket Connection Accepted - Login Succeeded\n";
                }
            }


            $this->clients->attach($conn);


            // DSD Config - Groups
            $conn->send(json_encode([
                "cmd"   => "DSDConfig",
                "event" => $this->objServer->DSDConfig
            ]));


            // New Client - Send recent DSD+ Events
            $iDSD = ($this->objServer->DDSTotalInstances - 1);
            for ($instance = 0; $instance <= $iDSD; $instance++) {

                if ($instance < count($this->objServer->DDSEvents)) {

                    if (($this->objServer->DDSEvents[$instance])) {

                        $recentEvents = array_slice($this->objServer->DDSEvents[$instance], -200, 200);

                        $conn->send(json_encode([
                            "cmd"   => "DSDEvents",
                            "instance" => $instance,
                            "events"   => $recentEvents,
                        ]));
                    }
                }
            }


            // New Client - Send recent File Events
            $iFile = ($this->objServer->FileTotalInstances - 1);
            for ($instance = 0; $instance <= $iFile; $instance++) {

                if ($instance < count($this->objServer->FileEvents)) {

                    if (($this->objServer->FileEvents[$instance])) {

                        $recentEvents = array_slice($this->objServer->FileEvents[$instance], -200, 200);

                        $conn->send(json_encode([
                            "cmd"   => "FileEvents",
                            "instance" => $instance,
                            "events"   => $recentEvents,
                        ]));
                    }
                }
            }


            // New Client - Send recent rtl_433 Events
            if ($instance < count($this->objServer->Rtl433Events)) {

                $recentEvents = array_slice($this->objServer->Rtl433Events[0], -200, 200);
                $conn->send(json_encode([
                    "cmd"   => "rtl433Events",
                    "instance" => 0,
                    "events"   => $recentEvents,
                ]));
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
