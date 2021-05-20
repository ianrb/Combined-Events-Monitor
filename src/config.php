<?php
class AppConfig
{
    // 
    // General Configuration
    // 

    // Website Title and Header Name
    public $WebsiteName = "Combined Events Monitor";

    // Debug Hostname - If server matches this string isDebug mode will be true and Debug variables will be used instead of Production
    private $HostNameDebug = "Mother-Goose";

    //  Debug Server Address
    private $ServerAddressDebug = "192.168.0.150";

    // Production Server Address
    private $ServerAddressProd = "josieinthedark.ddns.net";

    // Update Frequency (seconds) - How often to check for new events
    public $UpdateFrequency = 6;

    // On New Client Connection - How many recent events to send (too many will cause performance delays)
    public $RecentEvents = 400;

    // 
    // Authentication
    // 
    public $AuthRequired = false;
    public $AuthUsername = "user";
    public $AuthPassword = "pass";

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

    // DSD+ directory
    private $DSDPlusFolderDebug = "/home/ian/Documents/SDR/DSDPlus v2.268/";
    private $DSDPlusFolderProd = "/home/sdr/Desktop/DSDPlus v2.268/";

    // File Recordings parent directory
    //      -    UPDATE - App scans parent directory and each folder is created array
    private $FileEventsPathDebug = "/home/ian/Desktop/CEM/Recordings/";
    private $FileEventsPathProd = "/home/sdr/Desktop/CEM/Recordings/";

    // rtl_433 JSON output directory
    private $Rtl433PathDebug = "/home/ian/Desktop/";
    private $Rtl433PathProd = "/home/sdr/Desktop/CEM/";

    public function __construct($enforceAuth = true)
    {

        // Set isDebug true when debug hostname detected
        // isDebug - Changes several parameters within the application to adjust for different network and folder paths
        $this->isDebug = (gethostname() == $this->HostNameDebug);

        if ($this->isDebug) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }

        // Pages that require config.php will require authentication when configured
        if ($enforceAuth && $this->AuthRequired) {
            $this->login();
        }

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

    // If Authentication Required is true and Username or Password do match - redirect to login
    public function login()
    {
        session_start();

        if (isset($_SESSION['username'], $_SESSION['password'])) {

            $username = $_SESSION['username'];
            $password = $_SESSION['password'];

            if ($username == $this->AuthUsername && $password == $this->AuthPassword) {
                // login success
            } else {
                // login authentication failure
                $this->logout(1);
            }
        } else {
            // login unset session
            $this->logout(2);
        }
    }

    public function logout($cmd = 0)
    {
        session_destroy();
        header("location: /login?cmd=" . $cmd);
        exit;
        return;
    }

    // Remove all HTML tags and all characters with ASCII value > 127, from $_GET variable
    function get_sanatized_varible($name)
    {
        $str = "";

        if (isset($_GET[$name])) {

            $str = $_GET[$name];
            $newstr = filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        }

        return $newstr;
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
