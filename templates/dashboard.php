<?php

/**
 * Dashboard template
 * @var $args['current_user'] WP_User Current user
 * @var $args['user_settings'] array User settings
 * @var $args['terms'] Array of WP_Term objects (available taxonomies)
 */
?>
<div id="wsmd-dashboard" class="is-member-directory">

    <p id="wsmd-user-info">
        <?php
        _e(
            'Please fill in your details below.'
                . ' This information will be displayed in the member directory.'
                . ' Your position on the map will be automatically determined by the coordinates you enter after saving your settings.'
                . ' You can select multiple taxonomies that describe your skills and expertise.'
                . ' You can also update your settings at any time.',
            'wsmd'
        );
        ?>
    </p>

    <!-- Add all user fields here -->
    <form id="wsmd-form" action="<?php echo admin_url('admin-ajax.php'); ?>">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wsmd_woo_dashboard_save_user_settings'); ?>">
        <input type="hidden" name="action" value="wsmd_woo_dashboard_save_user_settings">
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
        <select name="wsmd_taxonomies[]" id="wsmd_taxonomies" multiple="multiple" placeholder="<?php esc_attr_e('Select taxonomies', 'wsmd'); ?>">
            <?php foreach ($args['grouped_terms'] as $parent_id => $group) { ?>
                <?php if (empty($group['terms'])) { ?>
                    <option value="<?php echo esc_attr($parent_id); ?>" <?php echo in_array($parent_id, $args['user_settings']['wsmd_taxonomies']) ? 'selected="selected"' : ''; ?>>
                        <?php echo esc_html($group['label']); ?>
                    </option>
                <?php } else { ?>
                    <optgroup label="<?php echo esc_attr($group['label']); ?>">
                        <?php foreach ($group['terms'] as $term) { ?>
                            <option value="<?php echo esc_attr($term->term_id); ?>" <?php echo in_array($term->term_id, $args['user_settings']['wsmd_taxonomies']) ? 'selected="selected"' : ''; ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                <?php } ?>
            <?php } ?>
        </select>
        <label id="wsmd-hide-profile">
            <input type="checkbox" name="wsmd_hide_profile" value="1" <?php echo $args['user_settings']['wsmd_hide_profile'] ? 'checked="checked"' : ''; ?>>
            <?php esc_html_e('Remove my profile from the public member directory', 'wsmd'); ?>
        </label>
        <div id="wsmd-map"></div>
        <button id="wsmd-save-settings" type="button" class="button"><?php _e('Save Settings', 'wsmd'); ?></button>
        <div id="wsmd-form-message"></div>
    </form>
</div>