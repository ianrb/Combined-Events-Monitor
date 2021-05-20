<?php

require_once __DIR__ . "/src/config.php";

$config = new AppConfig();

// Is Debug Show PHP errors
if ($config->isDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

?>

<!DOCTYPE html>

<html lang="en" style="background: #000;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Ian Bowman">
    <link rel="icon" href="favicon.ico">
    <title><?php echo $config->WebsiteName ?></title>


    <!-- Plugins CSS-->
    <link rel="stylesheet" href="plugins/bootstrap.min.css">
    <link rel="stylesheet" href="plugins/all.min.css">
    <link rel="stylesheet" href="plugins/spartan.css">
    <link rel="stylesheet" href="plugins/context.css">
    <!-- App Specific -->

    <?php if ($config->isDebug) { ?>

        <link rel="stylesheet" href="css/index.css?cb={automatically-updated}">
        <link rel="stylesheet" href="css/theme.css?cb={automatically-updated}">

    <?php } else { ?>

        <link rel="stylesheet" href="css/index.min.css?cb={automatically-updated}">

    <?php } ?>


</head>

<body style="display:none;">

    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">

        <a class="navbar-brand adobe-blank ml-auto" href="#"><?php echo $config->WebsiteName ?></a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="navbar-nav mr-auto">

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cog"></i>&nbsp;<span class="float-right h5 text-capitalize adobe-blank"><?php echo ($config->AuthRequired ? $config->AuthUsername : "Configuration"); ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">


                        <a class="dropdown-item" href="#mute-all">
                            <i class="fas fa-volume-up"></i>&nbsp;<span class="float-right">Mute All</span>
                        </a>
                        <a class="dropdown-item" href="#playback-mode">
                            <i class="fas fa-list-ol"></i>&nbsp;<span class="float-right">Linear</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item" href="#config">
                            <i class="fas fa-cogs"></i>&nbsp;<span class="float-right">Configuration</span>
                        </a>

                        <a class="dropdown-item" href="#ssl-bypass">
                            <i class="fas fa-key"></i>&nbsp;<span class="float-right">SSL Bypass</span>
                        </a>


                        <?php if ($config->AuthRequired) { ?>
                            <div class="dropdown-divider"></div>


                            <a class="dropdown-item" href="#logout">
                                <i class="fas fa-user-lock"></i>&nbsp;<span class="float-right">Logout</span>
                            </a>

                        <?php } ?>
                    </div>
                </li>

            </ul>
        </div>


    </nav>

    <main role="main" class="container-fluid">

        <div class="row">

            <!-- DSDPLus Events 0 -->
            <div class="event-group col-md-12 col-lg-6 col-xl-3" id="DSDPlus0">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4 text-left">
                                <button data-toggle="filter-events" type="button" class="btn btn-sm" title="Filter Events">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">DSD+ </h5>
                            </div>
                            <div class="col-4 text-right">
                                <button data-action="auto-scroll" type="button" class="btn btn-sm" title="Auto Scroll / Manual">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <button data-action="mute-unmute" type="button" class="btn btn-sm" title="Mute / Unmute">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button data-toggle="settings-popover" type="button" class="btn btn-sm" title="Settings">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body events">
                    </div>
                    <div class="card-footer">
                        <!-- Filter [talkgroup] / [radioid] -->
                        TIII 153.920 MHz
                    </div>
                </div>
            </div>

            <!-- ADS-B / DSDPlus LRRP -->
            <div class="event-group col-md-12 col-lg-6" id="MapboxMap">
                <div class="card text-center">
                    <div class="card-header">

                        <div class="row">
                            <div class="col-4 text-left">


                                <button data-toggle="filter-events" type="button" class="btn btn-sm" title="Filter Events">
                                    <i class="fas fa-search"></i>
                                </button>
                                <!-- <i class="pl-3"></i> -->

                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">ADS-B / LRRP </h5>
                            </div>
                            <div class="col-4 text-right">

                                <button data-action="mute-unmute" type="button" class="btn btn-sm" title="Mute / Unmute">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button data-toggle="settings-popover" type="button" class="btn btn-sm" title="Settings">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <div id="map"></div>
                    </div>
                    <div class="card-footer">

                        <div class="row">
                            <div class="col-6 text-left">


                                <button data-action="map-zoom-in" type="button" class="btn btn-sm" title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button data-action="map-zoom-out" type="button" class="btn btn-sm" title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>

                                <i class="pl-2"></i>

                                <button data-action="toggle-3d" type="button" class="btn btn-sm" title="Toggle 3D Buildings">
                                    <i class="fas fa-map"></i>
                                </button>


                                <i class="pl-4"></i>


                                <button data-action="toggle-plane" type="button" class="btn btn-sm">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <button data-action="toggle-town" type="button" class="btn btn-sm">
                                    <i class="fas fa-layer-group"></i>
                                </button>
                                <button data-action="toggle-trees" type="button" class="btn btn-sm">
                                    <i class="fas fa-tree"></i>
                                </button>

                            </div>
                            <div class="col-3 text-right">
                                <span>Layer</span>
                            </div>
                            <div class="col-3">
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


            <!-- DSDPlus Event 1 -->
            <div class="event-group col-md-12 col-lg-6 col-xl-3" id="DSDPlus1">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4 text-left">

                                <button data-toggle="filter-events" type="button" class="btn btn-sm" title="Filter Events">
                                    <i class="fas fa-search"></i>
                                </button>


                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">DSD+ </h5>
                            </div>
                            <div class="col-4 text-right">

                                <button data-action="auto-scroll" type="button" class="btn btn-sm" title="Auto Scroll / Manual">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <button data-action="mute-unmute" type="button" class="btn btn-sm" title="Mute / Unmute">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button data-toggle="settings-popover" type="button" class="btn btn-sm" title="Settings">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        TIII 159.225 MHz
                    </div>
                </div>
            </div>


            <!-- File Event 0 -->
            <div class="event-group col-md-12 col-lg-6 col-xl-3" id="FileEvent0">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4 text-left">
                                <button data-toggle="filter-events" type="button" class="btn btn-sm" title="Filter Events">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">CN RAIL </h5>
                            </div>
                            <div class="col-4 text-right">
                                <button data-action="auto-scroll" type="button" class="btn btn-sm" title="Auto Scroll / Manual">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <button data-action="mute-unmute" type="button" class="btn btn-sm" title="Mute / Unmute">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button data-toggle="settings-popover" type="button" class="btn btn-sm" title="Settings">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
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


            <!-- rtl_433 Events -->
            <div class="event-group col-md-12 col-lg-3" id="rtl433Event0">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4 text-left">
                                <button data-toggle="filter-events" type="button" class="btn btn-sm" title="Filter Events">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">rtl_433 </h5>
                            </div>
                            <div class="col-4 text-right">
                                <button data-action="auto-scroll" type="button" class="btn btn-sm">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <button data-action="mute-unmute" type="button" class="btn btn-sm">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button data-toggle="settings-popover" type="button" class="btn btn-sm" title="Settings">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        345 MHz
                    </div>
                </div>
            </div>

            <div class="event-group col-md-12 col-lg-3" id="rtl433Event1">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4 text-left">
                                <button data-toggle="filter-events" type="button" class="btn btn-sm" title="Filter Events">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">rtl_433 </h5>
                            </div>
                            <div class="col-4 text-right">

                                <button data-action="auto-scroll" type="button" class="btn btn-sm">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <button data-action="mute-unmute" type="button" class="btn btn-sm">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button data-toggle="settings-popover" type="button" class="btn btn-sm" title="Settings">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        433.920 MHz
                    </div>
                </div>
            </div>

            <!-- File Event 1 -->
            <div class="event-group col-md-12 col-lg-6 col-xl-3" id="FileEvent1">
                <div class="card text-center">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4 text-left">
                                <button data-toggle="filter-events" type="button" class="btn btn-sm" title="Filter Events">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="col-4">
                                <h5 class="card-title font-weight-bold">CYET ATZ</h5>
                            </div>
                            <div class="col-4 text-right">
                                <button data-action="auto-scroll" type="button" class="btn btn-sm" title="Auto Scroll / Manual">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <button data-action="mute-unmute" type="button" class="btn btn-sm" title="Mute / Unmute">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button data-toggle="settings-popover" type="button" class="btn btn-sm" title="Settings">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
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


<!-- Load Hidden Attributes for Javascript -->
<?php

function echoAttr($name, $val)
{
    echo "\n<input type=\"hidden\" id=\"hid_$name\" value=\"$val\">";
}
echoAttr("isDebug", $config->isDebug);
echoAttr("ServerAddress", $config->ServerAddress);

echoAttr("RecentEvents", $config->RecentEvents);
echoAttr("AuthRequired", $config->AuthRequired);
if ($config->AuthRequired) {
    echoAttr("AuthUsername", $config->AuthUsername);
    echoAttr("AuthPassword", base64_encode($config->AuthPassword));
}

?>

<!-- jQuery, Bootstrap, popper and other essential plugins  -->
<script src="/plugins/jquery-3.4.1.min.js"></script>
<script src="/plugins/popper.min.js"></script>
<script src="/plugins/howler.min.js"></script>

<script src="/plugins/bootstrap.bundle.min.js"></script>
<script src="/plugins/moment.min.js"></script>
<script src="/plugins/tether.min.js"></script>
<script src="/plugins/all.min.js"></script>
<script src="/plugins/jscolor.min.js"></script>
<script src="/plugins/context.js?cb=1"></script>
<!-- <script src="/plugins/wavesurfer.min.js"></script> -->

<!-- d3 -->
<script src="https://d3js.org/d3.v6.min.js"></script>

<!-- Mapbox -->
<script src='https://api.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.js'></script>
<link href='https://api.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.css' rel='stylesheet' />

<!-- code for this project - consider using Closure Compiler  -->
<script src="/js/config.js?cb={automatically-updated}"></script>
<script src="/js/mapbox.js?cb={automatically-updated}"></script>
<script src="/js/events.js?cb={automatically-updated}"></script>
<script src="/js/index.js?cb={automatically-updated}"></script>

</html>