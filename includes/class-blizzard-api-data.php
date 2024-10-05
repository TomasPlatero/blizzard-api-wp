<?php

/**
 * Handles the interaction with the Blizzard API.
 *
 * @link       https://artictempest.es
 * @since      1.0.0
 *
 * @package    Blizzard_Api
 * @subpackage Blizzard_Api/includes
 */

class Blizzard_Api_Data {

    /**
     * Retrieve Authorization from Blizzard.
     *
     * @since     1.0.0
     * @return    string|null    The access token or null on error.
     */
    public static function get_blizzard_token() {
        $curl = curl_init();
        $url = "https://oauth.battle.net/token";

        // Client credentials
        $client_id = get_option('blizzard_api_client_id');
        $client_secret = get_option('blizzard_api_client_secret');

        // Basic authentication
        $authorization = base64_encode($client_id . ':' . $client_secret);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . $authorization,
                'Content-Type: application/x-www-form-urlencoded'
            ),
            CURLOPT_POSTFIELDS => http_build_query(array(
                'grant_type' => 'client_credentials'
            )),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            error_log("Error retrieving token: " . $err);
            return null;
        } else {
            $data = json_decode($response, true);
            return isset($data['access_token']) ? $data['access_token'] : null;
        }
    }
}