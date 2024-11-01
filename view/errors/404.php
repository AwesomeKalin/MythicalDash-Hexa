<?php
use MythicalDash\SettingsManager;

http_response_code(404);
?>
<!DOCTYPE html>

<html lang="en" class="dark-style customizer-hide" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <?php include (__DIR__ . '/../requirements/head.php'); ?>
  <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-misc.css" />
  <title>
    <?= SettingsManager::getSetting('name') ?> - <?= $lang['404_title']?>
  </title>

</head>

<body>
  <?php
    if (SettingsManager::getSetting('show_snow') == 'true') {
      include (__DIR__ . '/../components/snow.php');
    }
  ?>
  <div id="preloader" class="discord-preloader">
    <div class="spinner"></div>
  </div>
  <div class="container-xxl container-p-y">
    <div class="misc-wrapper">
      <h2 class="mb-1 mx-2"><?= $lang['404_subtitle']?></h2>
      <p class="mb-4 mx-2">
        <?= $lang['404_description']?>
      </p>
      <a href="/dashboard" class="btn btn-primary mb-4"><?= $lang['home']?></a>
    </div>
  </div>
  <?php include (__DIR__ . '/../requirements/footer.php'); ?>
</body>

</html>