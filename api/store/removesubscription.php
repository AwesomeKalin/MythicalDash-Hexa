<?php

use MythicalDash\SettingsManager;

include(__DIR__ . "/../base.php");
include(__DIR__ . '/../requirements/page.php');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(400);
    die();
}

function verifyWebhook($body, $headers)
{
    $paypalSignature = isset($headers['PAYPAL-AUTH-ALGO']) ? $headers['PAYPAL-AUTH-ALGO'] : null;
    $paypalCertUrl = isset($headers['PAYPAL-CERT-URL']) ? $headers['PAYPAL-CERT-URL'] : null;
    $paypalTransmissionId = isset($headers['PAYPAL-TRANSMISSION-ID']) ? $headers['PAYPAL-TRANSMISSION-ID'] : null;
    $paypalTransmissionSig = isset($headers['PAYPAL-TRANSMISSION-SIG']) ? $headers['PAYPAL-TRANSMISSION-SIG'] : null;
    $paypalTransmissionTime = isset($headers['PAYPAL-TRANSMISSION-TIME']) ? $headers['PAYPAL-TRANSMISSION-TIME'] : null;

    // Your webhook ID (you get this from PayPal when setting up the webhook)
    $webhookId = '2PL96854NU357820L';

    // Make a request to PayPal to verify the signature
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api-m.paypal.com/v1/notifications/verify-webhook-signature");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . getAccessToken() // Get your access token
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'auth_algo' => $paypalSignature,
        'cert_url' => $paypalCertUrl,
        'transmission_id' => $paypalTransmissionId,
        'transmission_sig' => $paypalTransmissionSig,
        'transmission_time' => $paypalTransmissionTime,
        'webhook_id' => $webhookId,
        'webhook_event' => json_decode($body)
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $verificationResult = json_decode($response, true);
    return isset($verificationResult['verification_status']) && $verificationResult['verification_status'] === 'SUCCESS';
}

// Function to get an access token
function getAccessToken()
{
    $clientId = SettingsManager::getSetting('paypal_client_id');
    $secret = SettingsManager::getSetting('paypal_secret_key');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api-m.paypal.com/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic " . base64_encode("$clientId:$secret"),
        "Content-Type: application/x-www-form-urlencoded"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

    $response = curl_exec($ch);
    curl_close($ch);

    $accessToken = json_decode($response, true);
    return $accessToken['access_token'];
}

// Main webhook processing
$headers = getallheaders();
$body = file_get_contents('php://input');
$event = json_decode($body, true);

if (verifyWebhook($body, $headers)) {
    // Handle the webhook event
    $payment_id = mysqli_real_escape_string($conn, $event["resource"]["id"]);
    $result = $conn->query("SELECT * FROM `mythicaldash_payments` WHERE `code` = '" . mysqli_real_escape_string($conn, $payment_id) . "'");
    $row = $result->fetch_assoc();
    $user = $row["ownerkey"];
    
    if ($row["coins"] == '1') {
        $usr_cpu = $session->getUserInfoWithoutCookie("cpu", $user);
        $usr_ram = $session->getUserInfoWithoutCookie("ram", $user);
        $usr_disk = $session->getUserInfoWithoutCookie("disk", $user);
        $usr_svlimit = $session->getUserInfoWithoutCookie("server_limit", $user);
        $newcpu = $usr_cpu - "300";
        $newram = $usr_ram - "4096";
        $newdisk = $usr_disk - "20480";
        $newsvlimit = $usr_svlimit - "2";
        $conn->query("UPDATE `mythicaldash_users` SET `cpu` = '" . mysqli_real_escape_string($conn, $newcpu) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $user) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `ram` = '" . mysqli_real_escape_string($conn, $newram) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $user) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `disk` = '" . mysqli_real_escape_string($conn, $newdisk) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $user) . "';");
        $conn->query("UPDATE `mythicaldash_users` SET `server_limit` = '" . mysqli_real_escape_string($conn, $newsv) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $user) . "';");
        $conn->close();
    }

    http_response_code(200);
    echo 'Webhook processed';
} else {
    // Invalid webhook
    http_response_code(400);
    echo 'Invalid Webhook Signature';
}