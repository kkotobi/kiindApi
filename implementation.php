<?php

/**
 * License BSD
 *
 * This is the implementation file for the Kiind.me API,
 * Please go to http://info.kiind.me/documentation to find their documentation
 *
 * Right now this is all setup to use the amazon giftcard gift
 *
 *
 * It requires the following files to be present:
 * config/config.php
 * lib/Curl.php          (required inside KiindApi.php)
 * lib/KiindApi.php
 *
 * Written by: Kamran Kotobi  <kotobigit@gmail.com>
 *
 */

require_once('config/config.php'); // Brings back the $config array
require_once('lib/KiindApi.php');



/****************************************************************
 * EXAMPLE REQUESTS AS YOU WOULD MAKE THEM IN YOUR REGULAR CODE.
 ****************************************************************/


// Credential Check request example
$kiind = new KiindApi();
$kiind->checkCredentials($config);
exit();



// Sending a gift request example
$contacts = [
    [
        'firstname' => 'John',
        'email'     => 'john.doe@gmail.com'
    ]

];

$gifts = [
    [
        'id'             => $config['KIIND_GIFT_ID'],
        'price_in_cents' => 10000 //remember this is in cents.
    ]

];

$subject = "We value your business";
$message = "Thank you for being a loyal customer.";

$kiind = new KiindApi();
$kiind->sendGift($config, $contacts, $gifts, $message, $subject);
exit();


