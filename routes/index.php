<?php
use MythicalDash\SettingsManager;
use MythicalDash\Main;
/**$router->add('/', function() {
    if (isset($_GET['e'])) {
        header('location: /dashboard?e='. $_GET['e']);
    } else if (isset($_GET['s'])) {
        header('location: /dashboard?s='. $_GET['s']);
    } else {
        header('location: /dashboard');
    }
});
*/


$router->add('/', function() {
    require("../include/main.php");
    if (SettingsManager::getSetting("landingpage") == "false") {
        if (isset($_GET['e'])) {
            header('location: /dashboard?e='. $_GET['e']);
        } else if (isset($_GET['s'])) {
            header('location: /dashboard?s='. $_GET['s']);
        } else { 
            header('location: /dashboard');
        }
    }
    if (isset($_GET['e'])) {
        header('location: /dashboard?e='. $_GET['e']);
    } else if (isset($_GET['s'])) {
        header('location: /dashboard?s='. $_GET['s']);
    } else {
        $template = file_get_contents('../templates/landing/index.html');
        $placeholders = array(
            '%APP_LOGO%',
            '%APP_NAME%',
            '%APP_URL%',
            '%APP_BG%',
            '%SEO_TITLE%',
            '%SEO_DESCRIPTION%',
            '%SEO_IMAGE%',
            '%SEO_KEYWORDS%',
            '%DISCORD_INVITE%',
            "%LANDING_DESCRIPTION%",
            '%LANDING_SUPPORT_EMAIL%'
        );
        $values = array(
        SettingsManager::getSetting("logo"),
        SettingsManager::getSetting("name"),
        Main::getAppUrl(),
        SettingsManager::getSetting("bg"),
        SettingsManager::getSetting("name"),
        SettingsManager::getSetting("seo_description"),
        SettingsManager::getSetting("logo"),
        SettingsManager::getSetting("seo_keywords"),
        SettingsManager::getSetting("discord_invite"),
        SettingsManager::getSetting("seo_description"),
        SettingsManager::getSetting("fromEmail"),);
        $templateView = str_replace($placeholders, $values, $template);
        die($templateView);
    }
});


$router->add('/dashboard', function () {
    require("../include/main.php");
    require("../view/dashboard.php");
});

$router->add("/store", function () {
    require("../include/main.php");
    require("../view/store.php");
});

$router->add("/store/buy/coins", function () {
    require("../include/main.php");
    require("../view/stripe/buy_coins.php");
});

$router->add("/store/buy/starter", function () {
    require("../include/main.php");
    require("../view/stripe/buy_starter.php");
});

$router->add("/store/buy/basic", function () {
    require("../include/main.php");
    require("../view/stripe/buy_basic.php");
});

$router->add("/store/buy/standard", function () {
    require("../include/main.php");
    require("../view/stripe/buy_standard.php");
});

$router->add("/store/buy/advanced", function () {
    require("../include/main.php");
    require("../view/stripe/buy_advanced.php");
});

$router->add("/store/buy/pro", function () {
    require("../include/main.php");
    require("../view/stripe/buy_pro.php");
});

$router->add("/store/buy/elite", function () {
    require("../include/main.php");
    require("../view/stripe/buy_elite.php");
});

$router->add("/store/get/stripe/coins", function () {
    require("../include/main.php");
    require("../view/stripe/get_coins.php");
});

$router->add("/store/get/stripe/starter", function () {
    require("../include/main.php");
    require("../view/stripe/get_starter.php");
});

$router->add("/store/get/stripe/basic", function () {
    require("../include/main.php");
    require("../view/stripe/get_basic.php");
});

$router->add("/store/get/stripe/standard", function () {
    require("../include/main.php");
    require("../view/stripe/get_standard.php");
});

$router->add("/store/get/stripe/advanced", function () {
    require("../include/main.php");
    require("../view/stripe/get_advanced.php");
});

$router->add("/store/get/stripe/pro", function () {
    require("../include/main.php");
    require("../view/stripe/get_pro.php");
});

$router->add("/store/get/stripe/elite", function () {
    require("../include/main.php");
    require("../view/stripe/get_elite.php");
});

$router->add("/store/cancel/1", function (): void {
    require("../include/main.php");
    require("../view/stripe/cancel_starter.php");
});

$router->add("/store/cancel/2", function (): void {
    require("../include/main.php");
    require("../view/stripe/cancel_basic.php");
});

$router->add("/store/cancel/3", function (): void {
    require("../include/main.php");
    require("../view/stripe/cancel_standard.php");
});

$router->add("/store/cancel/4", function (): void {
    require("../include/main.php");
    require("../view/stripe/cancel_advanced.php");
});

$router->add("/store/cancel/5", function (): void {
    require("../include/main.php");
    require("../view/stripe/cancel_pro.php");
});

$router->add("/store/cancel/6", function (): void {
    require("../include/main.php");
    require("../view/stripe/cancel_elite.php");
});

$router->add("/e/404", function () {
    require("../include/main.php");
    require("../view/errors/404.php");
});

$router->add("/e/adblock", function () {
    require("../include/main.php");
    require("../view/errors/adblock.php");
});

$router->add("/e/401", function () {
    require("../include/main.php");
    require("../view/errors/401.php");
});

$router->add("/e/maintenance", function () {
    require("../include/main.php");
    require("../view/errors/maintenance.php");
});

$router->add("/blank", function () {
    require("../include/main.php");
    require("../view/blank.php");
});

$router->add("/leaderboard", function () {
    require("../include/main.php");
    require("../view/leaderboard.php");
});

?>