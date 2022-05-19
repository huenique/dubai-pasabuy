<?php

// webook (http callback endpoint) for ayden payments
// docs: https://docs.adyen.com/development-resources/webhooks
require_once __DIR__ . "/../vendor/adyen/php-api-library/src/Adyen/Util/HmacSignature.php";
require_once __DIR__ . "/../db/connection.php";

function verify_hmac(string|false $jsonRequest){
    // HMAC_KEY from the Customer Area
    $hmacKey = getenv("AYDEN_HMAC_KEY");

    // Notification Request JSON
    $notificationRequest = json_decode($jsonRequest, true);
    $hmac = new \Adyen\Util\HmacSignature();

    if ($notificationRequest) {
        // Handling multiple notificationRequests
        foreach ( $notificationRequest["notificationItems"] as $notificationRequestItem ) {
            $params = $notificationRequestItem["NotificationRequestItem"];

            // Handle the notification
            if ( $hmac->isValidNotificationHMAC($hmacKey, $params) ) {
                // Process the notification based on the eventCode
                print_r($params);
            } else {
                echo "INVALID";
                // Non valid NotificationRequest
            }
        }
    }
}

verify_hmac(file_get_contents("php://input"));
