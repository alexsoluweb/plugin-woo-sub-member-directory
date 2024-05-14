import '../../scss/member-directory.scss'
import { MarkerClusterer } from "@googlemaps/markerclusterer";
import mapStyles, { svgMarker } from "../map-style";

class MemberDirectory {

  // Properties
  /** @type {HTMLDivElement} */
  static memberDirectory = null;
  /** @type {google.maps.Map} */
  static map = null;
  /** @type {HTMLFormElement} */
  static form = null;
  /** @type {HTMLParagraphElement} */
  static formMessage = null;
  /** @type {Array<google.maps.Marker>} */
  static markers = [];
  /** @type {Array<Object>} */
  static memberList = null;
  /** @type {number} */
  static memberListOffset = 0;
  /** @type {number} */
  static memberListPerPage = 9;

  /**
   * Init application
   * @returns {void}
   */
  static init() {

    this.memberDirectory = document.querySelector('#wsmd-member-directory');

    // Check if the member directory element exists
    if (!this.memberDirectory) {
      return;
    }

    this.form = this.memberDirectory.querySelector('#wsmd-form');
    this.formMessage = this.form.querySelector('#wsmd-form-message');

    // fetch the members data
    this.fetchMembersData();
    // Init Map Places service
    this.initMapPlacesService();

    // Listen for event when members data is ready
    document.addEventListener('wsmd-members-data-ready', (e) => {
      this.randomizeMemberList();
      this.displayMembers();
      this.initGoogleMap();
    });

    // Pagination event listener
    this.memberDirectory.querySelector('#wsmd-member-list-load-more').addEventListener('click', (e) => {
      e.preventDefault();
      this.loadMoreMembers();
    });

    // Button for "Search my location"
    this.form.querySelector('#wsmd-my-location').addEventListener('click', (e) => {
      e.preventDefault();
      this.handleSearchNearMe();
    });

    // Prevent the form from submitting
    this.form.addEventListener('submit', (e) => {
      e.preventDefault();
    });
  }

  /**
   * Randomize the member list
   * @returns {void}
   */
  static randomizeMemberList() {
    this.memberList.sort(() => Math.random() - 0.5);
  }

  /**
   * Display the member list based on the current offset and limit
   * @returns {void}
   */
  static displayMembers() {
    const memberList = this.memberDirectory.querySelector('#wsmd-member-list');
    const start = this.memberListOffset;
    const end = this.memberListOffset + this.memberListPerPage;
    const membersToDisplay = this.memberList.slice(start, end);

    // Hide the load more button if there are no more members to display
    if (membersToDisplay.length > 0) {
      this.memberDirectory.querySelector('#wsmd-member-list-load-more').style.display = 'block';
    }else{
      this.memberDirectory.querySelector('#wsmd-member-list-load-more').style.display = 'none';
    }

    membersToDisplay.forEach(member => {
      const memberItem = document.createElement('div');
      memberItem.classList.add('wsmd-member-item');
      memberItem.innerHTML = `
        <div class="wsmd-member-item-header">
          <h3 class="wsmd-member-item-company">${member.wsmd_company}</h3>
          <p class="wsmd-member-item-occupation">${member.wsmd_occupation}</p>
        </div>
        <hr>
        <div class="wsmd-member-item-body">
          <div class="wsmd-member-item-address">
            <marker class="wsmd-icon-map-marker"></marker>
            ${member.wsmd_address}, ${member.wsmd_city}, ${member.wsmd_province_state}, ${member.wsmd_country}, ${member.wsmd_postal_zip_code}
          </div>
          <div class="wsmd-member-item-website">
            <marker class="wsmd-icon-external-link"></marker>
            ${member.wsmd_website}
          </div>
          <div class="wsmd-member-item-phone">
            <marker class="wsmd-icon-phone"></marker>
            ${member.wsmd_phone}
          </div>
          <div class="wsmd-member-item-email">
            <marker class="wsmd-icon-email"></marker>
            ${member.wsmd_email}
          </div>
        </div>
      `;
      memberList.appendChild(memberItem);
    });

    // Update the offset for the next load
    this.memberListOffset += this.memberListPerPage;
  }

  /**
   * Load more members and append them to the list
   * @returns {void}
   */
  static loadMoreMembers() {
    this.displayMembers();
  }

  /**
   * Handle the search near me button
   * @returns {void}
   */
  static handleSearchNearMe() {
    this.form.classList.add('loading');
    this.formMessage.classList.remove('error');
    this.formMessage.innerHTML = '';

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const userLocation = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
          };
          const nearestMarker = this.getNearestMarker(userLocation.lat, userLocation.lng);

          if (nearestMarker) {
            this.map.panTo(nearestMarker.getPosition());
            this.map.setZoom(12);
          } else {
            this.map.panTo(userLocation);
            this.map.setZoom(12);
          }
          this.form.classList.remove('loading');
        },
        () => {
          // User denied the request for Geolocation, or something else went wrong
          this.formMessage.innerHTML = 'Error: The Geolocation service failed.';
          this.formMessage.classList.add('error');
          this.form.classList.remove('loading');
        }
      );
    } else {
      // Browser doesn't support Geolocation
      this.formMessage.innerHTML = 'Error: Your browser doesn\'t support geolocation.';
      this.formMessage.classList.add('error');
      this.form.classList.remove('loading');
    }
  }

  /**
   * Initialize the Google Map Places Service
   * @returns {void}
   */
  static initMapPlacesService() {
    /** @type {HTMLInputElement} */
    const input = this.form.querySelector('#wsmd-search-address');
    const autocomplete = new google.maps.places.Autocomplete(input);

    autocomplete.addListener('place_changed', () => {
      const place = autocomplete.getPlace();
      if (!place.geometry) {
        console.log("Returned place contains no geometry");
        return;
      }

      const lat = place.geometry.location.lat();
      const lng = place.geometry.location.lng();
      const nearestMarker = this.getNearestMarker(lat, lng);

      // Pan to the nearest marker, or to the searched location
      if (nearestMarker) {
        this.map.panTo(nearestMarker.getPosition());
        this.map.setZoom(12);
      } else {
        this.map.panTo({ lat, lng });
        this.map.setZoom(12);
      }
    });
  }

  /**
   * Get the nearest marker to a given latitude and longitude
   * @param {number} lat
   * @param {number} lng
   * @returns {google.maps.Marker}
   */
  static getNearestMarker(lat, lng) {
    let nearestMarker = null;
    let minDistance = Infinity;

    this.markers.forEach(marker => {
      const d = google.maps.geometry.spherical.computeDistanceBetween(new google.maps.LatLng(lat, lng), marker.getPosition());
      if (d < minDistance) {
        minDistance = d;
        nearestMarker = marker;
      }
    });

    return nearestMarker;
  }

  /**
   * Initialize the Google Map 
   * @returns {void}
   */
  static initGoogleMap() {

    // Create a new map
    this.map = new google.maps.Map(document.querySelector('#wsmd-map'), {
      center: { lat: 0, lng: 0 },
      zoom: 2,
      styles: mapStyles,
      mapTypeControlOptions: {
        mapTypeIds: ['roadmap'] // Disable satellite option
      }
    });

    // Create a new info window
    const infoWindow = new google.maps.InfoWindow();

    // Create a new bounds
    const bounds = new google.maps.LatLngBounds();

    // Map through the members and create markers
    this.markers = this.memberList.map(member => {

      // Create a new marker
      const marker = new google.maps.Marker({
        position: {
          lat: parseFloat(member.wsmd_geocode.split(',')[0]),
          lng: parseFloat(member.wsmd_geocode.split(',')[1]),
        },
        map: this.map,
        title: member.wsmd_company,
        icon: svgMarker,
      });

      // Add the marker to the bounds
      bounds.extend(marker.getPosition());

      // Add a click event listener to the marker
      marker.addListener('click', () => {

        //Set the content of the info window
        infoWindow.setContent(`
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
              </span>
              <span class="wsmd-map-info-window-website">
                <marker class="wsmd-icon-external-link"></marker>
                ${member.wsmd_website}
              </span>
              <span class="wsmd-map-info-window-phone">
                <marker class="wsmd-icon-phone"></marker>
                ${member.wsmd_phone}
              </span>
              <span class="wsmd-map-info-window-email">
                <marker class="wsmd-icon-email"></marker>
                ${member.wsmd_email}
              </span>
            </div>
          </div>
        `);

        // Open the info window
        infoWindow.open(this.map, marker)
      });

      return marker;
    });

    // Create a new marker cluster
    new MarkerClusterer({ markers: this.markers, map: this.map });

    // Fit the map to the bounds
    this.map.fitBounds(bounds);
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
          const event = new CustomEvent('wsmd-members-data-ready');
          this.memberList = Object.values(data.data.members);
          document.dispatchEvent(event);
        } else {
          this.formMessage.classList.add('error');
          this.formMessage.innerHTML = data.data.message;
        }
      })
      .catch((error) => {
        this.formMessage.classList.add('error');
        this.formMessage.innerHTML = 'An error occurred while fetching members data';
      });
  }
}

// Main entry point
document.addEventListener('DOMContentLoaded', () => {
  MemberDirectory.init();
});
