<?php

/**
 * Dashboard template
 * @var $args['current_user'] WP_User Current user
 * @var $args['user_settings'] array User settings
 */
?>
<div id="wsmd-dashboard" class="is-member-directory">

    <p class="wsmd-info">
         <?php
            _e(
                'Please fill in your details below.'
                . ' This information will be displayed in the member directory.'
                . ' Once you have filled in your details, click the "Geo-code my location" button to generate your coordinates.'
                . ' It will try to automatically generate your coordinates based on your adress information.'
                . ' You can also drag the marker on the map to adjust your coordinates.'
                . ' Once you are happy with the marker location, click the "Save Settings" button to save your details.'
            , 'wsmd');
        ?>
    </p>

    <!-- Add all user fields here -->
    <form id="wsmd-form" action="<?php echo admin_url('admin-ajax.php'); ?>">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wsmd_dashboard_save_user_settings'); ?>">
        <input type="hidden" name="action" value="wsmd_dashboard_save_user_settings">
        <input type="hidden" name="wsmd_geocode" value="<?php echo esc_attr($args['user_settings']['wsmd_geocode']); ?>">
        <input type="text" name="wsmd_occupation" placeholder="<?php esc_attr_e('Occupation', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_occupation']); ?>">
        <input type="text" name="wsmd_company" placeholder="<?php esc_attr_e('Company', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_company']); ?>">
        <input type="url" name="wsmd_website" placeholder="<?php esc_attr_e('Website', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_website']); ?>">
        <input type="tel" name="wsmd_phone" placeholder="<?php esc_attr_e('Phone', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_phone']); ?>">
        <input type="email" name="wsmd_email" placeholder="<?php esc_attr_e('Email', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_email']); ?>">
        <input type="text" name="wsmd_address" placeholder="<?php esc_attr_e('Address', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_address']); ?>">
        <input type="text" name="wsmd_city" placeholder="<?php esc_attr_e('City', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_city']); ?>">
        <input type="text" name="wsmd_province_state" placeholder="<?php esc_attr_e('Province/State', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_province_state']); ?>">
        <input type="text" name="wsmd_postal_zip_code" placeholder="<?php esc_attr_e('Postal/Zip code', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_postal_zip_code']); ?>">
        <input type="text" name="wsmd_country" placeholder="<?php esc_attr_e('Country', 'wsmd'); ?>" value="<?php echo esc_attr($args['user_settings']['wsmd_country']); ?>">
        <div id="wsmd-map"></div>
        <button id="wsmd-geocode-address" type="button" class="button"><?php _e('Geo-code my address', 'wsmd'); ?></button>
        <button id="wsmd-save-settings" type="button" class="button"><?php _e('Save Settings', 'wsmd'); ?></button>
        <div id="wsmd-form-message"></div>
    </form>
</div>