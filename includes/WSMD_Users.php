<?php

namespace WSMD;


class WSMD_Users{
    
    public function __construct(){
        // Register fields for the user
        // add_action( 'show_user_profile', array($this, 'add_user_fields'), 100 );
        add_action( 'edit_user_profile', array($this, 'add_user_fields'), 100 );

        // Save the fields
        // add_action( 'personal_options_update', array($this, 'save_user_fields') );
        add_action( 'edit_user_profile_update', array($this, 'save_user_fields') );
    }

    /**
     * Add the user fields
     */
    public function add_user_fields($user){

        // Retrieve the user meta value or default to 'default' if it's not set
        $visibility = get_user_meta($user->ID, 'wsmd_visibility', true);
        $visibility = ($visibility === '') ? 'default' : $visibility;

        ?>
        <h3><?php _e('Member Directory', 'wsmd'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="wsmd_visibility"><?php _e('Force this member to be listed or removed in the Member Directory', 'wsmd'); ?></label></th>
                <td>
                    <label style="margin-right: 10px;">
                        <input type="radio" name="wsmd_visibility" value="default" <?php checked( $visibility, 'default' ); ?>>
                        <?php _e('Default (Let the subscription decide)', 'wsmd'); ?>
                    </label>
                    <label style="margin-right: 10px;">
                        <input type="radio" name="wsmd_visibility" value="forced" <?php checked( $visibility, 'forced' ); ?>>
                        <?php _e('Force to be listed in the Member Directory', 'wsmd'); ?>
                    </label>
                    <label>
                        <input type="radio" name="wsmd_visibility" value="removed" <?php checked( $visibility, 'removed' ); ?>>
                        <?php _e('Remove from the Member Directory', 'wsmd'); ?>
                    </label>
                </td>
            </tr>
            <!-- Geolocation coordinates -->
            <tr>
                <th><label for="wsmd_geolocation"><?php _e('Geolocation coordinates', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_geolocation" id="wsmd_geolocation" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_geolocation', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The geolocation coordinates of the user. Example: 45.5017, -73.5673', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Occupation -->
            <tr>
                <th><label for="wsmd_occupation"><?php _e('Occupation', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_occupation" id="wsmd_occupation" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_occupation', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The occupation of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Company -->
            <tr>
                <th><label for="wsmd_company"><?php _e('Company', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_company" id="wsmd_company" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_company', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The company of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Address -->
            <tr>
                <th><label for="wsmd_address"><?php _e('Address', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_address" id="wsmd_address" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_address', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The address of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- City -->
            <tr>
                <th><label for="wsmd_city"><?php _e('City', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_city" id="wsmd_city" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_city', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The city of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Province -->
            <tr>
                <th><label for="wsmd_province"><?php _e('Province', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_province" id="wsmd_province" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_province', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The province of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Postal Code -->
            <tr>
                <th><label for="wsmd_postal_code"><?php _e('Postal Code', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_postal_code" id="wsmd_postal_code" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_postal_code', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The postal code of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Country -->
            <tr>
                <th><label for="wsmd_country"><?php _e('Country', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_country" id="wsmd_country" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_country', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The country of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Website -->
            <tr>
                <th><label for="wsmd_website"><?php _e('Website', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_website" id="wsmd_website" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_website', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The website of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Phone -->
            <tr>
                <th><label for="wsmd_phone"><?php _e('Phone', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_phone" id="wsmd_phone" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_phone', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The phone number of the user', 'wsmd'); ?></p>
                </td>
            </tr>
            <!-- Email -->
            <tr>
                <th><label for="wsmd_email"><?php _e('Email', 'wsmd'); ?></label></th>
                <td>
                    <input type="text" name="wsmd_email" id="wsmd_email" value="<?php echo esc_attr( get_user_meta($user->ID, 'wsmd_email', true) ); ?>" class="regular-text">
                    <p class="description"><?php _e('The email of the user', 'wsmd'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save the user fields. No need to check nonce because it is already done by WordPress.
     */
    public function save_user_fields($user_id){

        // Sanitize and save the fields visibility
        if( isset($_POST['wsmd_visibility']) ){
            $_POST['wsmd_visibility'] = sanitize_text_field($_POST['wsmd_visibility']);
            update_user_meta( $user_id, 'wsmd_visibility', $_POST['wsmd_visibility'] );
        }

        // Sanitize and save the fields geolocation
        if( isset($_POST['wsmd_geolocation']) ){
            $_POST['wsmd_geolocation'] = sanitize_text_field($_POST['wsmd_geolocation']);
            update_user_meta( $user_id, 'wsmd_geolocation', $_POST['wsmd_geolocation'] );
        }

        // Sanitize and save the fields occupation
        if( isset($_POST['wsmd_occupation']) ){
            $_POST['wsmd_occupation'] = sanitize_text_field($_POST['wsmd_occupation']);
            update_user_meta( $user_id, 'wsmd_occupation', $_POST['wsmd_occupation'] );
        }

        // Sanitize and save the fields company
        if( isset($_POST['wsmd_company']) ){
            $_POST['wsmd_company'] = sanitize_text_field($_POST['wsmd_company']);
            update_user_meta( $user_id, 'wsmd_company', $_POST['wsmd_company'] );
        }

        // Sanitize and save the fields address
        if( isset($_POST['wsmd_address']) ){
            $_POST['wsmd_address'] = sanitize_text_field($_POST['wsmd_address']);
            update_user_meta( $user_id, 'wsmd_address', $_POST['wsmd_address'] );
        }

        // Sanitize and save the fields city
        if( isset($_POST['wsmd_city']) ){
            $_POST['wsmd_city'] = sanitize_text_field($_POST['wsmd_city']);
            update_user_meta( $user_id, 'wsmd_city', $_POST['wsmd_city'] );
        }

        // Sanitize and save the fields province
        if( isset($_POST['wsmd_province']) ){
            $_POST['wsmd_province'] = sanitize_text_field($_POST['wsmd_province']);
            update_user_meta( $user_id, 'wsmd_province', $_POST['wsmd_province'] );
        }

        // Sanitize and save the fields postal code
        if( isset($_POST['wsmd_postal_code']) ){
            $_POST['wsmd_postal_code'] = sanitize_text_field($_POST['wsmd_postal_code']);
            update_user_meta( $user_id, 'wsmd_postal_code', $_POST['wsmd_postal_code'] );
        }

        // Sanitize and save the fields country
        if( isset($_POST['wsmd_country']) ){
            $_POST['wsmd_country'] = sanitize_text_field($_POST['wsmd_country']);
            update_user_meta( $user_id, 'wsmd_country', $_POST['wsmd_country'] );
        }

        // Sanitize and save the fields website
        if( isset($_POST['wsmd_website']) ){
            $_POST['wsmd_website'] = sanitize_text_field($_POST['wsmd_website']);
            update_user_meta( $user_id, 'wsmd_website', $_POST['wsmd_website'] );
        }

        // Sanitize and save the fields phone
        if( isset($_POST['wsmd_phone']) ){
            $_POST['wsmd_phone'] = sanitize_text_field($_POST['wsmd_phone']);
            update_user_meta( $user_id, 'wsmd_phone', $_POST['wsmd_phone'] );
        }

        // Sanitize and save the fields email
        if( isset($_POST['wsmd_email']) ){
            $_POST['wsmd_email'] = sanitize_text_field($_POST['wsmd_email']);
            update_user_meta( $user_id, 'wsmd_email', $_POST['wsmd_email'] );
        }
    }

    /**
     * Get the user meta for the Member Directory
     * 
     * @param int $user_id The user ID
     * 
     * @return string The user meta for the Member Directory
     */
    public static function get_user_settings($userID){
        return array(
            'wsmd_visibility' => get_user_meta($userID, 'wsmd_visibility', true),
            'wsmd_geolocation' => get_user_meta($userID, 'wsmd_geolocation', true),
            'wsmd_occupation' => get_user_meta($userID, 'wsmd_occupation', true),
            'wsmd_company' => get_user_meta($userID, 'wsmd_company', true),
            'wsmd_address' => get_user_meta($userID, 'wsmd_address', true),
            'wsmd_city' => get_user_meta($userID, 'wsmd_city', true),
            'wsmd_province' => get_user_meta($userID, 'wsmd_province', true),
            'wsmd_postal_code' => get_user_meta($userID, 'wsmd_postal_code', true),
            'wsmd_country' => get_user_meta($userID, 'wsmd_country', true),
            'wsmd_website' => get_user_meta($userID, 'wsmd_website', true),
            'wsmd_phone' => get_user_meta($userID, 'wsmd_phone', true),
            'wsmd_email' => get_user_meta($userID, 'wsmd_email', true),
        );
    }
}
