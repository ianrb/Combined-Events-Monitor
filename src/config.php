<?php
class AppConfig
{
    // 
    // General Configuration
    // 

    // Website Title and Header Name
    public $WebsiteName = "COMBINED EVENTS MONITOR";

    // Debug Hostname - If server matches this string isDebug mode will be true and Debug variables will be used instead of Production
    private $HostNameDebug = "Mother-Goose";

    //  Debug Server Address
    private $ServerAddressDebug = "192.168.0.150";

    // Production Server Address
    private $ServerAddressProd = "josieinthedark.ddns.net";

    // Update Frequency (seconds) - How often to check for new events
    public $UpdateFrequency = 4;

    // On New Client Connection - How many recent events to send (too many will cause performance delays)
    public $RecentEvents = 220;

    // 
    // Authentication
    // 
    public $AuthRequired = true;
    public $AuthUsername = "josie";
    public $AuthPassword = "testing";

    // 
    // SSL Certificate
    //

    // Certificate File
    private $SSLCertificateDebug = "/etc/apache2/ssl/server.crt";
    private $SSLCertificateProd = "/etc/apache2/ssl/server.crt";

    // Key File
    private $SSLKeyDebug = "/etc/apache2/ssl/server.key";
    private $SSLKeyProd = "/etc/apache2/ssl/server.key";


    // 
    // Files & Directories
    // 

    // DSD+ directories
    private $DSDPlusFolderDebug = "/home/ian/Documents/SDR/DSDPlus v2.268/";
    private $DSDPlusFolderProd = "/home/sdr/Desktop/DSDPlus v2.268/";

    // File Recordings parent directory
    private $FileEventsPathDebug = "/home/sdr/Desktop/Recordings/";
    private $FileEventsPathProd = "/home/sdr/Desktop/Recordings/";

    // rtl_433 JSON output directory
    private $Rtl433PathDebug = "/home/ian/Desktop/";
    private $Rtl433PathProd = "/home/sdr/Desktop/Recordings/rtl_433/";

    public function __construct()
    {

        // Set isDebug true when debug hostname detected
        // isDebug - Changes several paramters within the application to adjust for different network and folder paths
        $this->isDebug = (gethostname() == $this->HostNameDebug);

        if ($this->isDebug) {

            $this->ServerAddress = $this->ServerAddressDebug;

            $this->SSLCertificate = $this->SSLCertificateDebug;
            $this->SSLKey = $this->SSLKeyDebug;

            $this->DSDPlusFolder = $this->DSDPlusFolderDebug;
            $this->FileEventsPath = $this->FileEventsPathDebug;
            $this->Rtl433Path = $this->Rtl433PathDebug;
        } else {

            $this->ServerAddress = $this->ServerAddressProd;

            $this->SSLCertificate = $this->SSLCertificateProd;
            $this->SSLKey = $this->SSLKeyProd;

            $this->DSDPlusFolder = $this->DSDPlusFolderProd;
            $this->FileEventsPath = $this->FileEventsPathProd;
            $this->Rtl433Path = $this->Rtl433PathProd;
        }
    }

    // Do Not Edit
    // These properties are set in __construct function
    public $isDebug = false;

    public $ServerAddress = "";

    public $SSLCertificate = "";
    public $SSLKey = "";

    public $DSDPlusFolder = "";
    public $FileEventsPath = "";
    public $Rtl433Path = "";
}
