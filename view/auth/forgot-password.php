<?php
use MythicalDash\Encryption;
use MythicalDash\ErrorHandler;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use MythicalDash\SettingsManager;
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;

try {
    $conn = new Connect();
    $conn = $conn->connectToDatabase();
    $session = new SessionManager();
    if (SettingsManager::getSetting("enable_smtp") == "false") {
        header('location: /auth/login?e='.$lang['login_mail_server_not_setup']);
        die();
    }
    session_start();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['reset_password'])) {
                if (SettingsManager::getSetting("enable_smtp") == "true") {
                    $email = mysqli_real_escape_string($conn, $_POST['email']);
                    $check_query = "SELECT * FROM mythicaldash_users WHERE email = '$email'";
                    $result = mysqli_query($conn, $check_query);
                    if (!mysqli_num_rows($result) > 0) {
                        //GET USER INFO
                        $userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE email = '" . $email . "'")->fetch_array();
                        //GENERATE A CODE TO RESET THE PASSWORD
                        $skey = Encryption::generate_keynoinfo();
                        //EMAIL SERVER STUFF
                        $smtpHost = SettingsManager::getSetting("smtpHost");
                        $smtpPort = SettingsManager::getSetting("smtpPort");
                        $smtpSecure = SettingsManager::getSetting("smtpSecure");
                        $smtpUsername = SettingsManager::getSetting("smtpUsername");
                        $smtpPassword = SettingsManager::getSetting("smtpPassword");
                        $fromEmail = SettingsManager::getSetting("fromEmail");
                        $toEmail = $email;
                        $first_name = $userdb['first_name'];
                        $last_name = $userdb['last_name'];
                        $subject = SettingsManager::getSetting("name") . " password reset!";
                        $message = '
                    <!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <!--[if mso]>
    <xml><o:officedocumentsettings><o:pixelsperinch>96</o:pixelsperinch></o:officedocumentsettings></xml>
  <![endif]-->
    <title>Reset your Password</title>
    <link
        href="https://fonts.googleapis.com/css?family=Montserrat:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700"
        rel="stylesheet" media="screen">
    <style>
        .hover-underline:hover {
            text-decoration: underline !important;
        }

        @media (max-width: 600px) {
            .sm-w-full {
                width: 100% !important;
            }

            .sm-px-24 {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }

            .sm-py-32 {
                padding-top: 32px !important;
                padding-bottom: 32px !important;
            }
        }
    </style>
</head>

<body
    style="margin: 0; width: 100%; padding: 0; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #eceff1;">
    <div style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; display: none;">A request to reset
        password was received from your ' . SettingsManager::getSetting("name") . '</div>
    <div role="article" aria-roledescription="email" aria-label="Reset your Password" lang="en"
        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly;">
        <table style="width: 100%; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;" cellpadding="0"
            cellspacing="0" role="presentation">
            <tr>
                <td align="center"
                    style="mso-line-height-rule: exactly; background-color: #eceff1; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;">
                    <table class="sm-w-full" style="width: 600px;" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td class="sm-py-32 sm-px-24"
                                style="mso-line-height-rule: exactly; padding: 48px; text-align: center; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;">
                                <a href="' . $appURL . '"
                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly;">
                                    <img src="' . SettingsManager::getSetting("logo") . '" width="155" alt="' . SettingsManager::getSetting("name") . '"
                                        style="max-width: 100%; vertical-align: middle; line-height: 100%; border: 0;">
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" class="sm-px-24"
                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly;">
                                <table style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        <td class="sm-px-24"
                                            style="mso-line-height-rule: exactly; border-radius: 4px; background-color: #ffffff; padding: 48px; text-align: left; font-family: Montserrat, -apple-system, Segoe UI, sans-serif; font-size: 16px; line-height: 24px; color: #626262;">
                                            <p
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin-bottom: 0; font-size: 20px; font-weight: 600;">
                                                Hey</p>
                                            <p
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin-top: 0; font-size: 24px; font-weight: 700; color: #ff5850;">
                                                ' . $first_name . ' ' . $last_name . '!</p>
                                            <p
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 24px;">
                                                A request to reset password was received from your
                                                <span style="font-weight: 600;">' . SettingsManager::getSetting("name") . '</span> Account -
                                                <a href="mailto:' . $email . '" class="hover-underline"
                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; color: #7367f0; text-decoration: none;">' . $email . '</a>
                                                 from the IP - <span
                                                    style="font-weight: 600;">' . $session->getIP() . '</span> .
                                            </p>
                                            <p
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 24px;">
                                                Use this link to reset your password and login.</p>
                                            <a href="' . $appURL . '/auth/reset-password?code=' . $skey . '"
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin-bottom: 24px; display: block; font-size: 16px; line-height: 100%; color: #7367f0; text-decoration: none;">' . $appURL . '/auth/reset-password?code=' . $skey . '</a>
                                            <table cellpadding="0" cellspacing="0" role="presentation">
                                                <tr>
                                                    <td
                                                        style="mso-line-height-rule: exactly; mso-padding-alt: 16px 24px; border-radius: 4px; background-color: #7367f0; font-family: Montserrat, -apple-system, Segoe UI, sans-serif;">
                                                        <a href="' . $appURL . '/auth/reset-password?code=' . $skey . '"
                                                            style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; display: block; padding-left: 24px; padding-right: 24px; padding-top: 16px; padding-bottom: 16px; font-size: 16px; font-weight: 600; line-height: 100%; color: #ffffff; text-decoration: none;">Reset
                                                            Password &rarr;</a>
                                                    </td>
                                                </tr>
                                            </table>
                                            <p
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-top: 24px; margin-bottom: 24px;">
                                                <span style="font-weight: 600;">Note:</span> This link is valid for 1
                                                hour from the time it was
                                                sent to you and can be used to change your password only once.
                                            </p>
                                            <p
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0;">
                                                If you did not intend to deactivate your account or need our help
                                                keeping the account, please
                                                contact us at
                                                <a href="mailto:' . SettingsManager::getSetting("fromEmail") . '" class="hover-underline"
                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; color: #7367f0; text-decoration: none;">' . SettingsManager::getSetting("fromEmail") . '</a>
                                            </p>
                                            <table style="width: 100%;" cellpadding="0" cellspacing="0"
                                                role="presentation">
                                                <tr>
                                                    <td
                                                        style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; padding-top: 32px; padding-bottom: 32px;">
                                                        <div
                                                            style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; height: 1px; background-color: #eceff1; line-height: 1px;">
                                                            &zwnj;</div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <p
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 16px;">
                                                Not sure why you received this email? Please
                                                <a href="mailto:' . SettingsManager::getSetting("fromEmail") . '" class="hover-underline"
                                                    style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; color: #7367f0; text-decoration: none;">let
                                                    us know</a>.
                                            </p>
                                            <p
                                                style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; margin: 0; margin-bottom: 16px;">
                                                Thanks, <br>The ' . SettingsManager::getSetting("name") . ' Team</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; height: 20px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="font-family: Montserrat, sans-serif; mso-line-height-rule: exactly; height: 16px;">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
                    ';
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = $smtpHost;
                        $mail->Port = $smtpPort;
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtpUsername;
                        $mail->Password = $smtpPassword;
                        $mail->SMTPSecure = $smtpSecure;
                        $mail->setFrom($fromEmail);
                        $mail->addAddress($toEmail);
                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body = $message;
                        try {
                            $mail->send();
                            $conn->query("INSERT INTO `mythicaldash_resetpasswords` (`email`, `ownerkey`, `resetkeycode`, `ip_addres`) VALUES ('" . mysqli_real_escape_string($conn, $email) . "', '" . mysqli_real_escape_string($conn, $userdb['api_key']) . "', '" . mysqli_real_escape_string($conn, $skey) . "', '" . mysqli_real_escape_string($conn, $session->getIP()) . "');");
                            $domain = substr(strrchr($email, "@"), 1);
                            $redirections = array('gmail.com' => 'https://mail.google.com', 'yahoo.com' => 'https://mail.yahoo.com', 'hotmail.com' => 'https://outlook.live.com', 'outlook.com' => "https://outlook.live.com", 'gmx.net' => "https://gmx.net", 'icloud.com' => "https://www.icloud.com/mail", 'me.com' => "https://www.icloud.com/mail", 'mac.com' => "https://www.icloud.com/mail", );
                            if (isset($redirections[$domain])) {
                                header("location: " . $redirections[$domain]);
                                exit;
                            } else {
                                header("location: /auth/login");
                                exit;
                            }
                        } catch (Exception $e) {
                            ErrorHandler::Critical("Failed to update settings ", $e);
                            $error_message = $lang['login_error_unknown'];
                            header("location: /auth/forgot-password?error=" . urlencode($error_message));
                            die();
                        }
                    }
                } else {
                    header('location: /auth/forgot-password?e='.$lang['login_mail_server_not_setup']);
                    die();
                }
        } else {
            header("location: /auth/forgot-password?e=".$lang['login_error_unknown']);
            die();
        }
    }
} catch (Exception $e) {
    header("location: /auth/forgot-password?e=".$lang['login_error_unknown']);
    ErrorHandler::Error("Forgot-Password ", $e);
    die();
}


?>
<!DOCTYPE html>
<html lang="en" class="dark-style customizer-hide" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="horizontal-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-auth.css" />
    <title>
        <?= SettingsManager::getSetting("name") ?> - <?= $lang['forgot_password']?>
    </title>
</head>

<body>
    <?php
    if (SettingsManager::getSetting("show_snow") == "true") {
        include(__DIR__ . '/../components/snow.php');
    }
    ?>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="authentication-wrapper authentication-cover ">
        <div class="authentication-inner row">
            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="<?= $appURL ?>/assets/img/illustrations/auth-forgot-password-illustration-light.png"
                        alt="auth-forgot-password-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-forgot-password-illustration-light.png"
                        data-app-dark-img="illustrations/auth-forgot-password-illustration-dark.png" />

                    <img src="<?= $appURL ?>/assets/img/illustrations/bg-shape-image-light.png"
                        alt="auth-forgot-password-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
                </div>
            </div>
            <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
                <div class="w-px-400 mx-auto">
                    <h3 class="mb-1 fw-bold"><?= $lang['forgot_password']?>? 🔒</h3>
                    <p class="mb-4"><?= $lang['forgot_password_info'] ?></p>
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
                    <?php
                    if (isset($_GET['s'])) {
                        ?>
                        <div class="text-center alert alert-success" role="alert">
                            <?= htmlspecialchars($_GET['s']) ?>
                        </div>
                        <?php
                    } else {

                    }
                    ?>
                    <form class="mb-3" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label"><?= $lang['email'] ?></label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Enter your email" autofocus />
                        </div>
                        <button name="reset_password" value="true" class="btn btn-primary d-grid w-100"><?= $lang['send'] ?></button>
                    </form>
                    <div class="text-center">
                        <a href="/auth/login" class="d-flex align-items-center justify-content-center">
                            <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                            <?= $lang['back_to_login']?>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php'); ?>
    <script src="<?= $appURL ?>/assets/js/pages-auth.js"></script>
</body>

</html>