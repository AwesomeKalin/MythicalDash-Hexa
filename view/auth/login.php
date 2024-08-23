<?php
use MythicalDash\SettingsManager;
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;

try {
  $conn = new Connect();
  $conn = $conn->connectToDatabase();
  $session = new SessionManager();
  session_start();
  $csrf = new MythicalSystems\Utils\CSRFHandler;
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($csrf->validate('login-form')) {
      if (isset($_POST['login'])) {
        if (SettingsManager::getSetting("enable_turnstile") == "false") {
          $captcha_success = 1;
        } else {
          $captcha_success = \MythicalSystems\CloudFlare\Turnstile::validate($_POST["cf-turnstile-response"], $session->getIP(), SettingsManager::getSetting("turnstile_secretkey"));
        }
        if ($captcha_success) {
          $email = mysqli_real_escape_string($conn, $_POST['email']);
          $password = mysqli_real_escape_string($conn, $_POST['password']);
          if (!$email == "" || !$password == "") {
            $query = "SELECT * FROM mythicaldash_users WHERE email = '$email' OR username = '$email'";
            $result = mysqli_query($conn, $query);
            if ($result) {
              if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $hashedPassword = $row['password'];
                if (password_verify($password, $hashedPassword)) {
                  $token =  mysqli_real_escape_string($conn, $row['api_key']);
                  $email =  mysqli_real_escape_string($conn, $row['email']);
                  $banned =  mysqli_real_escape_string($conn, $row['banned']);
                  if (!$banned == "") {
                    header('location: /auth/login?e='.$lang['login_banned']);
                    exit; // Stop execution if user is banned
                  } else {
                    $usr_id = mysqli_real_escape_string($conn, $row['api_key']);
                    $url = "http://ipinfo.io/" . $session->getIP() . "/json";
                    $data = json_decode(file_get_contents($url), true);
                      if (SettingsManager::getSetting('enable_anti_vpn') == "true") {
                        if (isset($data['error']) || $data['org'] == "AS1221 Telstra Pty Ltd") {
                        header('location: /auth/login?e='.$lang['login_please_no_vpn']);
                        die();
                      }
                    }
                    $userids = array();
                    $loginlogs = mysqli_query($conn, "SELECT * FROM mythicaldash_login_logs WHERE userkey = '".mysqli_real_escape_string($conn, $usr_id)."'");
                    foreach ($loginlogs as $login) {
                      $ip = $login['ipaddr'];
                      if ($ip == "12.34.56.78") {
                        continue;
                      }
                      $saio = mysqli_query($conn, "SELECT * FROM mythicaldash_login_logs WHERE ipaddr = '" . mysqli_real_escape_string($conn,$ip) . "'");
                      foreach ($saio as $hello) {
                        if (in_array($hello['userkey'], $userids)) {
                          continue;
                        }
                        if ($hello['userkey'] == $usr_id) {
                          continue;
                        }
                        array_push($userids, $hello['userkey']);
                      }
                    }
                    if (SettingsManager::getSetting('enable_alting') == "true") {
                      if (count($userids) !== 0) {
                        header('location: /auth/login?e='.$lang['login_please_no_alts']);
                        die();
                      }
                    }
                    $conn->query("INSERT INTO mythicaldash_login_logs (ipaddr, userkey) VALUES ('" . mysqli_real_escape_string($conn,$session->getIP()) . "', '".mysqli_real_escape_string($conn,$usr_id)."')");

                    $cookie_name = 'token';
                    $cookie_value = $token;
                    setcookie($cookie_name, $cookie_value, time() + (10 * 365 * 24 * 60 * 60), '/');
                    $conn->query("UPDATE `mythicaldash_users` SET `last_ip` = '" . mysqli_real_escape_string($conn, $session->getIP()) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn,$usr_id) . "';");
                    if (isset($_GET['r'])) {
                      header('location: ' . $_GET['r']);
                    } else {
                      header('location: /dashboard');
                    }
                    // Stop execution after successful login
                  }
                } else {
                  header('location: /auth/login?e='.$lang['login_invalid_password']);
                  exit; // Stop execution if password is invalid
                }
              } else {
                header('location: /auth/login?e='.$lang['login_invalid_email']);
                exit; // Stop execution if email is invalid
              }
            } else {
              header('location: /auth/login?e='.$lang['login_failed']);
              exit; // Stop execution if login fails
            }
            mysqli_free_result($result);
            $conn->close();
            exit;
          } else {
            header("location: /auth/login?e=".$lang['captcha_failed']);
            die();
          }
        }
      } else {
        header('location: /auth/login?e='.$lang['login_failed']);
        exit; // Stop execution if login button is not pressed
      }
    } else {
      // CSRF validation failed
      setcookie('api_key', '', time() - (10 * 365 * 24 * 60 * 60 * 2), '/');
      setcookie('phpsessid', '', time() - (10 * 365 * 24 * 60 * 60 * 2), '/');
      header('location: /auth/login?e='.$lang['csrf_failed']);
      exit;
    }
  }
} catch (Exception $e) {
  header("location: /auth/login?e=". $lang['login_error_unknown']."<br><code>".$e->getMessage()."</code>");
  die();
}
?>
<html lang="en" class="dark-style customizer-hide" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="horizontal-menu-template">

<head>
  <?php include(__DIR__ . '/../requirements/head.php'); ?>
  <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-auth.css" />
  <title>
    <?= SettingsManager::getSetting("name") ?> - <?= $lang['login'] ?>
  </title>
</head>
<body>
  <div id="preloader" class="discord-preloader">
    <div class="spinner"></div>
  </div>

  <div class="authentication-wrapper authentication-cover ">
    <div class="authentication-inner row">
      <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4 center">
        <div class="w-px-400 mx-auto">
          <h3 class="mb-1 fw-bold text-center"><?= $lang['welcome_to'] ?>
            <?= SettingsManager::getSetting("name") ?>!
          </h3>
          <p class="mb-4 text-center"><?= $lang['please_login'] ?></p>
          <?php
          if (isset($_GET['e'])) {
            ?>
            <div class="text-center alert alert-danger" role="alert">
              <?= htmlspecialchars($_GET['e']) ?>
            </div>
            <?php
          } else {

          }
          ?>
          <form method="POST">
            <div class="mb-3">
              <label for="email" class="form-label"><?= $lang['email'] ?> <?= $lang['or']?> <?= $lang['username']?></label>
              <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email"
                autofocus />
            </div>
            <div class="mb-3 form-password-toggle">
              <?php if (SettingsManager::getSetting("enable_smtp") == "true") {
                ?>
                <div class="d-flex justify-content-between">
                  <label class="form-label" for="password"><?= $lang['password']?></label>
                  <a href="/auth/forgot-password">
                    <small><?= $lang['forgot_password'] ?></small>
                  </a>
                </div>
                <?php
              } ?>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
              </div>
            </div>     
            <?php
            if (SettingsManager::getSetting("enable_turnstile") == "true") {
              ?>
              <center>
                <div class="cf-turnstile" data-sitekey="<?= SettingsManager::getSetting("turnstile_sitekey") ?>"></div>
              </center>
              &nbsp;
              <?php
            }
            ?>
            <?= $csrf->input('login-form'); ?>
            <button type="submit" name="login" class="btn btn-primary d-grid w-100"><?= $lang['login']?></button>

          </form>
          <p class="text-center">
            <span><?= $lang['new_to_platform'] ?></span>
            <a href="/auth/register">
              <span><?= $lang['register'] ?></span>
            </a>
          </p>
          <div class="auth-footer-btn d-flex justify-content-center">
            <?php
            if (SettingsManager::getSetting("enable_discord_link") == "true") {
              ?>
              <a href="/auth/discord" target="_self" class="btn btn-primary">
                <img width="18px" height="18px" src="/assets/img/discord-mark-white.svg" alt="Discord Logo">
              </a>
              <?php
            }
            ?>

          </div>
          <br>
          </p>
        </div>
      </div>
    </div>
  </div>
  <?php include(__DIR__ . '/../requirements/footer.php'); ?>
  <script src="<?= $appURL ?>/assets/js/pages-auth.js"></script>
</body>

</html>