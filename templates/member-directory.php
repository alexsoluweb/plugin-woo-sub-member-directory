<?php
/**
 * Member Directory template
 * 
 * @var $args['shortcode_args'] Array of shortcode arguments
 * @var $args['members'] Array of members
 */
?>
<div id="wsmd-member-directory">
    <form id="wsmd-form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        <input type="hidden" name="action" value="wsmd_member_directory_get_members">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wsmd_member_directory_get_members'); ?>">
        <label for="wsmd-search-address"><?php esc_html_e('Find the nearest members', 'wsmd'); ?></label>
        <input id="wsmd-search-address" type="text" placeholder="<?php esc_attr_e('City, postal code, country, etc.', 'wsmd'); ?>">
        <span id="wsmd-filter-or"><?php esc_html_e('or', 'wsmd'); ?></span>
        <button type="button" id="wsmd-my-location"><?php esc_html_e('My location', 'wsmd'); ?></button>
        <p id="wsmd-form-message"></p>
    </form>
    <div id="wsmd-map"></div>
    <div id="wsmd-member-list-container">
        <div id="wsmd-member-list">
            <!-- Member items will be dynamically populated here -->
        </div>
        <div id="wsmd-member-list-pagination"></div>
    </div>
</div>
