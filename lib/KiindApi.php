<?php

/**
 * KiindApi V1.2.1  8/15/2014
 * Wrapper class for the Kiind.me API
 * License BSD
 * Written by: Kamran Kotobi  <kotobigit@gmail.com>
 *
 * Important fact as of this version we can either send:
 * 1 email with 1 gift
 * 1 email with multiple gifts
 * multiple emails with same gift for everyone one or many
 *
 * It does NOT support multiple emails with different gifts for each email.
 *
 * It requires the following files to be present:
 * config/config.php
 * lib/Curl.php          (required here)
 * lib/KiindApi.php
 *
 * Lit of endpoints
 *
 * /marketplace
 * /marketplace/{id}
 * /marketplace/regions
 * /marketplace/vendors
 * /marketplace/categories
 * /campaign
 * /campaign/{uuid}
 * /campaign/{id}
 *
 *
 * Response codes
 *
 * 200 OK ­ Everything looks good
 * 400 Bad Request ­ The request cannot be fulfilled due to bad syntax
 * 401 Unauthorized – Login credentials are not valid.
 * 402 Request Failed – Parameters were valid but request failed.
 * 403 Forbidden – Your credentials are not authorized to access this method
 * 405 Method not allowed – The method used is not allowed for this resource.
 * 500 Internal Server Error – Something on Kiind’s servers has gone wrong.  Contact customer support if you continue to receive this error.
 * 503 Service Unavailable ­  Planned maintenance or a known temporary condition resulting in downtime.
 *
 * Please go to http://info.kiind.me/documentation to find their documentation
 *
 */

require_once('lib/Curl.php');

Class KiindApi
{
    use Curl;

    /**
     *
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

        // This is here to make each request unique.
        // I highly recommend changing this to the customer's email + date for example.
        $usec    = microtime();
        $replace = [" ", "."];
        $usec    = str_replace($replace, '', $usec);
        $now     = date('Y-m-d H:i:s', strtotime('now'));
        $unique  = $now . '-' . mb_substr($usec, 0, 4);


        // Now we build the data to be sent via curl to Kiind.me
        $subject                                  = $subject ? : 'You have received a giftcard!';
        $message                                  = $message ? : 'Please enjoy your giftcard!';
        $postCurlDataToKiind                      = [];
        $postCurlDataToKiind['subject']           = $subject;
        $postCurlDataToKiind['message']           = $message;
        $postCurlDataToKiind['contacts']          = $contacts; // It handles a collection of contacts or just the one.
        $postCurlDataToKiind['marketplace_gifts'] = $gifts;
        $postCurlDataToKiind['expiry']            = date('Y-m-d', strtotime('now + 3 month')); // giftcard's expiration date
        $postCurlDataToKiind['id']                = 'uniquevalue' . $unique; // has to be a unique value each request!
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
     * @desc  Takes in the config array and hits the kiind.me api to return your account status.
     * @param $config - array
     * @return bool|mixed
     */
    public function checkCredentials($config)
    {

        // Here we send the request to kiind to get our credentials.
        $resp = Curl::get($config['KIIIND_CREDENTIALS'], $config['KIIND_AUTH_TOKEN']);

        if ($resp) {
            print_r($resp);

            return true;
        }

        return false;

    }

    /**
     *
     * @desc  Takes an array of arrays and turns them into an array with objects inside of it with key=> val pairs
     *        It is used for the creation fo the 'contacts' or 'marketplace_gifts' array for the Kiind JSON
     * @param $data - array of arrays
     *
     * $data has look something like this (multiple items)
     *
     * $data = [
     *   [
     *      'firstname' => 'joe',
     *      'email' => 'joe@aol.com'
     *   ],
     *   [
     *      'firstname' => 'joe2',
     *      'email' => 'joe2@aol.com'
     *   ]
     *
     * ];
     *
     * Or just 1 item
     *
     * $contacts = [
     *  [
     *      'firstname' => 'joe',
     *      'email' => 'joe@aol.com'
     *  ]
     *
     *
     * @return mixed|string
     */
    public function makeContactsOrGiftsArray($data)
    {
        if (empty($data)) {
            return '';
        }

        $contacts = json_encode($data);
        $contacts = json_decode($contacts);

        if (json_last_error() == JSON_ERROR_NONE) {
            return $contacts;
        }

        return '';
    }

    /**
     *
     * @desc   Takes in an array with all the information to create a json object that Kiind.me likes
     * @param  $data
     * @return string - that looks like a json object
     *
     * Expected JSON structure to look like this:  (for 1 gift).
     * For multiple just add another object inside "marketplace_gifts" but everyone will get the same amount of gifts.
     *
     *
     * {
     * "subject":"test #2",
     * "message":"i hope you like this",
     * "contacts":[
     * {
     * "firstname":"kamran",
     * "email":"testEmail@gmail.com"
     * }
     * ],
     * "marketplace_gifts":[
     * {
     * "id":1,
     * "price_in_cents":10000
     * }
     * ],
     * "expiry":"2015-01-01",
     * "id":"giftFrom2015-01-01-1"
     * }
     *
     */
    public function makeKiindCurlData($data)
    {
        $json = '';

        if (empty($data)) {
            return $json;
        }

        // Here we grab these 2 keys and turn them into objects to satisfy the expected JSON by Kiind
        $data['contacts']          = $this->makeContactsOrGiftsArray($data['contacts']);
        $data['marketplace_gifts'] = $this->makeContactsOrGiftsArray($data['marketplace_gifts']);

        if (!$data['contacts'] || !$data['marketplace_gifts']) {
            return $json;
        }

        $json = json_encode($data);

        return $json;

    }

    /**
     * @desc  Takes in the $postResult object from the Kiind.me API and it looks for the status code. All their responses
     *        always have a status code, the only time the request is good is when we get a status 200 back.
     * @param $postResult - object
     * @param $verbose - bool
     * @param $postResult - object
     * @param bool $verbose
     * @return bool
     */
    public function parseResponseFromKiind($postResult, $verbose = false)
    {
        if (!$postResult) {
            return false;
        }

        if ($verbose) {
            print_r($postResult);
        }

        if ($postResult->status == 200) {
            return true;

        }

        return false;
    }

}