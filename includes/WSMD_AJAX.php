<?php

namespace WSMD;

/**
 * AJAX functions for the plugin
 */
class WSMD_AJAX{
    public function __construct(){
        add_action('wp_ajax_wsmd_dashboard_save_user_settings', array($this, 'dashboard_save_user_settings'));
        add_action('wp_ajax_wsmd_member_directory_get_members', array($this, 'member_directory_get_members'));
        add_action('wp_ajax_nopriv_wsmd_member_directory_get_members', array($this, 'member_directory_get_members'));
    }

    /**
     * Save user settings
     */
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

        WSMD_User_Settings::save_user_settings(get_current_user_id());

        wp_send_json_success(array(
            'message' => __('Settings saved successfully', 'wsmd')
        ));
    }

    /**
     * Get members for member directory
     */
    public function member_directory_get_members(){

        // $members = WSMD_Helpers::get_members();
        $members = WSMD_Dummy_Data::get_members();

        wp_send_json_success(array(
            'members' => $members
        ));
    }

}