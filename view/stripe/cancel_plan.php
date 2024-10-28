<?php

use MythicalDash\SettingsManager;

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

include(__DIR__ . '/../requirements/page.php');
if (SettingsManager::getSetting("enable_stripe") == "false") {
    header('location: /');
}

try {
    $plantocancel = $conn->query("SELECT * FROM `mythicaldash_payments` WHERE `coins` = 1 LIMIT 1");
    if (!$result->num_rows > 0) {
        http_response_code(404);
        die();
    }
    $planrow = $result->fetch_assoc();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api-m.paypal.com/v1/billing/subscriptions/" . $row["code"] . "/cancel");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . getAccessToken() // Get your access token
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "reason" => "Cancelling through client area",
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $conn->query("DELETE FROM `mythicaldash_payments` WHERE `coins` = 1 LIMIT 1");
    header('Location: /dashboard?s=Succesfully%20cancelled%20your%20subscription!');
    die();
} catch (Exception $e) {
    header('Location: /dashboard?s=Failed%20to%20cancel,%20please%20try%20again!');
    die();
}