import { MarkerClusterer } from "@googlemaps/markerclusterer";

/**
 * Update the MarkerClusterer instance and set map bounds
 * @param {Object} context - The context (MemberDirectory instance)
 * @param {google.maps.LatLngBounds} bounds - The bounds to set for the map
 * @returns {void}
 */
export const updateMarkerClusterer = (context, bounds) => {
  if (context.markerClusterer) {
    context.markerClusterer.clearMarkers();
  }

  const visibleMarkers = context.markers.filter(marker => marker.getVisible());

  if (visibleMarkers.length > 1) {
    context.markerClusterer = new MarkerClusterer({
      markers: visibleMarkers,
      map: context.map
    });
    context.map.fitBounds(bounds);

    google.maps.event.addListenerOnce(context.map, 'idle', () => {
      if (context.map.getZoom() > 12) {
        context.map.setZoom(12);
      }
    });
  } else if (visibleMarkers.length === 1) {
    context.map.setCenter(visibleMarkers[0].getPosition());
    context.map.setZoom(12);
  } else {
    context.map.setCenter({ lat: 0, lng: 0 });
    context.map.setZoom(2);
  }
};

/**
 * Open info window with member details
 * @param {google.maps.InfoWindow} infoWindow - The info window instance
 * @param {google.maps.Marker} marker - The marker instance
 * @param {Object} member - Member data
 * @returns {void}
 */
export const openInfoWindow = (infoWindow, marker, member) => {
  let content = `
    <div class="wsmd-map-info-window">
      <div class="wsmd-map-info-window-header">
        <h3 class="wsmd-map-info-window-company">${member.wsmd_company}</h3>
        <p class="wsmd-map-info-window-occupation">${member.wsmd_occupation}</p>
      </div>
      <hr>
      <div class="wsmd-map-info-window-body">
        <span class="wsmd-map-info-window-address">
          <marker class="wsmd-icon-map-marker"></marker>
          ${member.wsmd_address}, ${member.wsmd_city}, ${member.wsmd_province_state}, ${member.wsmd_country}, ${member.wsmd_postal_zip_code}
        </span>`;

  if (member.wsmd_website) {
    content += `
      <span class="wsmd-map-info-window-website">
        <marker class="wsmd-icon-external-link"></marker>
        ${member.wsmd_website}
      </span>`;
  }
  if (member.wsmd_phone) {
    content += `
      <span class="wsmd-map-info-window-phone">
        <marker class="wsmd-icon-phone"></marker>
        ${member.wsmd_phone}
      </span>`;
  }
  if (member.wsmd_email) {
    content += `
      <span class="wsmd-map-info-window-email">
        <marker class="wsmd-icon-email"></marker>
        ${member.wsmd_email}
      </span>`;
  }

  content += `
      </div>
    </div>`;

  infoWindow.setContent(content);
  infoWindow.open(marker.getMap(), marker);
};

/**
 * Set marker animation
 * @param {Object} context - The context (MemberDirectory instance)
 * @param {google.maps.Marker} marker - The marker instance
 * @returns {void}
 */
export const setMarkerAnimation = (context, marker) => {
  marker.setAnimation(google.maps.Animation.BOUNCE);
  context.activeMarker = marker;
}

/**
 * Reset marker animation
 * @param {Object} context - The context (MemberDirectory instance)
 * @returns {void}
 */
export const resetMarkerAnimation = (context) => {
  if (context.activeMarker) {
    context.activeMarker.setAnimation(null);
  }
};