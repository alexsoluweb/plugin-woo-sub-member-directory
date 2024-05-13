import '../../scss/member-directory.scss'
import { MarkerClusterer } from "@googlemaps/markerclusterer";

class MemberDirectory {

  /**
   * Init application
   * @returns {void}
   */
  static init() {

    // Get the member directory element
    this.memberDirectory = document.querySelector('#wsmd-member-directory');

    // Check if the member directory element exists
    if (!this.memberDirectory) {
      return;
    }

    this.form = this.memberDirectory.querySelector('#wsmd-form');
    this.formMessage = this.form.querySelector('#wsmd-form-message');

    // fetch the members data
    this.fetchMembersData()

    // Listen for the custom event
    document.addEventListener('wsmd-members-data-ready', (event) => {
      const members = Object.values(event.detail);
      this.initGoogleMap(members);
    });
  }

  /**
   * Initialize the Google Map
   * @param {Array} members 
   * @returns {void}
  */
  static initGoogleMap(members) {

    // Create a new map
    const map = new google.maps.Map(document.querySelector('#wsmd-map'), {
      center: { lat: 0, lng: 0 },
      zoom: 2,
    });

    // Create a new info window
    const infoWindow = new google.maps.InfoWindow();

    // Create a new bounds
    const bounds = new google.maps.LatLngBounds();

    // Loop through the members data
    // members.forEach(member => {
    const markers = members.map(member => {   

      // Create a new marker
      const marker = new google.maps.Marker({
        position: { 
          lat: parseFloat(member.wsmd_geocode.split(',')[0]),
          lng: parseFloat(member.wsmd_geocode.split(',')[1]),
        },
        map: map,
        title: member.name,
      });

      // Add the marker to the bounds
      bounds.extend(marker.position);

      // Add a click event listener to the marker
      marker.addListener('click', () => {
        infoWindow.setContent(`
          <div>
            <h3>${member.wsmd_occupation}</h3>
            <p>${member.wsmd_company}</p>
            <p>${member.wsmd_address}, ${member.wsmd_city}, ${member.wsmd_province_state}, ${member.wsmd_country}, ${member.wsmd_postal_zip_code}</p>
            <p>${member.wsmd_website}</p>
            <p>${member.wsmd_phone}</p>
            <p>${member.wsmd_email}</p>
          </div>
        `);
        infoWindow.open(map, marker);
      });

      return marker;
    });

    // Create a new marker cluster
    new MarkerClusterer({ markers, map });

    // Fit the map to the bounds
    map.fitBounds(bounds);
  }

  /**
   * Fetch members data from AJAX endpoint
   * @returns {void}
   */
  static fetchMembersData() {
    const data = new FormData(this.form);
    const ajaxUrl = this.form.getAttribute('action');

    fetch(ajaxUrl, {
      method: 'POST',
      body: data,
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Custom event to handle the data
          const event = new CustomEvent('wsmd-members-data-ready', { detail: data.data.members });
          document.dispatchEvent(event);
        } else {
          this.formMessage.innerHTML = data.data.message;
        }
      })
      .catch((error) => {
        this.formMessage.innerHTML = 'An error occurred while fetching members data';
      });
  }
}

// Main entry point
document.addEventListener('DOMContentLoaded', () => {
  MemberDirectory.init();
});
