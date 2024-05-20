import '@src/scss/dashboard.scss';
import mapStyles from './mapStyles';
import '@src/scss/tomselect.scss';
import TomSelect from 'tom-select';

class Dashboard
{
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
    static init()
    {
        const dashboard = document.querySelector('#wsmd-dashboard');
        if (!dashboard) return;

        this.form = dashboard.querySelector('#wsmd-form');
        this.formMessage = this.form.querySelector('#wsmd-form-message');
        const geocodeValue = this.form.querySelector('input[name="wsmd_geocode"]').value;
        const userLat = parseFloat(geocodeValue.split(',')[0]) || 46.8139;
        const userLng = parseFloat(geocodeValue.split(',')[1]) || -71.2080;
        this.map = this.createMap(userLat, userLng);

        // Only create marker if geocode is set
        if (geocodeValue) {
            this.marker = this.createMarker(userLat, userLng);
        }

        this.initTomSelect();

        // Save settings listener
        this.form.querySelector('#wsmd-save-settings').addEventListener('click', (e) =>
        {
            e.preventDefault();
            this.saveSettings();
        });
    }

    /**
     * Save settings
     * @returns {void}
     */
    static saveSettings()
    {
        this.formMessage.innerHTML = '';
        this.formMessage.className = '';
        this.formMessage.classList.add('loading');
        const data = new FormData(this.form);
        const ajaxUrl = this.form.getAttribute('action');

        fetch(ajaxUrl, {
            method: 'POST',
            body: data,
        })
            .then((response) => response.json())
            .then((data) =>
            {

                const responseData = data.data;

                if (data.success) {
                    // Show success message
                    this.showMessage(this.formMessage, 'success', responseData.message);

                    // Update the geocode field with validated data from backend
                    const { lat, lng } = responseData.geocode;
                    const newLocation = { lat: parseFloat(lat), lng: parseFloat(lng) };
                    this.form.querySelector('input[name="wsmd_geocode"]').value = `${lat}, ${lng}`;
                    this.map.setCenter(newLocation);

                    // Update the marker position
                    if (!this.marker) {
                        this.marker = this.createMarker(lat, lng);
                    } else {
                        this.marker.setPosition(newLocation);
                    }

                    // Update the address fields with validated data from backend
                    const addressComponents = responseData.address_components;
                    this.form.querySelector('input[name="wsmd_address"]').value = addressComponents.street_address || '';
                    this.form.querySelector('input[name="wsmd_city"]').value = addressComponents.locality || '';
                    this.form.querySelector('input[name="wsmd_province_state"]').value = addressComponents.administrative_area_level_1 || '';
                    this.form.querySelector('input[name="wsmd_country"]').value = addressComponents.country || '';
                    this.form.querySelector('input[name="wsmd_postal_zip_code"]').value = addressComponents.postal_code || '';
                } else {

                    if (responseData.message) {
                        this.showMessage(this.formMessage, 'error', responseData.message);
                    } else if (responseData.field_validation_errors) {
                        const errors = responseData.field_validation_errors;
                        let errorMessages = '';
                        Object.keys(errors).forEach((field) =>
                        {
                            const fieldElement = this.form.querySelector(`input[name="${field}"]`);
                            fieldElement.classList.add('error');
                            fieldElement.setAttribute('title', errors[field]);
                            errorMessages += `<span class="error-field">${errors[field]}</span>`;
                        });
                        this.showMessage(this.formMessage, 'error', errorMessages);
                    }
                }
            })
            .catch((error) =>
            {
                console.error(error);
                this.showMessage(this.formMessage, 'error', error.toString());
            });
    }

    /**
     * Create Google Map
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @returns {google.maps.Map} - Google Map instance
     */
    static createMap(lat, lng)
    {
        return new google.maps.Map(this.form.querySelector('#wsmd-map'), {
            center: { lat, lng },
            zoom: 6,
            styles: mapStyles,
            mapTypeControlOptions: {
                mapTypeIds: ['roadmap']
            }
        });
    }

    /**
     * Create Google Map Marker
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @returns {google.maps.Marker} - Google Map Marker instance
     */
    static createMarker(lat, lng)
    {
        return new google.maps.Marker({
            position: { lat, lng },
            map: this.map,
        });
    }

    /**
     * Display a message
     * @param {HTMLElement} element - Element to display the message in
     * @param {string} type - Type of message ('success' or 'error')
     * @param {string} message - Message to display
     */
    static showMessage(element, type, message)
    {
        element.classList.remove('loading');
        element.classList.add(type);
        element.innerHTML = message;
    }

    /**
     * Initialize Tom Select
     */
    static initTomSelect()
    {
        /** @type {HTMLSelectElement} */
        const selectElement = document.querySelector('#wsmd_taxonomies');
        if (selectElement) {
            new TomSelect(selectElement, {
                optgroupLabelField: 'label',
                allowEmptyOption: true,
                sortField: 'text',
                plugins: ["remove_button"],
            });
        }
    }
}

// Initialize the application 
// This provide Google Maps callback function
// @ts-ignore
window.WSMD = window.WSMD || {};
WSMD.initApp = function ()
{
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () =>
        {
            Dashboard.init();
        });
    } else {
        Dashboard.init();
    }
}; 