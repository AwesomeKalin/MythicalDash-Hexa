<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/requirements/page.php');
$cpuprice = SettingsManager::getSetting("price_cpu");
$ramprice = SettingsManager::getSetting("price_memory");
$diskprice = SettingsManager::getSetting("price_disk_space");
$svprice = SettingsManager::getSetting("price_server_limit");
$portsprice = SettingsManager::getSetting("price_allocation");
$databaseprice = SettingsManager::getSetting("price_database");
$backupprice = SettingsManager::getSetting("price_backup");

$usr_coins = $session->getUserInfo("coins");
$usr_cpu = $session->getUserInfo("cpu");
$usr_ram = $session->getUserInfo("ram");
$usr_disk = $session->getUserInfo("disk");
$usr_svlimit = $session->getUserInfo("server_limit");
$usr_ports = $session->getUserInfo("ports");
$usr_databases = $session->getUserInfo("databases");
$usr_backup_limit = $session->getUserInfo("backups");

if (isset($_GET["buycpu"])) {
    if ($usr_coins >= $cpuprice) {
        $newcoins = $usr_coins - $cpuprice;
        $newcpu = $usr_cpu + "100";
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $newcoins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `cpu` = '" . mysqli_real_escape_string($conn, $newcpu) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        header("location: /store?s=" . $lang['store_thanks_for_buying']);
        $conn->close();
        die();
    } else {
        header("location: /store?e=" . $lang['store_need_more_coins']);
        die();
    }
}

if (isset($_GET["buyram"])) {
    if ($usr_coins >= $ramprice) {
        $newcoins = $usr_coins - $ramprice;
        $newram = $usr_ram + "1024";
        $conn->query("UPDATE `mythicaldash_users` SET `ram` = '" . mysqli_real_escape_string($conn, $newram) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $newcoins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        header("location: /store?s=" . $lang['store_thanks_for_buying']);
        $conn->close();
        die();
    } else {
        header("location: /store?e=" . $lang['store_need_more_coins']);
        die();
    }
}

if (isset($_GET["buydisk"])) {
    if ($usr_coins >= $diskprice) {
        $newcoins = $usr_coins - $diskprice;
        $newdisk = $usr_disk + "1024";
        $conn->query("UPDATE `mythicaldash_users` SET `disk` = '" . mysqli_real_escape_string($conn, $newdisk) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $newcoins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        header("location: /store?s=" . $lang['store_thanks_for_buying']);
        $conn->close();
        die();
    } else {
        header("location: /store?e=" . $lang['store_need_more_coins']);
        die();
    }
}

if (isset($_GET["buysv"])) {
    if ($usr_coins >= $svprice) {
        $newcoins = $usr_coins - $svprice;
        $newsv = $usr_svlimit + "1";
        $conn->query("UPDATE `mythicaldash_users` SET `server_limit` = '" . mysqli_real_escape_string($conn, $newsv) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $newcoins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        header("location: /store?s=" . $lang['store_thanks_for_buying']);
        $conn->close();
        die();
    } else {
        header("location: /store?e=" . $lang['store_need_more_coins']);
        die();
    }
}

if (isset($_GET["buyport"])) {
    if ($usr_coins >= $portsprice) {
        $newcoins = $usr_coins - $portsprice;
        $newport = $usr_ports + "1";
        $conn->query("UPDATE `mythicaldash_users` SET `ports` = '" . mysqli_real_escape_string($conn, $newport) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $newcoins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        header("location: /store?s=" . $lang['store_thanks_for_buying']);
        $conn->close();
        die();
    } else {
        header("location: /store?e=" . $lang['store_need_more_coins']);
        die();
    }
}


if (isset($_GET['buydata'])) {
    if ($usr_coins >= $databaseprice) {
        $newcoins = $usr_coins - $databaseprice;
        $newdb = $usr_databases + "1";
        $conn->query("UPDATE `mythicaldash_users` SET `databases` = '" . mysqli_real_escape_string($conn, $newdb) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $newcoins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        header("location: /store?s=" . $lang['store_thanks_for_buying']);
        $conn->close();
        die();
    } else {
        header("location: /store?e=" . $lang['store_need_more_coins']);
        die();
    }
}

if (isset($_GET['buyback'])) {
    if ($usr_coins >= $backupprice) {
        $newcoins = $usr_coins - $backupprice;
        $newbk = $usr_backup_limit + "1";
        $conn->query("UPDATE `mythicaldash_users` SET `backups` = '" . mysqli_real_escape_string($conn, $newbk) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $newcoins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        header("location: /store?s=" . $lang['store_thanks_for_buying']);
        $conn->close();
        die();
    } else {
        header("location: /store?e=" . $lang['store_need_more_coins']);
        die();
    }
}

?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include(__DIR__ . '/requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> -
        <?= $lang['store'] ?>
    </title>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
</head>

<body>
    <?php
    if (SettingsManager::getSetting("show_snow") == "true") {
        include(__DIR__ . '/components/snow.php');
    }
    ?>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">
                                <?= $lang['dashboard'] ?> /
                            </span>
                            <?= $lang['store'] ?>
                        </h4>
                        <?php include(__DIR__ . '/components/alert.php') ?>
                        <div id="ads">
                            <?php
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                ?>
                                <br>
                                <?= SettingsManager::getSetting("ads_code") ?>
                                <br>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        /*if (SettingsManager::getSetting("allow_payments") == "true") {
                            ?>
                            <div class="alert alert-warning" role="alert">
                                <?= $lang['store_not_have_enough_coins'] ?> <a href="/store/buy/coins">
                                    <?= $lang['store_wana_buy_coins'] ?>
                                </a>
                            </div>
                            <?php
                        }*/
                        ?>
                        <div class="row mb-5">
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/b6TNCeZ.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                Starter Plan
                                            </h5>
                                            <small class="text-muted">
                                                <code>$2/month</code>
                                            </small>
                                            <p class="card-text">
                                                Get 4gb RAM, 3 CPU Cores, 20GB Disk and 2 server slots for you and your
                                                friends!
                                            </p>
                                            <a href="/store/buy/starter" class="btn btn-outline-primary waves-effect">
                                                <?= $lang['store_buy'] ?>
                                            </a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/b6TNCeZ.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                Basic Plan
                                            </h5>
                                            <small class="text-muted">
                                                <code>$3/month</code>
                                            </small>
                                            <p class="card-text">
                                                Get 6gb RAM, 4 CPU Cores, 20GB Disk and 3 server slots so you can start
                                                making your dream server!
                                            </p>
                                            <a href="/store/buy/basic" class="btn btn-outline-primary waves-effect">
                                                <?= $lang['store_buy'] ?>
                                            </a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/b6TNCeZ.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                Standard Plan
                                            </h5>
                                            <small class="text-muted">
                                                <code>$4/month</code>
                                            </small>
                                            <p class="card-text">
                                                Get 8gb RAM, 5 CPU Cores, 30GB Disk and 4 server slots to launch your
                                                server to the world!
                                            </p>
                                            <a href="/store/buy/standard" class="btn btn-outline-primary waves-effect">
                                                <?= $lang['store_buy'] ?>
                                            </a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <img class="card-img-top mx-auto d-block" src="https://i.imgur.com/b6TNCeZ.png"
                                        alt="Card image cap" style="width: 200px;">
                                    <center>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                Advanced Plan
                                            </h5>
                                            <small class="text-muted">
                                                <code>$5/month</code>
                                            </small>
                                            <p class="card-text">
                                                Get 10gb RAM, 6 CPU Cores, 35GB Disk and 5 server slots to scale up your
                                                server!
                                            </p>
                                            <a href="/store/buy/advanced" class="btn btn-outline-primary waves-effect">
                                                <?= $lang['store_buy'] ?>
                                            </a>
                                        </div>
                                    </center>
                                </div>
                            </div>
                        </div>
                        <div id="ads">
                            <?php
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                ?>
                                <br>
                                <?= SettingsManager::getSetting("ads_code") ?>
                                <br>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php include(__DIR__ . '/components/footer.php') ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/requirements/footer.php') ?>
    <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
</body>

</html>