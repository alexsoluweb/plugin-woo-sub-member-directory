import '../../scss/dashboard.scss';
import mapStyles, { svgMarker } from '../map-style';
import '@node/tom-select/dist/scss/tom-select.scss';
import TomSelect from 'tom-select';

class Dashboard {
  /**
   * Initialize the dashboard
   */
  static init() {
    const dashboard = document.querySelector('#wsmd-dashboard');
    if (!dashboard) return;

    const form = dashboard.querySelector('#wsmd-form');
    const formMessage = form.querySelector('#wsmd-form-message');

    const userLat = parseFloat(form.querySelector('input[name="wsmd_geocode"]').value.split(',')[0]) || 46.8139;
    const userLng = parseFloat(form.querySelector('input[name="wsmd_geocode"]').value.split(',')[1]) || -71.2080;

    form.querySelector('#wsmd-map').classList.add('init');

    const map = this.createMap(userLat, userLng, form);
    const marker = this.createMarker(userLat, userLng, map);

    this.addMarkerDragListener(marker, form);
    this.addGeocodeAddressListener(form, formMessage, map, marker);
    this.addSaveSettingsListener(form, formMessage);

    this.initializeTomSelect();
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
      draggable: true,
    });
  }

  /**
   * Add marker drag event listener
   * @param {google.maps.Marker} marker - Google Map Marker instance
   * @param {HTMLElement} form - Form element
   */
  static addMarkerDragListener(marker, form) {
    google.maps.event.addListener(marker, 'dragend', () => {
      form.querySelector('input[name="wsmd_geocode"]').value = `${marker.getPosition().lat()}, ${marker.getPosition().lng()}`;
    });
  }

  /**
   * Add geocode address button event listener
   * @param {HTMLElement} form - Form element
   * @param {HTMLElement} formMessage - Form message element
   * @param {google.maps.Map} map - Google Map instance
   * @param {google.maps.Marker} marker - Google Map Marker instance
   */
  static addGeocodeAddressListener(form, formMessage, map, marker) {
    form.querySelector('#wsmd-geocode-address').addEventListener('click', (e) => {
      e.preventDefault();
      formMessage.innerHTML = '';
      formMessage.className = '';
      formMessage.classList.add('loading');

      const addressComponents = [
        form.querySelector('input[name="wsmd_address"]').value,
        form.querySelector('input[name="wsmd_city"]').value,
        form.querySelector('input[name="wsmd_province_state"]').value,
        form.querySelector('input[name="wsmd_country"]').value,
        form.querySelector('input[name="wsmd_postal_zip_code"]').value,
      ];
      const fullAddress = addressComponents.filter(Boolean).join(', ');

      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: fullAddress }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK) {
          const location = results[0].geometry.location;
          map.setCenter(location);
          marker.setPosition(location);
          form.querySelector('input[name="wsmd_geocode"]').value = `${location.lat()}, ${location.lng()}`;
          this.showMessage(formMessage, 'success', 'Geocode was successful.');
        } else {
          this.showMessage(formMessage, 'error', `Geocode was not successful for the following reason: ${status}`);
        }
      });
    });
  }

  /**
   * Add save settings button event listener
   * @param {HTMLElement} form - Form element
   * @param {HTMLElement} formMessage - Form message element
   */
  static addSaveSettingsListener(form, formMessage) {
    form.querySelector('#wsmd-save-settings').addEventListener('click', (e) => {
      e.preventDefault();
      formMessage.innerHTML = '';
      formMessage.className = '';
      formMessage.classList.add('loading');

      const data = new FormData(form);
      const ajaxUrl = form.getAttribute('action');

      fetch(ajaxUrl, {
        method: 'POST',
        body: data,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            this.showMessage(formMessage, 'success', data.data.message);
          } else {
            this.showMessage(formMessage, 'error', data.data.message);
          }
        })
        .catch((error) => {
          this.showMessage(formMessage, 'error', error);
        });
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
