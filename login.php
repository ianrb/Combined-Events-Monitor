<?php

require_once __DIR__ . "/src/config.php";

$config = new AppConfig();

session_start();
$username = $_POST['username'];
$password = $_POST['password'];

if (isset($username, $password)) {
    $_SESSION['username'] = $username;
    $_SESSION['password'] = $password;
    header("location: /");
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
    <title>LOGIN - <?php echo $config->WebsiteName ?></title>

    <!-- Plugins -->
    <link rel="stylesheet" href="/plugins/bootstrap.min.css">
    <link rel="stylesheet" href="/plugins/all.min.css">
    <link rel="stylesheet" href="/plugins/fontawesome/css/all.css">

    <!-- App Specific -->
    <link rel="stylesheet" href="/css/index.css?cb=258">
    <link rel="stylesheet" href="/css/login.css?cb=258">
    <link rel="stylesheet" href="/css/theme.css?cb=258">

    <!-- jQuery, popper and other essential plugins  -->
    <script src="/plugins/jquery-3.4.1.min.js"></script>
    <script src="/plugins/popper.min.js"></script>
    <!-- <script src="/plugins/moment.min.js"></script> -->
    <script src="/plugins/tether.min.js"></script>
    <script src="/plugins/bootstrap.min.js"></script>
    <script src="/plugins/fontawesome/js/all.min.js"></script>


</head>

<body>

    <main role="main" class="container-fluid">

        <div class="card text-center" id="login">
            <div class="card-header">
                <h5 class="card-title">AUTHORISATION REQUIRED</h5>
            </div>
            <div class="card-body">

                <form class="mt-4" method="post">

                    <h1 class="h5 mt-3 font-weight-bold">LOGIN TO</h1>
                    <h1 class="h3 mt-3 font-weight-bold"><?php echo $config->WebsiteName ?></h1>
                    <img class="mb-4" src="/img/lucy-moon.jpg" alt="" style="width: 100px; height: 100px; border-radius: 25rem;">


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


    </main>

</body>

</html>