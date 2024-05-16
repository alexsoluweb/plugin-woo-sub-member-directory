<?php
/**
 * Member Directory template
 * 
 * @var $args['shortcode_args'] Array of shortcode arguments
 * @var $args['terms'] array Available taxonomies set in the plugin settings
 */
?>
<div id="wsmd-member-directory">
    <form id="wsmd-form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        <p id="wsmd-form-message"></p>
        <div class="wsmd-filter-row search">
            <input type="hidden" name="action" value="wsmd_member_directory_get_members">
            <label for="wsmd-search-address"><?php esc_html_e('Find the nearest members', 'wsmd'); ?></label>
            <input id="wsmd-search-address" type="text" placeholder="<?php esc_attr_e('Enter a location', 'wsmd'); ?>">
        </div>
        <div class="wsmd-filter-row my-location">
            <div id="wsmd-filter-or">
                <span><?php esc_html_e('or', 'wsmd'); ?></span>
            </div>
            <button type="button" id="wsmd-my-location" title="<?php esc_attr_e('My location', 'wsmd'); ?>">
                <span class="btn-text"><?php esc_html_e('Use my location', 'wsmd'); ?></span>
                <span class="wsmd-icon-paper-plane"></span>
            </button>
        </div>
        <div class="wsmd-filter-row taxonomies">
            <label for="wsmd_taxonomies"><?php esc_html_e('Filter by taxonomies', 'wsmd'); ?></label>
            <select name="wsmd_taxonomies[]" id="wsmd_taxonomies" multiple="multiple" data-placeholder="<?php esc_attr_e('Select taxonomies', 'wsmd'); ?>">
                <?php foreach ($args['terms'] as $term) { ?>
                    <option value="<?php echo esc_attr($term->term_id); ?>">
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </form>
    <div id="wsmd-map"></div>
    <div id="wsmd-member-list-container">
        <div id="wsmd-member-list">
            <!-- Member items will be dynamically populated here -->
        </div>
        <button id="wsmd-member-list-load-more"><?php esc_html_e('Load more', 'wsmd'); ?></button>
    </div>
</div>
