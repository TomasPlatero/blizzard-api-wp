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

    /**
     * Save an image locally from a URL and add it to the WordPress media library.
     *
     * @param string $image_url The URL of the image to download.
     * @param string $image_name The name for the image.
     * @return int|false The attachment ID of the saved image, or false on failure.
     */
    public static function save_image_locally($image_url, $image_name) {
        $response = wp_remote_get($image_url, array('timeout' => 10));

        if (is_wp_error($response)) {
            return false;
        }

        $image_data = wp_remote_retrieve_body($response);
        if (empty($image_data)) {
            return false;
        }

        // Set the filename for the image.
        $filename = sanitize_file_name($image_name) . '.jpg';
        $upload_dir = wp_upload_dir();

        // Determine the file path.
        $file_path = $upload_dir['path'] . '/' . $filename;

        // Save the image to the local filesystem.
        file_put_contents($file_path, $image_data);

        // Prepare the file for WordPress media library.
        $file_type = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        // Insert the attachment into the WordPress media library.
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        return $attachment_id;
    }    
}