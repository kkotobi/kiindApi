<?php

/*
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

trait KiindApi
{

    /**
     *
     * License BSD
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
     * @return bool
     */
    public function parseResponseFromKiind($postResult, $verbose = false)
    {
        $result = true;

        if($verbose){
            print_r($postResult);
        }

        // To see the FULL message: print_r($postResult->error->message); or print_r($postResult);
        if ($postResult->status !== 200) {
            $result = false;

        }

        return $result;
    }

}