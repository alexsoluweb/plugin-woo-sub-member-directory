<?php

/**
 * Documentation class
 */

namespace WSMD;

class WSMD_Docs
{
    /**
     * The constructor
     */
    public function __construct()
    {
        // Add thickbox to the plugin page for our documentation link
        add_action('admin_enqueue_scripts', array($this, 'enqueue_thickbox'));

        // Add a documentation link to the plugin page
        add_filter('plugin_action_links_' . WSMD_BASENAME, array($this, 'add_action_links'));

        // Add content to the Thickbox
        add_action('admin_footer', array($this, 'plugin_footer_content'));
    }

    /**
     * Add thickbox to the plugin page for our documentation link
     */
    public function enqueue_thickbox()
    {
        if (get_current_screen()->base === 'plugins') {
            add_thickbox();
        }
    }

    /**
     * Add a documentation link to the plugin page
     * 
     * @param array $links The plugin action links
     * @return array The modified plugin action links
     */
    public function add_action_links($links)
    {
        $links[] = '<a href="#TB_inline?width=742&height=889&inlineId=wsmd-plugin-docs" class="thickbox">' . esc_html__('Documentation', 'wsmd') . '</a>';
        return $links;
    }

    /**
     * Add content to the Thickbox
     */
    public function plugin_footer_content()
    {
        if (get_current_screen()->base == 'plugins') {
?>
            <div id="wsmd-plugin-docs" style="display:none;">
                <?php echo self::get_docs(); ?>
            </div>
<?php
        }
    }

    /**
     * Get the plugin documentation
     * 
     * @return string The plugin documentation
     */
    public static function get_docs()
    {
        return <<<DOCS
<h1>WSMD Plugin Documentation</h1>

<p>
    The Member Directory plugin allows you to create a member directory on your website using the Google Maps API.
    This plugin integrates with WooCommerce subscriptions to enable users to subscribe and gain access to the member directory.
</p>

<h2>Settings</h2>

<p>
    The plugin settings can be found in the WooCommerce settings page under the <a href="https://localhost/wp-admin/admin.php?page=wc-settings&tab=wsmd" target="_blank">Member Directory</a> tab. 
    Here, you can configure the taxonomies available for members to select and the subscription products that grant access to the member directory.
    This is also the place where you need to set your Google Maps API keys.
</p>

<p>
    You need two separate API keys for this plugin:
    <ul>
        <li><strong>Geocoding API Key (Backend Use):</strong> This key is used for server-side geocoding requests to convert addresses into geographic coordinates.</li>
        <li><strong>Google Maps API Key (Frontend Use):</strong> This key is used for rendering maps and location-based features on the frontend, like showing member locations on a map or using the Places API for address autocomplete.</li>
    </ul>
</p>

<p>
    For more information on obtaining a Google Maps API key, please visit: 
    <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Get API Key</a>.
</p>

<p>
    <strong>API Key Restrictions:</strong> 
    <ul>
        <li>The <strong>Geocoding API Key</strong> should be either unrestricted or restricted by your server's IP address.</li>
        <li>The <strong>Google Maps API Key</strong> for frontend use should be restricted by HTTP referrer to your website's domain to prevent unauthorized use.</li>
    </ul>
</p>

<h2>User Administration</h2>

<p>
    You can manage users who have access to the member directory on each user's profile page.
    Here, you can view the user's details, the taxonomies they have selected, and the coordinates of their location.
    You can also force a user to be listed or hidden in the member directory, independent of their subscription status.
</p>

<h2>Member Directory</h2>

<p>
    The member directory can be displayed on a page or post using the <code>[wsmd_member_directory]</code> shortcode.
    The member directory displays a list of members with their details and taxonomies, along with a map that shows markers for each member's location.
</p>

<p>
    Features include:
    <ul>
        <li>Marker popup info windows with member details.</li>
        <li>Dropdown filters to filter members by taxonomies.</li>
        <li>Location-based searches to display the nearest members by entering a location in the search box.</li>
        <li>'Use my location' button to display the closest members to the user's current location.</li>
    </ul>
</p>

<h2>Member Dashboard</h2>

<p>
    The member dashboard can be found on the "My Account" page of WooCommerce under the "Member Directory" tab.
    The dashboard allows members to update their details and settings.
</p>

<p>
    Features include:
    <ul>
        <li>Updating personal details and selecting taxonomies that describe their skills and expertise.</li>
        <li>Entering coordinates to set their location, with the plugin using the Geocoding API to obtain latitude and longitude.</li>
        <li>Choosing to be listed or hidden from the member directory.</li>
    </ul>
</p>

DOCS;
    }
}
