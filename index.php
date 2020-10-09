<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Ian Bowman">
    <title>Combined Events Monitor</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/style.css?cb=257">
    <link rel="stylesheet" href="css/theme.css?cb=257">
    <link href="fontawesome/css/all.css" rel="stylesheet">

    <script src="/js/jquery-3.4.1.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>

    <script src="/js/moment.min.js"></script>
    <script src="/js/tether.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/all.min.js"></script>
    <script src="/fontawesome/js/all.js"></script>

    <script src="https://d3js.org/d3.v6.min.js"></script>

    <script src='https://api.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.css' rel='stylesheet' />


    <script src="/js/script.js?cb=257"></script>

</head>

<body>



    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="#">COMBINED EVENTS MONITOR</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="navbar-nav ml-auto">
                <!-- <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li> 
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i data-action="toggle-3d" class="fas fa-volume-mute"></i>
                        Primary Output
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
                </li>
                -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Options</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown01">
                        <a class="dropdown-item" href="#mute-all">Mute All</a>
                    </div>
                </li>
            </ul>
            <!-- <form class="form-inline my-2 my-lg-0">
                <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
                <button class="btn btn-secondary my-2 my-sm-0" type="submit">Search</button>
            </form> -->
        </div>
    </nav>





    <main role="main" class="container-fluid">


        <div class="row">

            <div class="col-lg-12 col-xl-4" id="DSDPlus0">
                <div class="card text-center">
                    <div class="card-header">
                        <h5 class="card-title col-12">DSD+ </h5>
                        <i data-action="play-pause" class="fas fas-card-header fa-volume-up"></i>
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
                            <h5 class="card-title col-12">LRRP</h5>
                            <i data-action="toggle-3d" class="fas fas-card-header fa-map"></i>
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
                        <h5 class="card-title col-12">DSD+</h5>
                        <i data-action="play-pause" class="fas fas-card-header fa-volume-up"></i>
                    </div>
                    <div class="card-body">
                        <div class="events"></div>
                    </div>
                    <div class="card-footer">
                        TIII / 159.225 MHz
                    </div>
                </div>
            </div>


            <!-- </div> -->
            <!-- <div class="row mt-3"> -->

            <div class="col-lg-12 col-xl-4" id="FileEvent0">
                <div class="card text-center">
                    <div class="card-header">
                        <h5 class="card-title col-12">CN Rail - Edson</h5>
                        <i data-action="play-pause" class="fas fas-card-header fa-volume-up"></i>
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
                        <h5 class="card-title col-12">rtl_433</h5>
                        <!-- <i data-action="play-pause" class="fas fas-card-header fa-volume-up"></i> -->
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
                        <h5 class="card-title col-12">CYET ATZ - Edson Airport</h5>
                        <i data-action="play-pause" class="fas fas-card-header fa-volume-up"></i>
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