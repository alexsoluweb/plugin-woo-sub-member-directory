<?php

namespace WSMD;

class WSMD_AJAX
{
    public function __construct()
    {
        add_action('wp_ajax_wsmd_woo_dashboard_save_user_settings', array($this, 'dashboard_save_user_settings'));
        add_action('wp_ajax_wsmd_member_directory_get_members', array($this, 'member_directory_get_members'));
        add_action('wp_ajax_nopriv_wsmd_member_directory_get_members', array($this, 'member_directory_get_members'));
    }

    /**
     * Save user settings
     */
    public function dashboard_save_user_settings()
    {
        // Check nonce
        if (!check_ajax_referer('wsmd_woo_dashboard_save_user_settings', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid nonce', 'wsmd')
            ));
        }

        // Check user logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('You must be logged in to save settings', 'wsmd')
            ));
        }

        // Get and sanitize address fields
        $address = sanitize_text_field($_POST['wsmd_address']);
        $city = sanitize_text_field($_POST['wsmd_city']);
        $state = sanitize_text_field($_POST['wsmd_province_state']);
        $country = sanitize_text_field($_POST['wsmd_country']);
        $postal_code = sanitize_text_field($_POST['wsmd_postal_zip_code']);

        // Check if all fields are filled
        if (empty($address) || empty($city) || empty($state) || empty($country) || empty($postal_code)) {
            wp_send_json_error(array(
                'message' => __('Please fill out all address fields.', 'wsmd')
            ));
        }

        // Geocode the address using Google Places API
        $full_address = "$address, $city, $state, $country, $postal_code";
        $geocode_result = $this->geocode_address($full_address);

        // Check if geocode was successful
        if (!$geocode_result['success']) {
            wp_send_json_error(array(
                'message' => $geocode_result['message']
            ));
        }

        // Extract address components from the API result
        $address_components = $geocode_result['address_components'];

        // Update address fields with validated data
        $_POST['wsmd_address'] = $address_components['street_address'] ?? $address;
        $_POST['wsmd_city'] = $address_components['locality'] ?? $city;
        $_POST['wsmd_province_state'] = $address_components['administrative_area_level_1'] ?? $state;
        $_POST['wsmd_country'] = $address_components['country'] ?? $country;
        $_POST['wsmd_postal_zip_code'] = $address_components['postal_code'] ?? $postal_code;

        // Save user settings including the geocode
        $_POST['wsmd_geocode'] = $geocode_result['geocode']['lat'] . ',' . $geocode_result['geocode']['lng'];
        WSMD_User_Settings::save_user_settings(get_current_user_id());

        wp_send_json_success(array(
            'message' => __('Settings saved successfully', 'wsmd'),
            'geocode' => $geocode_result['geocode'],
            'address_components' => $address_components
        ));
    }

    /**
     * Geocode the address using Google Places API
     * @param string $address - The full address to geocode
     * @return array - The geocode result with 'success' and 'geocode' or 'message'
     */
    private function geocode_address($address)
    {
        // Retrieve the Google Places API key
        $api_key = WSMD_Woo_Settings::get_settings('wsmd_google_maps_api_key');

        // Check if Google Places API key is set
        if (empty($api_key)) {
            return array('success' => false, 'message' => __('Google Places API key is not set.', 'wsmd'));
        }

        $api_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=$api_key";
        $response = wp_remote_get($api_url);

        // Check if response is an error or not 200
        if (is_wp_error($response) || (wp_remote_retrieve_response_code($response) !== 200)) {
            return array('success' => false, 'message' => __('Failed to contact Google Places API.', 'wsmd'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data['status'] !== 'OK') {
            return array('success' => false, 'message' => __('Geocode was not successful for the following reason: ', 'wsmd') . $data['status']);
        }

        $location = $data['results'][0]['geometry']['location'];
        $address_components = $this->extract_address_components($data['results'][0]['address_components']);
        return array('success' => true, 'geocode' => array('lat' => $location['lat'], 'lng' => $location['lng']), 'address_components' => $address_components);
    }

    /**
     * Extract address components from Google Places API result
     * @param array $components - The address components from the API result
     * @return array - The extracted address components
     */
    private function extract_address_components($components)
    {
        $address = array(
            'street_address' => '',
            'locality' => '',
            'administrative_area_level_1' => '',
            'country' => '',
            'postal_code' => '',
        );

        foreach ($components as $component) {
            if (in_array('street_number', $component['types'])) {
                $address['street_address'] = $component['long_name'];
            }
            if (in_array('route', $component['types'])) {
                $address['street_address'] .= ' ' . $component['long_name'];
            }
            if (in_array('locality', $component['types'])) {
                $address['locality'] = $component['long_name'];
            }
            if (in_array('administrative_area_level_1', $component['types'])) {
                $address['administrative_area_level_1'] = $component['short_name'];
            }
            if (in_array('country', $component['types'])) {
                $address['country'] = $component['long_name'];
            }
            if (in_array('postal_code', $component['types'])) {
                $address['postal_code'] = $component['long_name'];
            }
        }

        return $address;
    }

    /**
     * Get members for member directory
     */
    public function member_directory_get_members()
    {
        // $members = WSMD_Helpers::get_members();
        $members = WSMD_Dummy_Data::get_members();

        wp_send_json_success(array(
            'members' => $members
        ));
    }
}
