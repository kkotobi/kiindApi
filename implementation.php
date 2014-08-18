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
 * lib/Curl.php
 * lib/KiindApi.php
 *
 * Written by: Kamran Kotobi  <kotobigit@gmail.com>
 *
 */

require_once('config/config.php'); // Brings back the $config array
require_once('lib/KiindApi.php');
require_once('lib/Curl.php');


class KiindImplementation
{

    use KiindApi, Curl;

    /**
     * @desc  It only handles multiple contacts with the same gift for all.
     * @param $config - array contains the default info to connect to kiind.me
     * @param $contacts - array of contacts with first name and email
     * @param $gifts - array with the id of the gift and it's price in cents
     * @param null $message - string message of the email if any.
     * @param null $subject - string for the subject of the email.
     * @return bool
     */

    public function sendGift($config, $contacts, $gifts, $message = null, $subject = null)
    {
        if (empty($config) || empty($contacts) || empty($gifts)) {
            return false;
        }

        // These micro second calculation is her to make each request unique.
        // I recommend changing this to the customer's email + date for example.
        $usec = microtime();
        $usec = str_replace(" ", ".", $usec);

        // Now we build the data to be sent via curl to Kiind.me
        $subject                                  = $subject ? : 'You have received a giftcard!';
        $message                                  = $message ? : 'Please enjoy your giftcard!';
        $postCurlDataToKiind                      = [];
        $postCurlDataToKiind['subject']           = $subject;
        $postCurlDataToKiind['message']           = $message;
        $postCurlDataToKiind['contacts']          = $contacts; // It handles a collection of contacts or just the one.
        $postCurlDataToKiind['marketplace_gifts'] = $gifts;
        $postCurlDataToKiind['expiry']            = date('Y-m-d', strtotime('now + 3 month')); // giftcard's expiration date
        $postCurlDataToKiind['id']                = 'uniquevalue' . $usec; // has to be a unique value each request!
        // END of the building the data to send to Kiind.

        $kiindJSON = null;

        // Here we build the JSON object just how kiind likes it.
        $kiindJSON = $this->makeKiindCurlData($postCurlDataToKiind);

        // If true then no JSON was built in the makeKiindCurlData method.
        if (!$kiindJSON) {
            return false;
        }

        $postResult = Curl::post($config['KIIND_URL'], $kiindJSON, $config['KIIND_AUTH_TOKEN']);

        // If we want to see the DETAILED message coming back from Kiind.me please uncomment the line below.
        //print_r($postResult);

        if (!$postResult) {
            return false;
        }

        // Ok.. it returned with something Let's parse the response!!
        $responseFromKiind = $this->parseResponseFromKiind($postResult);

        // This means it came back with something other than a 200 ok as status from the API
        if (!$responseFromKiind) {
            return false;
        }

        return true;

    }

    /**
     * @param $config
     * @param bool $verbose
     * @return bool|mixed
     */
    public function checkCredentials($config, $verbose = false)
    {
        $resp = Curl::get($config['KIIIND_CREDENTIALS'], $config['KIIND_AUTH_TOKEN']);

        if ($verbose) {
            print_r($resp);
        }

        return $resp;

    }


}


/*********************************************************************************************
 * HERE WE SETUP THE VARIABLES AND MAKE THE REQUESTS HAPPEN AS YOU WOULD IN YOUR REGULAR CODE.
 *********************************************************************************************/

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

$kiind = new KiindImplementation();
$resp  = $kiind->sendGift($config, $contacts, $gifts, $message, $subject);

echo $resp ? 'success ' : 'fail';

exit();


