<?php
/**
 * Member Directory template
 * 
 * @var $args['shortcode_args'] Array of shortcode arguments
 */
?>
<div id="wsmd-member-directory">
    <form id="wsmd-form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        <input type="hidden" name="action" value="wsmd_member_directory_get_members">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wsmd_member_directory_get_members'); ?>">
        <p class="wsmd-filter-title"><?php esc_html_e('Find Members', 'wsmd'); ?></p>
        <input id="wsmd-search-address" type="search" placeholder="<?php esc_attr_e('Adress, city, post/zip code, country', 'wsmd'); ?>">
        <button id="wsmd-search-button"><?php esc_html_e('Search', 'wsmd'); ?></button>
        <span><?php esc_html_e('or', 'wsmd'); ?></span>
        <button id="wsmd-search-near-me">Search Near Me</button>
        <p id="form-message"></p>
    </form>
    <div id="wsmd-map"></div>
    <div id="wsmd-member-list">
        <!-- Member items will be dynamically populated here -->
    </div>
</div>
