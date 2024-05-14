import '../../scss/member-directory.scss';
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
  /** @type {Map<string, google.maps.Marker>} */
  static memberIdToMarkerMap = new Map();
  /** @type {Array<Object>} */
  static memberList = null;
  /** @type {number} */
  static memberListOffset = 0;
  /** @type {number} */
  static memberListPerPage = 9;
  /** @type {google.maps.Marker|null} */
  static activeMarker = null; // Track the currently active marker

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
    this.fetchMembersData(); // Fetch members data
    
    // Listen for event when members data is ready
    document.addEventListener('wsmd-members-data-ready', (e) => {
      this.randomizeMemberList();
      this.displayMembers();
      this.initGoogleMap();
      this.initMapPlacesService();
    });

    // Pagination event listener
    this.memberDirectory.querySelector('#wsmd-member-list-load-more').addEventListener('click', (e) => {
      e.preventDefault();
      this.loadMoreMembers();
    });

    // Button "Search my location" event listener
    this.form.querySelector('#wsmd-my-location').addEventListener('click', (e) => {
      e.preventDefault();
      this.handleSearchNearMe();
    });

    // Prevent the form from submitting
    this.form.addEventListener('submit', (e) => {
      e.preventDefault();
    });

    // Event delegation for member item clicks
    const memberList = this.memberDirectory.querySelector('#wsmd-member-list');
    memberList.addEventListener('click', (e) => {
      const memberItem = e.target.closest('.wsmd-member-item');
      if (memberItem) {
        const memberId = memberItem.dataset.memberId;
        const marker = this.memberIdToMarkerMap.get(memberId);
        if (marker) {
          this.map.panTo(marker.getPosition());
          this.map.setZoom(12);
          // Scroll to the top of the google map
          window.scrollTo({ top: this.memberDirectory.querySelector('#wsmd-map').offsetTop, behavior: 'smooth' });
          
          // Stop the previous marker's bounce animation
          if (this.activeMarker) {
            this.activeMarker.setAnimation(null);
          }

          // Make the current marker bounce
          marker.setAnimation(google.maps.Animation.BOUNCE);
          this.activeMarker = marker;
        }
      }
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
   * @param {boolean} $reset - Reset the list
   * @returns {void}
   */
  static displayMembers($reset = false) {
    const memberList = this.memberDirectory.querySelector('#wsmd-member-list');
    const start = this.memberListOffset;
    const end = this.memberListOffset + this.memberListPerPage;
    const membersToDisplay = this.memberList.slice(start, end);

    // Reset the list
    if ($reset) {
      memberList.innerHTML = '';
    }

    membersToDisplay.forEach((member, index) => {
      const memberItem = document.createElement('div');
      memberItem.classList.add('wsmd-member-item');
      memberItem.dataset.memberId = member.wsmd_id; // Set data-member-id attribute
      memberItem.style.opacity = '0';
      memberItem.style.transform = 'translateY(100px)';
      memberItem.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;

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

      // Trigger reflow for the animation to start
      window.getComputedStyle(memberItem).transform;
      memberItem.style.opacity = '1';
      memberItem.style.transform = 'translateY(0)';
    });

    // Update the offset for the next load
    this.memberListOffset += this.memberListPerPage;

    // Check if there are more members to load
    if (this.memberListOffset >= this.memberList.length) {
      this.memberDirectory.querySelector('#wsmd-member-list-load-more').style.display = 'none';
    } else {
      this.memberDirectory.querySelector('#wsmd-member-list-load-more').style.display = 'block';
    }
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

          // Reorder the member list by proximity to the user's location
          this.memberList.sort((a, b) => {
            const distanceA = this.calculateDistance(userLocation.lat, userLocation.lng, parseFloat(a.wsmd_geocode.split(',')[0]), parseFloat(a.wsmd_geocode.split(',')[1]));
            const distanceB = this.calculateDistance(userLocation.lat, userLocation.lng, parseFloat(b.wsmd_geocode.split(',')[0]), parseFloat(b.wsmd_geocode.split(',')[1]));
            return distanceA - distanceB;
          });

          // Display the reordered member list
          this.memberListOffset = 0;
          this.displayMembers(true);

          const nearestMarker = this.getNearestMarker(userLocation.lat, userLocation.lng);

          // Pan to the nearest marker, or to the user's location
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
        this.formMessage.classList.add('error');
        this.formMessage.innerHTML = 'No details available for input: ' + place.name;
        return;
      } else {
        this.formMessage.classList.remove('error');
        this.formMessage.innerHTML = '';
      }

      const lat = place.geometry.location.lat();
      const lng = place.geometry.location.lng();

      // Reorder the member list by proximity to the searched location
      this.memberList.sort((a, b) => {
        const distanceA = this.calculateDistance(lat, lng, parseFloat(a.wsmd_geocode.split(',')[0]), parseFloat(a.wsmd_geocode.split(',')[1]));
        const distanceB = this.calculateDistance(lat, lng, parseFloat(b.wsmd_geocode.split(',')[0]), parseFloat(b.wsmd_geocode.split(',')[1]));
        return distanceA - distanceB;
      });

      // Display the reordered member list
      this.memberListOffset = 0;
      this.displayMembers(true);

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
   * Calculate the distance between two points using the Haversine formula
   * @param {number} lat1
   * @param {number} lng1
   * @param {number} lat2
   * @param {number} lng2
   * @returns {number} Distance in kilometers
   */
  static calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // Radius of the Earth in kilometers
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLng / 2) * Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
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

      // Add the marker to the memberIdToMarkerMap
      this.memberIdToMarkerMap.set(member.wsmd_id, marker);

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
          const event = new CustomEvent('wsmd-members-data-ready');
          this.memberList = Object.keys(data.data.members).map(key => ({
            wsmd_id: key,
            ...data.data.members[key]
          }));
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
