<?php

require_once __DIR__ . "/src/config.php";

$config = new AppConfig();

// If Authentication Required is true and Username or Password do match - redirect to login
if ($config->AuthRequired) {
    session_start();
    $username = $_SESSION['username'];
    $password = $_SESSION['password'];

    if ($username != $config->AuthUsername | $password != $config->AuthPassword) {
        header("location: login.php");
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Ian Bowman">
    <link rel="icon" href="favicon.ico">
    <title><?php echo $config->WebsiteName ?></title>


    <!-- Load Hidden Attributes for Javascript -->
    <?php
    function echoAttr($name, $val)
    {
        echo "\n<input type=\"hidden\" id=\"hid_$name\" value=\"$val\">";
    }
    echoAttr("isDebug", $config->isDebug);
    echoAttr("ServerAddress", $config->ServerAddress);
    echoAttr("AuthRequired", $config->AuthRequired);
    if ($config->AuthRequired) {
        echoAttr("AuthUsername", $config->AuthUsername);
        echoAttr("AuthPassword", $config->AuthPassword);
    }

    ?>

    <!-- Plugins -->
    <link rel="stylesheet" href="/plugins/bootstrap.min.css">
    <link rel="stylesheet" href="/plugins/all.min.css">
    <link rel="stylesheet" href="/plugins/huebee.min.css">

    <!-- App Specific -->
    <link rel="stylesheet" href="/css/index.css?cb=258">
    <link rel="stylesheet" href="/css/theme.css?cb=258">

    <!-- jQuery, popper and other essential plugins  -->
    <script src="/plugins/jquery-3.4.1.min.js"></script>
    <script src="/plugins/popper.min.js"></script>
    <script src="/plugins/howler.min.js"></script>

    <script src="/plugins/bootstrap.min.js"></script>
    <script src="/plugins/moment.min.js"></script>
    <script src="/plugins/tether.min.js"></script>
    <script src="/plugins/all.min.js"></script>
    <script src="/plugins/huebee.min.js"></script>

    <!-- d3 -->
    <script src="https://d3js.org/d3.v6.min.js"></script>

    <!-- Mapbox -->
    <script src='https://api.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.css' rel='stylesheet' />

    <!-- code for this project -->
    <script src="/js/index.js?cb=258"></script>

</head>

<body>

    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="#"><?php echo $config->WebsiteName ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Options</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown01">
                        <a class="dropdown-item" href="#mute-all">Mute All</a>
                        <a class="dropdown-item" href="#theme">Theme</a>

                        <?php if ($config->AuthRequired) {
                            echo '<div class="dropdown-divider"></div> <a class="dropdown-item" href="logout.php">Logout</a>';
                        } ?>

                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <main role="main" class="container-fluid">


        <div class="row">

            <div class="col-lg-12 col-xl-4" id="DSDPlus0">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4">
                                <i data-action="mute-unmute" class="mr-1 fas fa-volume-up"></i>
                                <input type="range" class="volume-selector" min="0" max="1" step="0.1">
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">DSD+ </h5>
                            </div>
                            <div class="col-4 text-right">
                                <i data-action="auto-scroll" class="fas fa-comment mr-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        <!-- Filter [talkgroup] / [radioid] -->
                        TIII / 153.920 MHz
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-xl-4" id="LRRP">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <h5 class="card-title font-weight-bold col-12">LRRP</h5>
                            <div class="fas-card-header">
                                <i data-action="toggle-3d" class="fas fa-map"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="map"></div>
                    </div>
                    <div class="card-footer">

                        <div class="row">
                            <div class="col-6 text-right">
                                <span>Select Layer</span>
                            </div>
                            <div class="col-6">
                                <select class="form-control select-theme">
                                    <option value="streets-v11">Streets</option>
                                    <option value="light-v10">Light</option>
                                    <option value="dark-v10" selected>Dark</option>
                                    <option value="outdoors-v11">Outdoors</option>
                                    <option value="satellite-v9">Satellite</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <div class="col-lg-12 col-xl-4" id="DSDPlus1">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4">
                                <i data-action="mute-unmute" class="mr-1 fas fa-volume-up"></i>
                                <input type="range" class="volume-selector" min="0" max="1" step="0.1">
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">DSD+ </h5>
                            </div>
                            <div class="col-4 text-right">
                                <i data-action="auto-scroll" class="fas fa-comment mr-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        TIII / 159.225 MHz
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-xl-4" id="FileEvent0">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4">
                                <i data-action="mute-unmute" class="mr-1 fas fa-volume-up"></i>
                                <input type="range" class="volume-selector" min="0" max="1" step="0.1">
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">CN RAIL </h5>
                            </div>
                            <div class="col-4 text-right">
                                <i data-action="auto-scroll" class="fas fa-comment mr-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        160.785, 161.195, 161.205, 161.415, 161.535, 162.000 - MHz FM
                    </div>
                </div>
            </div>


            <div class="col-lg-12 col-xl-4" id="rtl433Event0">
                <div class="card text-center">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold col-12">rtl_433</h5>
                        <!-- <i data-action="mute-unmute" class="fas fas-card-header fa-volume-up"></i> -->
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        345 MHz
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-xl-4" id="FileEvent1">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4">
                                <i data-action="mute-unmute" class="mr-1 fas fa-volume-up"></i>
                                <input type="range" class="volume-selector" min="0" max="1.0" step="0.1">
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">CYET ATZ</h5>
                            </div>
                            <div class="col-4 text-right">
                                <i data-action="auto-scroll" class="fas fa-comment mr-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        123.200 MHz AM
                    </div>
                </div>
            </div>
        </div>



    </main>

    <?php include_once('modals.php') ?>

</body>

</html>