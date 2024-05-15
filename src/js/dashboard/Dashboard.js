import '../../scss/dashboard.scss';
import mapStyles, { svgMarker } from '../map-style';
import '../../scss/tomselect.scss';
import TomSelect from 'tom-select';

class Dashboard {
  /** @type {HTMLFormElement} */
  static form;
  /** @type {HTMLElement} */
  static formMessage;
  /** @type {google.maps.Map} */
  static map;
  /** @type {google.maps.Marker} */
  static marker;

  /**
   * Initialize the dashboard
   */
  static init() {
    const dashboard = document.querySelector('#wsmd-dashboard');
    if (!dashboard) return;

    this.form = dashboard.querySelector('#wsmd-form');
    this.formMessage = this.form.querySelector('#wsmd-form-message');

    const userLat = parseFloat(this.form.querySelector('input[name="wsmd_geocode"]').value.split(',')[0]) || 46.8139;
    const userLng = parseFloat(this.form.querySelector('input[name="wsmd_geocode"]').value.split(',')[1]) || -71.2080;

    this.form.querySelector('#wsmd-map').classList.add('init');

    this.map = this.createMap(userLat, userLng, this.form);
    this.marker = this.createMarker(userLat, userLng, this.map);

    this.initializeTomSelect();
    this.addEventListeners();
  }

  /**
   * Add event listeners
   */
  static addEventListeners() {

    // Save settings
    this.form.querySelector('#wsmd-save-settings').addEventListener('click', (e) => {
      e.preventDefault();
      this.saveSettings();
    });
  }

  /**
   * Save settings
   * @returns {void}
   */
  static saveSettings() {
    this.formMessage.innerHTML = '';
    this.formMessage.className = '';
    this.formMessage.classList.add('loading');

    // Ensure all address fields are filled
    const requiredFields = [
      'wsmd_address',
      'wsmd_city',
      'wsmd_province_state',
      'wsmd_country',
      'wsmd_postal_zip_code',
    ];

    const missingFields = requiredFields.filter(field => !this.form.querySelector(`input[name="${field}"]`).value.trim());
    if (missingFields.length > 0) {
      this.showMessage(this.formMessage, 'error', 'Please fill out all address fields.');
      return;
    }

    const data = new FormData(this.form);
    const ajaxUrl = this.form.getAttribute('action');

    fetch(ajaxUrl, {
      method: 'POST',
      body: data,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.showMessage(this.formMessage, 'success', data.data.message);
          const { lat, lng } = data.data.geocode;
          const newLocation = { lat: parseFloat(lat), lng: parseFloat(lng) };
          this.form.querySelector('input[name="wsmd_geocode"]').value = `${lat}, ${lng}`;
          this.map.setCenter(newLocation);
          this.marker.setPosition(newLocation);
        } else {
          this.showMessage(this.formMessage, 'error', data.data.message);
        }
      })
      .catch((error) => {
        this.showMessage(this.formMessage, 'error', error);
      });
  }

  /**
   * Create Google Map
   * @param {number} lat - Latitude
   * @param {number} lng - Longitude
   * @param {HTMLElement} form - Form element
   * @returns {google.maps.Map} - Google Map instance
   */
  static createMap(lat, lng, form) {
    return new google.maps.Map(form.querySelector('#wsmd-map'), {
      center: { lat, lng },
      zoom: 6,
      styles: mapStyles,
    });
  }

  /**
   * Create Google Map Marker
   * @param {number} lat - Latitude
   * @param {number} lng - Longitude
   * @param {google.maps.Map} map - Google Map instance
   * @returns {google.maps.Marker} - Google Map Marker instance
   */
  static createMarker(lat, lng, map) {
    return new google.maps.Marker({
      position: { lat, lng },
      map: map,
    });
  }

  /**
   * Display a message
   * @param {HTMLElement} element - Element to display the message in
   * @param {string} type - Type of message ('success' or 'error')
   * @param {string} message - Message to display
   */
  static showMessage(element, type, message) {
    element.classList.remove('loading');
    element.classList.add(type);
    element.innerHTML = message;
  }

  /**
   * Initialize Tom Select
   */
  static initializeTomSelect() {
    /** @type {HTMLSelectElement} */
    const selectElement = document.querySelector('#wsmd_taxonomies');
    if (selectElement) {
      new TomSelect(selectElement, {
        placeholder: selectElement.getAttribute('data-placeholder'),
        allowEmptyOption: true,
        plugins: ['remove_button'],
      });
    }
  }
}

// Main entry point
document.addEventListener('DOMContentLoaded', () => {
  Dashboard.init();
});
