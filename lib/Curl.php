<?php

/**
 * Class Curl
 *
 * License BSD
 *
 * Implements the basic get, post as well as some helper methods.
 * Written by: Kamran Kotobi  <kotobigit@gmail.com>
 *
 */
trait Curl
{

    public static function get($url, $token = null)
    {

        if (!function_exists('curl_init') || empty($url)) {
            return false;
        }

        $headerParams = [];

        // If we passed in a bearer then we added to the params passed in header.
        if (!empty($token)) {
            array_push($headerParams, 'Authorization: ' . $token);
        }

        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_USERPWD, $token);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // in the event the url has a redirect it will follow it.
        curl_setopt($ch, CURLOPT_HEADER, 0); // to include the header/not in the result.match
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // if it is set to true, data is returned as string instead of outputting it.
        curl_setopt($ch, CURLOPT_TIMEOUT, 4); //timeout set for '4' seconds

        // Fetch and return content, save it.
        $raw_data = curl_exec($ch);
        curl_close($ch);

        return static::returnCurlData($raw_data);

    }

    /**
     * @desc   It takes in the url via string and
     * @param  $url - url to hit via cURL
     * @param  $curlData - the data that will be passed via cURL, it needs to be processed before hand.
     * @param  $bearer - string is the auth token if needed, this param is optional
     * @return bool|mixed
     */

    public static function post($url, $curlData, $bearer = null)
    {

        if (!static::doesCurlAndDataExists($url, $curlData)) {
            return false;
        }

        $headerParams = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($curlData)
        ];

        // If we passed in a bearer then we added to the params passed in header.
        if (!empty($bearer)) {
            array_push($headerParams, 'Authorization: ' . $bearer);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8); //timeout set for '8' seconds
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerParams);

        $raw_data = curl_exec($ch);
        curl_close($ch);

        return static::returnCurlData($raw_data);

    }


    /**
     * @desc  Helper method that checks to see if cURL has been installed in our system
     *        and checks to see if we are passing values and an url to it.
     * @param $url
     * @param $curlData
     * @return bool
     */
    public static function doesCurlAndDataExists($url, $curlData)
    {
        $result = true;

        // is cURL installed yet?
        if (!function_exists('curl_init') || empty($curlData) || empty($url)) {
            $result = false;
        }

        return $result;
    }

    /**
     * @desc   Helper method that takes in the data coming back from the cURL request and if it's JSON de we decode it.
     * @param  $raw_data
     * @return mixed
     */
    public static function returnCurlData($raw_data)
    {
        $data = $raw_data;

        // If the  return from the API is JSON, we decode it!
        json_decode($raw_data);

        if (json_last_error() == JSON_ERROR_NONE) {
            $data = json_decode($raw_data);
        }

        return $data;
    }


}