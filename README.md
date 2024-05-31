# WSMD Plugin Documentation

The Member Directory plugin allows you to create a member directory on your website using the Google Maps API. This plugin integrates with WooCommerce subscriptions to enable users to subscribe and gain access to the member directory.

## Settings

The plugin settings can be found in the WooCommerce settings page under the **Member Directory** tab. Here, you can configure the taxonomies available for members to select and the subscription products that grant access to the member directory. This is also the place where you need to set your Google Maps API key.

For more information on obtaining a Google Maps API key, please visit: [Get API Key](https://developers.google.com/maps/documentation/maps-static/get-api-key).

The plugin requires the following Google APIs:
- **Geocoding API**: Converts addresses into geographic coordinates.
- **Places API**: Retrieves coordinates of the locations entered by members.
- **Geometry API**: Calculates the distance between two coordinates.

It is highly recommended to restrict your API key to your website domain to prevent unauthorized use.

## User Administration

You can manage users who have access to the member directory on each user's profile page. Here, you can view the user's details, the taxonomies they have selected, and the coordinates of their location. You can also force a user to be listed or hidden in the member directory, independent of their subscription status.

## Member Directory

The member directory can be displayed on a page or post using the `[wsmd_member_directory]` shortcode. The member directory displays a list of members with their details and taxonomies, along with a map that shows markers for each member's location.

Features include:
- Marker popup info windows with member details.
- Dropdown filters to filter members by taxonomies.
- Location-based searches to display the nearest members by entering a location in the search box.
- 'Use my location' button to display the closest members to the user's current location.

## Member Dashboard

The member dashboard can be found on the "My Account" page of WooCommerce under the **Member Directory** tab. The dashboard allows members to update their details and settings.

Features include:
- Updating personal details and selecting taxonomies that describe their skills and expertise.
- Entering coordinates to set their location, with the plugin using the Geocoding API to obtain latitude and longitude.
- Choosing to be listed or hidden from the member directory.
