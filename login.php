<?php

require_once __DIR__ . "/src/config.php";

$config = new AppConfig();

session_start();
$username = $_POST['username'];
$password = $_POST['password'];

if (isset($username, $password)) {
    $_SESSION['username'] = $username;
    $_SESSION['password'] = $password;
    header("location: index.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Ian Bowman">

    <title>LOGIN - <?php echo $config->WebsiteName ?></title>
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

    <!-- code for this project -->
    <script src="/js/script.js?cb=257"></script>

</head>

<body>

    <style type="text/css">
        input {
            text-align: center;
            margin-top: 25px;
        }
    </style>

    <main role="main" class="container-fluid">


        <div class="row">

            <div class="col-md-12 col-lg-6 offset-lg-3">
                <div class="card text-center">
                    <div class="card-header">
                        <h5 class="card-title col-12">AUTHORISATION REQUIRED</h5>
                    </div>
                    <div class="card-body">

                        <form class="mt-4" method="post">
                            <h1 class="h5 mt-3 font-weight-bold">LOGIN TO</h1>
                            <h1 class="h3 mt-3 font-weight-bold"><?php echo $config->WebsiteName ?></h1>
                            <img class="mb-4" src="/img/LucyMoon.jpg" alt="" style="width: 100px; height: 100px; border-radius: 25rem;">

                            <label for="username" class="sr-only">Username</label>
                            <input type="username" name="username" class="form-control" placeholder="Username" autocomplete="username" required autofocus>

                            <label for="password" class="sr-only">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Password" autocomplete="current-password" required>
                            <!--          
                            <div class="checkbox mb-3">
                                <label> <input type="checkbox" value="remember-me"> Remember me
                                </label>
                            </div> -->

                            <?php
                            if ($isError && $loginMethod == 3) {
                                echo "<p class=\"text-danger\">Could not find user account. Please check the spelling of email and password. If you have no registered please Sign Up first.</p>";
                            }
                            ?>
                            <button class="mt-3 btn btn-md btn-success btn-block" type="submit" name="btnLogin" id="btnLogin">LOGIN</button>
                        </form>

                    </div>
                </div>
            </div>

        </div>



    </main>

    <?php include_once('modals.php') ?>

</body>

</html>