export default class GoogleMap {
  static init() {

    // Get the dashboard element
    const dashboard = document.querySelector('#wsmd-dashboard');

    // Check if the dashboard exists
    if (!dashboard) {
      return;
    }

    // Get the form element
    const form = dashboard.querySelector('#wsmd-form');
    const formMessage = form.querySelector('#wsmd-form-message');

    // Retrieve user coordinates
    let userLat = parseFloat(form.querySelector('input[name="wsmd_geocode"]').value.split(',')[0]) || 46.8139;
    let userLng = parseFloat(form.querySelector('input[name="wsmd_geocode"]').value.split(',')[1]) || -71.2080;

    // Add init class to the map container
    form.querySelector('#wsmd-map').classList.add('init');

    // Create a map object and specify the DOM element for display.
    let map = new google.maps.Map(form.querySelector('#wsmd-map'), {
      center: { lat: userLat, lng: userLng },
      zoom: 6
    });

    // Create a marker and set its position.
    let marker = new google.maps.Marker({
      position: { lat: userLat, lng: userLng },
      map: map,
      draggable: true
    });

    // Add a listener for the marker drag
    google.maps.event.addListener(marker, 'dragend', function () {
      form.querySelector('input[name="wsmd_geocode"]').value = marker.getPosition().lat() + ', ' + marker.getPosition().lng();
    });

    // Geocode address
    form.querySelector('#wsmd-geocode-address').addEventListener('click', function (e) {
      e.preventDefault();
      formMessage.innerHTML = '';
      formMessage.className = '';
      formMessage.classList.add('loading');
      let geocoder = new google.maps.Geocoder();
      const address = form.querySelector('input[name="wsmd_address"]').value;
      const city = form.querySelector('input[name="wsmd_city"]').value;
      const province_state = form.querySelector('input[name="wsmd_province_state"]').value;
      const country = form.querySelector('input[name="wsmd_country"]').value;
      const postal_zip_code = form.querySelector('input[name="wsmd_postal_zip_code"]').value;
      const full_address = `${address}, ${city}, ${province_state}, ${country}, ${postal_zip_code}`;

      
      geocoder.geocode({ 'address': full_address }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          map.setCenter(results[0].geometry.location);
          marker.setPosition(results[0].geometry.location);
          form.querySelector('input[name="wsmd_geocode"]').value = results[0].geometry.location.lat() + ', ' + results[0].geometry.location.lng();
          formMessage.classList.remove('loading');
          formMessage.classList.add('success');
          formMessage.innerHTML = 'Geocode was successful.';
        } else {
          formMessage.classList.remove('loading');
          formMessage.classList.add('error');
          formMessage.innerHTML = 'Geocode was not successful for the following reason: ' + status;
        }
      });

    });

    // Save settings
    form.querySelector('#wsmd-save-settings').addEventListener('click', function (e) {
      e.preventDefault();
      formMessage.innerHTML = '';
      formMessage.className = '';
      formMessage.classList.add('loading');
      const data = new FormData(form);
      const ajaxUrl = form.getAttribute('action');

      fetch(ajaxUrl, {
        method: 'POST',
        body: data
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          formMessage.classList.remove('loading');
          formMessage.classList.add('success');
          formMessage.innerHTML = data.data.message;
        } else {
          formMessage.classList.remove('loading');
          formMessage.classList.add('error');
          formMessage.innerHTML = data.data.message;
        }
      })
      .catch((error) => {
        formMessage.classList.remove('loading');
        formMessage.classList.add('error');
        formMessage.innerHTML = error;
      });
    });
  }
}