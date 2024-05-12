<?php

namespace WSMD;

class WSMD_AJAX{
    public function __construct(){
        add_action('wp_ajax_wsmd_dashboard_save_user_settings', array($this, 'dashboard_save_user_settings'));
    }

    public function dashboard_save_user_settings(){

        // Check nonce
        if(!check_ajax_referer('wsmd_dashboard_save_user_settings', 'nonce', false)){
            wp_send_json_error(array(
                'message' => __('Invalid nonce', 'wsmd')
            ));
        }

        // Check user logged in
        if(!is_user_logged_in()){
            wp_send_json_error(array(
                'message' => __('You must be logged in to save settings', 'wsmd')
            ));
        }

        WSMD_Users::save_user_settings(get_current_user_id());

        wp_send_json_success(array(
            'message' => __('Settings saved successfully', 'wsmd')
        ));
    }
}