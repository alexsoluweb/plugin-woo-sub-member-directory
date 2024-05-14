import '../../scss/member-directory.scss';
import { MarkerClusterer } from "@googlemaps/markerclusterer";
import mapStyles, { svgMarker } from "../map-style";

class MemberDirectory {
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
  static activeMarker = null;

  /**
   * Initialize the application
   * @returns {void}
   */
  static init() {
    this.memberDirectory = document.querySelector('#wsmd-member-directory');

    if (!this.memberDirectory) return;

    this.form = this.memberDirectory.querySelector('#wsmd-form');
    this.formMessage = this.form.querySelector('#wsmd-form-message');
    this.fetchMembersData();
    this.setupEventListeners();
  }

  /**
   * Set up event listeners
   * @returns {void}
   */
  static setupEventListeners() {
    document.addEventListener('wsmd-members-data-ready', () => {
      this.randomizeMemberList();
      this.displayMembers();
      this.initGoogleMap();
      this.initMapPlacesService();
    });

    this.memberDirectory.querySelector('#wsmd-member-list-load-more').addEventListener('click', (e) => {
      e.preventDefault();
      this.loadMoreMembers();
    });

    this.form.querySelector('#wsmd-my-location').addEventListener('click', (e) => {
      e.preventDefault();
      this.handleSearchNearMe();
    });

    this.form.addEventListener('submit', (e) => {
      e.preventDefault();
    });

    this.memberDirectory.querySelector('#wsmd-member-list').addEventListener('click', (e) => {
      this.handleMemberItemClick(e);
    });
  }

  /**
   * Handle member item click to center map on marker
   * @param {Event} e
   * @returns {void}
   */
  static handleMemberItemClick(e) {
    const memberItem = e.target.closest('.wsmd-member-item');
    if (memberItem) {
      const memberId = memberItem.dataset.memberId;
      const marker = this.memberIdToMarkerMap.get(memberId);
      if (marker) {
        this.map.panTo(marker.getPosition());
        this.map.setZoom(12);
        window.scrollTo({ top: this.memberDirectory.querySelector('#wsmd-map').offsetTop, behavior: 'smooth' });

        if (this.activeMarker) {
          this.activeMarker.setAnimation(null);
        }
        marker.setAnimation(google.maps.Animation.BOUNCE);
        this.activeMarker = marker;
      }
    }
  }

  /**
   * Randomize the member list
   * @returns {void}
   */
  static randomizeMemberList() {
    this.memberList.sort(() => Math.random() - 0.5);
  }

  /**
   * Display the member list
   * @param {boolean} reset - Reset the list
   * @returns {void}
   */
  static displayMembers(reset = false) {
    const memberList = this.memberDirectory.querySelector('#wsmd-member-list');
    const start = this.memberListOffset;
    const end = this.memberListOffset + this.memberListPerPage;
    const membersToDisplay = this.memberList.slice(start, end);

    if (reset) {
      memberList.innerHTML = '';
    }

    membersToDisplay.forEach((member, index) => {
      const memberItem = this.createMemberItem(member, index);
      memberList.appendChild(memberItem);

      // Trigger reflow for the animation to start
      requestAnimationFrame(() => {
        memberItem.style.opacity = '1';
        memberItem.style.transform = 'translateY(0)';
      });
    });

    this.memberListOffset += this.memberListPerPage;
    this.toggleLoadMoreButton();
  }

  /**
   * Create a member item element
   * @param {Object} member
   * @param {number} index
   * @returns {HTMLElement}
   */
  static createMemberItem(member, index) {
    const memberItem = document.createElement('div');
    memberItem.classList.add('wsmd-member-item');
    memberItem.dataset.memberId = member.wsmd_id;
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

    return memberItem;
  }

  /**
   * Toggle the visibility of the load more button
   * @returns {void}
   */
  static toggleLoadMoreButton() {
    const loadMoreButton = this.memberDirectory.querySelector('#wsmd-member-list-load-more');
    loadMoreButton.style.display = this.memberListOffset >= this.memberList.length ? 'none' : 'block';
  }

  /**
   * Load more members
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
    this.clearErrorMessage();

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => this.handleGeolocationSuccess(position),
        () => this.showErrorMessage('Error: The Geolocation service failed.'),
      );
    } else {
      this.showErrorMessage('Error: Your browser doesn\'t support geolocation.');
    }
  }

  /**
   * Handle successful geolocation
   * @param {GeolocationPosition} position
   * @returns {void}
   */
  static handleGeolocationSuccess(position) {
    const userLocation = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    };

    this.sortMemberListByDistance(userLocation.lat, userLocation.lng);
    this.memberListOffset = 0;
    this.displayMembers(true);

    const nearestMarker = this.getNearestMarker(userLocation.lat, userLocation.lng);
    this.map.panTo(nearestMarker ? nearestMarker.getPosition() : userLocation);
    this.map.setZoom(12);
    this.form.classList.remove('loading');
  }

  /**
   * Clear error message
   * @returns {void}
   */
  static clearErrorMessage() {
    this.formMessage.innerHTML = '';
    this.formMessage.classList.remove('error');
  }

  /**
   * Show error message
   * @param {string} message
   * @returns {void}
   */
  static showErrorMessage(message) {
    this.formMessage.innerHTML = message;
    this.formMessage.classList.add('error');
    this.form.classList.remove('loading');
  }

  /**
   * Sort member list by distance
   * @param {number} lat
   * @param {number} lng
   * @returns {void}
   */
  static sortMemberListByDistance(lat, lng) {
    this.memberList.sort((a, b) => {
      const distanceA = this.calculateDistance(lat, lng, parseFloat(a.wsmd_geocode.split(',')[0]), parseFloat(a.wsmd_geocode.split(',')[1]));
      const distanceB = this.calculateDistance(lat, lng, parseFloat(b.wsmd_geocode.split(',')[0]), parseFloat(b.wsmd_geocode.split(',')[1]));
      return distanceA - distanceB;
    });
  }

  /**
   * Initialize the Google Map Places Service
   * @returns {void}
   */
  static initMapPlacesService() {
    const input = this.form.querySelector('#wsmd-search-address');
    const autocomplete = new google.maps.places.Autocomplete(input);
    
    autocomplete.addListener('place_changed', () => {
      this.clearErrorMessage();
      const place = autocomplete.getPlace();

      if (!place.geometry) {
        this.showErrorMessage('No details available for input: ' + place.name);
        return;
      }

      const lat = place.geometry.location.lat();
      const lng = place.geometry.location.lng();

      this.sortMemberListByDistance(lat, lng);
      this.memberListOffset = 0;
      this.displayMembers(true);

      const nearestMarker = this.getNearestMarker(lat, lng);
      this.map.panTo(nearestMarker ? nearestMarker.getPosition() : { lat, lng });
      this.map.setZoom(12);
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
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
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
    this.map = new google.maps.Map(document.querySelector('#wsmd-map'), {
      center: { lat: 0, lng: 0 },
      zoom: 2,
      styles: mapStyles,
      mapTypeControlOptions: {
        mapTypeIds: ['roadmap']
      }
    });

    const infoWindow = new google.maps.InfoWindow();
    const bounds = new google.maps.LatLngBounds();

    this.markers = this.memberList.map(member => {
      const marker = new google.maps.Marker({
        position: {
          lat: parseFloat(member.wsmd_geocode.split(',')[0]),
          lng: parseFloat(member.wsmd_geocode.split(',')[1]),
        },
        map: this.map,
        title: member.wsmd_company,
        icon: svgMarker,
      });

      this.memberIdToMarkerMap.set(member.wsmd_id, marker);
      bounds.extend(marker.getPosition());

      marker.addListener('click', () => {
        this.openInfoWindow(infoWindow, marker, member);
      });

      return marker;
    });

    new MarkerClusterer({ markers: this.markers, map: this.map });
    this.map.fitBounds(bounds);
  }

  /**
   * Open info window with member details
   * @param {google.maps.InfoWindow} infoWindow
   * @param {google.maps.Marker} marker
   * @param {Object} member
   * @returns {void}
   */
  static openInfoWindow(infoWindow, marker, member) {
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
    infoWindow.open(this.map, marker);
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
          this.showErrorMessage(data.data.message);
        }
      })
      .catch(() => {
        this.showErrorMessage('An error occurred while fetching members data');
      });
  }
}

// Main entry point
document.addEventListener('DOMContentLoaded', () => {
  MemberDirectory.init();
});
