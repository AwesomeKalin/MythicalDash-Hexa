<?php
use MythicalDash\Encryption;
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;
use MythicalDash\PayPal\Payment;

$csrf = new MythicalSystems\Utils\CSRFHandler;

include(__DIR__ . '/../requirements/page.php');
if (SettingsManager::getSetting("allow_payments") == "false") {
    header('location: /');
    die();
}
function convertToCents($amount) {
    return round($amount * 100);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['paystripe'])) {
        $mypaymentkey = Encryption::generate_keynoinfo();
        $conn->query("INSERT INTO `mythicaldash_payments` (`code`, `coins`, `ownerkey`, `getaway`, `status`) VALUES ('" . mysqli_real_escape_string($conn, $mypaymentkey) . "', '" . mysqli_real_escape_string($conn, $_GET['coins']) . "', '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "', 'stripe', 'pending');");
        try {
            //stripe_secret_key
            \Stripe\Stripe::setApiKey(SettingsManager::getSetting('stripe_secret_key'));
            $checkout_session = \Stripe\Checkout\Session::create([
                "mode" => "payment",
                'customer_email' => $session->getUserInfo('email'),
                "success_url" => "" . $appURL . "/store/get/stripe/coins?code=" . $mypaymentkey,
                "cancel_url" => "" . $appURL . "/user/payments?e=" . $lang['payment_request_cancel'],
                "line_items" => [
                    [
                        "quantity" => 1,
                        "price_data" => [
                            "currency" => strtolower(SettingsManager::getSetting('payments_currency')),
                            "unit_amount" => intval(convertToCents(SettingsManager::getSetting('coin_per_balance'))) * intval($_GET['coins']),
                            "product_data" => [
                                "images" => [
                                    $appURL . "/assets/img/illustrations/page-pricing-standard.png"
                                ],
                                "name" => "Payment of " . $_GET['coins'] . " coins on " . SettingsManager::getSetting("name"),
                                "description" => "Upon successful payment, you'll be acquiring " . $_GET['coins'] . " on " . SettingsManager::getSetting("name") . "! Rest assured, our system is fully secure and powered by Stripe for end-to-end protection."
                            ]
                        ]
                    ]
                ]
            ]);
            http_response_code(303);
            header("location: " . $checkout_session->url);
            die();
        } catch (Exception $e) {
            header("location: /user/payments?e=Stripe Error" . $e);
            ErrorHandler::Error("Stripe Error ", $e);
            die();
        }

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
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> -
        <?= $lang['buy_coins'] ?>
    </title>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
    <style>
        .custom-paypal-button {
            background-color: #161931 !important;
            background: #161931 !important;
        }
    </style>
</head>

<body>
    <?php
    if (SettingsManager::getSetting("show_snow") == "true") {
        include(__DIR__ . '/../components/snow.php');
    }
    ?>

    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">
                                <?= $lang['store'] ?> /
                            </span>
                            <?= $lang['coins'] ?>
                        </h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
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
                        <div class="row">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-header text-center">
                                        <div class="card-title">
                                            Buy Standard Plan
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <form method='GET'>
                                            <?= $csrf->input('pay-form'); ?>
                                            <?php
                                            if (SettingsManager::getSetting("enable_stripe") == "true") {
                                                ?>
                                                <button name="paystripe" type="submit" class="btn btn-primary">
                                                    <?= $lang['store_buy'] ?>
                                                    <?= $lang['coins'] ?> (Stripe)
                                                </button>
                                                <?php
                                            }
                                            ?>
                                            <?php
                                            if (SettingsManager::getSetting('enable_paypal') == 'true') {
                                                ?><br>
                                                <center>
                                                    <div id="paypal-button-container" class="mt-3"></div>
                                                </center>

                                            <?php } ?>

                                        </form>


                                    </div>
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
                <?php include(__DIR__ . '/../components/footer.php') ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php') ?>

    <script src="<?= $appURL ?>/assets/js/user/paymentss-ecommerce.js"></script>
    <script
        src="https://www.paypal.com/sdk/js?client-id=<?= SettingsManager::getSetting('paypal_client_id') ?>&currency=<?= strtoupper(SettingsManager::getSetting('payments_currency')) ?>&vault=true&intent=subscription"></script>
    <script>
        var key = "";
        paypal.Buttons(
            createSubscription: (data, actions) => {
                return actions.subscription.create({
                    plan_id: 'P-70T8690908543601EM4P3W2I'
                });
            },

            onApprove: (data, actions) => {
                return actions.order.capture().then(function (orderData) {
                    //console.log('Payment', orderData, JSON.stringify(orderData, null, 2));
                    const transaction = orderData.purchase_units[0].payments.captures[0];
                    console.warn('Capture result', orderData, JSON.stringify(orderData, null, 2));
                    var data = {
                        'plan': 'standard',
                        'status': transaction.status,
                        'payment_id': transaction.id,
                        'date': '<?= date('Y-m-d H:i') ?>',
                        'key': key,
                    };

                    $.ajax({
                        method: "POST",
                        url: "/store/get/stripe/standard?paypal=run",
                        data: data,
                        success: function (response) {
                            window.location.href = "/dashboard?s=Thanks for buying from <?= SettingsManager::getSetting('name') ?>";
                        }
                    });
                });
            },
            style: {
                color: 'black',
                shape: 'pill',
                disableMaxWidth: true,
            },
            onInit: function (data, actions) {
                document.querySelector('#paypal-button-container').classList.add('custom-paypal-button');
            }
        }).render('#paypal-button-container')
    </script>


</body>