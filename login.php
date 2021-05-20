<?php

require_once __DIR__ . "/src/config.php";

// do not enforce page auth for login page
$config = new AppConfig(false);

// Is Debug Show PHP Errors
if (!$config->isDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}



if (isset($_POST['username'], $_POST['password'])) {

    session_start();

    $_SESSION['username'] = $_POST['username'];
    $_SESSION['password'] = $_POST['password'];

    header("location: /dashboard");
}

?>

<!DOCTYPE html>
<html lang="en" style="background: #000;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Nobody reads this part, right?">
    <meta name="author" content="Ian Bowman">
    <link rel="icon" href="favicon.ico">
    <title>Login - <?php echo $config->WebsiteName ?></title>


</head>

<body>

    <!-- Plugins -->
    <link rel="stylesheet" href="/plugins/bootstrap.min.css">
    <link rel="stylesheet" href="/plugins/all.min.css">
    <link rel="stylesheet" href="/plugins/spartan.css">

    <!-- App Specific -->

    <?php if ($config->isDebug) { ?>

        <link rel="stylesheet" href="/css/index.css?cb={automatically-updated}">
        <link rel="stylesheet" href="/css/login.css?cb={automatically-updated}">
        <link rel="stylesheet" href="/css/theme.css?cb={automatically-updated}">

    <?php } else { ?>

        <link rel="stylesheet" href="/css/index.min.css?cb={automatically-updated}">
        <link rel="stylesheet" href="/css/login.css?cb={automatically-updated}">

    <?php } ?>




    <main role="main" class="container-fluid">

        <div class="card text-center" id="login">
            <div class="card-header">
                <!-- <h5 class="card-title">AUTHORISATION REQUIRED</h5> -->
                <div class="lucy-container">
                    <img class="lucy-logo" src="/img/lucy-moon.jpg" alt="Zaddy Ba Zoos!">
                </div>

                <h5 class="mt-2 card-title"><?php echo $config->WebsiteName ?></h5>

            </div>
            <div class="card-body">

                <form id="login-form" name="login" method="post">

                    <label for="username" class="sr-only">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Username" autocomplete="username" required autofocus>

                    <label for="password" class="sr-only">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" autocomplete="current-password" required>

                    <div class="checkbox mb-3" style="display:none;">
                        <label> <input type="checkbox" value="remember-me"> Remember me
                        </label>
                    </div>

                    <?php
                    if (isset($isError, $loginMethod) && $isError && $loginMethod == 3) {
                        echo "<p class=\"text-danger\">Could not find user account. Please check the spelling of email and password. If you have no registered please Sign Up first.</p>";
                    }
                    ?>
                    <button class="mt-3 btn btn-md btn-success btn-block" type="submit" name="btnLogin" id="btnLogin">LOGIN</button>
                </form>





                <?php

                $userip = $_SERVER['REMOTE_ADDR'];
                if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                    $userip = $_SERVER['HTTP_CF_CONNECTING_IP'];
                }
                echo "<hr><span><b>IP</b>: $userip<br><span>";

                ?>



            </div>
        </div>


    </main>

    <!-- jQuery, popper and other essential plugins  -->
    <script src="/plugins/jquery-3.4.1.min.js"></script>
    <script src="/plugins/popper.min.js"></script>
    <script src="/plugins/all.min.js"></script>
    <script src="/plugins/tether.min.js"></script>

    <script src="/plugins/bootstrap.bundle.min.js"></script>

    <script>
        $(function() {
            $('body').show();
            $("input[name='username']").focus();
        });
    </script>


</body>

</html>