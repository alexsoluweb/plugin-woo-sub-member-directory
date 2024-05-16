import '../../scss/member-directory.scss';
import { MarkerClusterer } from "@googlemaps/markerclusterer";
import mapStyles, { svgMarker } from "../map-style";
import '../../scss/tomselect.scss';
import TomSelect from 'tom-select';

class MemberDirectory {
  static memberDirectory = null;
  static map = null;
  static form = null;
  static formMessage = null;
  static markers = [];
  static memberIdToMarkerMap = new Map();
  static memberList = null;
  static memberListOffset = 0;
  static memberListPerPage = 9;
  static activeMarker = null;
  static taxonomiesSelect = null;
  static infoWindow = null;

  static init() {
    this.memberDirectory = document.querySelector('#wsmd-member-directory');
    if (!this.memberDirectory) return;

    this.form = this.memberDirectory.querySelector('#wsmd-form');
    this.formMessage = this.form.querySelector('#wsmd-form-message');
    this.fetchMembersData();
    this.setupEventListeners();
    this.initTomSelect();
  }

  static initTomSelect() {
    const selectElement = document.querySelector('#wsmd_taxonomies');
    if (selectElement) {
      this.taxonomiesSelect = new TomSelect(selectElement, {
        placeholder: selectElement.getAttribute('data-placeholder'),
        allowEmptyOption: true,
        plugins: ['remove_button'],
        onChange: () => {
          this.filterMembersByTaxonomies();
        }
      });
    }
  }

  static setupEventListeners() {
    this.form.addEventListener('submit', (e) => {
      e.preventDefault();
    });

    document.addEventListener('wsmd-members-data-ready', () => {
      this.initGoogleMap();

      if (!this.memberList.length) {
        this.showErrorMessage('No members found');
        this.disableFormInputs();
        return;
      }

      this.randomizeMemberList();
      this.displayMembers();
      this.initMapPlacesService();

      this.memberDirectory.querySelector('#wsmd-member-list-load-more').addEventListener('click', (e) => {
        e.preventDefault();
        this.loadMoreMembers();
      });

      this.form.querySelector('#wsmd-my-location').addEventListener('click', (e) => {
        this.resetMarkerAnimation();
        this.handleSearchNearMe();
      });

      this.memberDirectory.querySelector('#wsmd-member-list').addEventListener('click', (e) => {
        this.resetMarkerAnimation();
        this.handleMemberItemClick(e);
      });
    });
  }

  static filterMembersByTaxonomies() {
    const selectedTaxonomies = this.taxonomiesSelect.getValue().map(Number);
    const filteredMembers = selectedTaxonomies.length === 0
      ? this.memberList
      : this.memberList.filter(member => {
        const memberTaxonomies = member.wsmd_taxonomies || [];
        return selectedTaxonomies.some(taxonomy => memberTaxonomies.includes(taxonomy));
      });

    this.displayMembers(true, filteredMembers);
    this.updateMapMarkers(filteredMembers);
  }

  static handleMemberItemClick(e) {
    const memberItem = e.target.closest('.wsmd-member-item');
    if (memberItem) {
      const memberId = memberItem.dataset.memberId;
      const marker = this.memberIdToMarkerMap.get(memberId);
      if (marker) {
        this.map.panTo(marker.getPosition());
        this.map.setZoom(12);
        this.memberDirectory.querySelector('#wsmd-map').scrollIntoView({ behavior: 'smooth' });
        marker.setAnimation(google.maps.Animation.BOUNCE);
        this.activeMarker = marker;
      }
    }
  }

  static resetMarkerAnimation() {
    if (this.activeMarker) {
      this.activeMarker.setAnimation(null);
    }
  }

  static randomizeMemberList() {
    this.memberList.sort(() => Math.random() - 0.5);
  }

  static displayMembers(reset = false, filteredMembers = null) {
    const memberList = this.memberDirectory.querySelector('#wsmd-member-list');
    if (reset) {
      memberList.innerHTML = '';
      this.memberListOffset = 0;
    }

    const members = filteredMembers || this.memberList;
    const start = this.memberListOffset;
    const end = this.memberListOffset + this.memberListPerPage;
    const membersToDisplay = members.slice(start, end);

    membersToDisplay.forEach((member, index) => {
      const memberItem = this.createMemberItem(member, index);
      memberList.appendChild(memberItem);

      setTimeout(() => {
        memberItem.style.opacity = '1';
        memberItem.style.transform = 'translateY(0)';
        memberItem.style.transitionDelay = `${index * 0.1}s`;
      }, 1);
    });

    this.memberListOffset += this.memberListPerPage;
    this.toggleLoadMoreButton(members);
  }

  static createMemberItem(member) {
    const memberItem = document.createElement('div');
    memberItem.classList.add('wsmd-member-item');
    memberItem.dataset.memberId = member.wsmd_id;
    memberItem.style.opacity = '0';
    memberItem.style.transform = 'translateY(100px)';
    memberItem.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

    let content = `
      <div class="wsmd-member-item-header">
        <h3 class="wsmd-member-item-company">${member.wsmd_company}</h3>
        <p class="wsmd-member-item-occupation">${member.wsmd_occupation}</p>
      </div>
      <hr>
      <div class="wsmd-member-item-body">
        <div class="wsmd-member-item-address">
          <marker class="wsmd-icon-map-marker"></marker>
          ${member.wsmd_address}, ${member.wsmd_city}, ${member.wsmd_province_state}, ${member.wsmd_country}, ${member.wsmd_postal_zip_code}
        </div>`;

    if (member.wsmd_website) {
      content += `
        <div class="wsmd-member-item-website">
          <marker class="wsmd-icon-external-link"></marker>
          ${member.wsmd_website}
        </div>`;
    }
    if (member.wsmd_phone) {
      content += `
        <div class="wsmd-member-item-phone">
          <marker class="wsmd-icon-phone"></marker>
          ${member.wsmd_phone}
        </div>`;
    }
    if (member.wsmd_email) {
      content += `
        <div class="wsmd-member-item-email">
          <marker class="wsmd-icon-email"></marker>
          ${member.wsmd_email}
        </div>`;
    }

    content += '</div>';
    memberItem.innerHTML = content;

    return memberItem;
  }

  static toggleLoadMoreButton(members) {
    const loadMoreButton = this.memberDirectory.querySelector('#wsmd-member-list-load-more');
    loadMoreButton.style.display = this.memberListOffset >= members.length ? 'none' : 'block';
  }

  static loadMoreMembers() {
    this.displayMembers();
  }

  static handleSearchNearMe() {
    this.form.classList.add('loading');
    this.clearErrorMessage();

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => this.handleGeolocationSuccess(position),
        () => this.showErrorMessage('Error: Navigation geolocation failed. Please enable location services and try again.')
      );
    } else {
      this.showErrorMessage('Error: Your browser doesn\'t support geolocation.');
    }
  }

  static handleGeolocationSuccess(position) {
    const userLocation = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    };

    this.sortMemberListByDistance(userLocation.lat, userLocation.lng);
    this.displayMembers(true);

    const nearestMarker = this.getNearestMarker(userLocation.lat, userLocation.lng);
    this.map.panTo(nearestMarker ? nearestMarker.getPosition() : userLocation);
    this.map.setZoom(12);
    this.form.classList.remove('loading');
  }

  static clearErrorMessage() {
    this.formMessage.innerHTML = '';
    this.formMessage.classList.remove('error');
  }

  static showErrorMessage(message) {
    this.formMessage.innerHTML = message;
    this.formMessage.classList.add('error');
    this.form.classList.remove('loading');
  }

  static sortMemberListByDistance(lat, lng) {
    this.memberList.sort((a, b) => {
      const distanceA = this.calculateDistance(lat, lng, parseFloat(a.wsmd_geocode.split(',')[0]), parseFloat(a.wsmd_geocode.split(',')[1]));
      const distanceB = this.calculateDistance(lat, lng, parseFloat(b.wsmd_geocode.split(',')[0]), parseFloat(b.wsmd_geocode.split(',')[1]));
      return distanceA - distanceB;
    });
  }

  static initMapPlacesService() {
    const input = this.form.querySelector('#wsmd-search-address');
    const autocomplete = new google.maps.places.Autocomplete(input);

    autocomplete.addListener('place_changed', () => {
      this.resetMarkerAnimation();
      this.clearErrorMessage();
      const place = autocomplete.getPlace();

      if (!place.geometry) {
        this.showErrorMessage('No details available for input: ' + place.name);
        return;
      }

      const lat = place.geometry.location.lat();
      const lng = place.geometry.location.lng();

      this.sortMemberListByDistance(lat, lng);
      this.displayMembers(true);

      const nearestMarker = this.getNearestMarker(lat, lng);
      this.map.panTo(nearestMarker ? nearestMarker.getPosition() : { lat, lng });
      this.map.setZoom(12);
    });
  }

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

  static initGoogleMap() {
    this.map = new google.maps.Map(document.querySelector('#wsmd-map'), {
      center: { lat: 0, lng: 0 },
      zoom: 2,
      styles: mapStyles,
      mapTypeControlOptions: {
        mapTypeIds: ['roadmap']
      }
    });

    this.infoWindow = new google.maps.InfoWindow();
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
        this.openInfoWindow(this.infoWindow, marker, member);
      });

      return marker;
    });

    this.updateClusterAndBounds(bounds);
  }

  static updateMapMarkers(filteredMembers = null) {
    const members = filteredMembers || this.memberList;
    const bounds = new google.maps.LatLngBounds();

    this.markers.forEach(marker => marker.setMap(null));
    this.markers = [];

    this.markers = members.map(member => {
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
        this.openInfoWindow(this.infoWindow, marker, member);
      });

      return marker;
    });

    this.updateClusterAndBounds(bounds);
  }

  static updateClusterAndBounds(bounds) {
    if (this.markers.length > 1) {
      new MarkerClusterer({ markers: this.markers, map: this.map });
      this.map.fitBounds(bounds);

      google.maps.event.addListenerOnce(this.map, 'idle', () => {
        if (this.map.getZoom() > 12) {
          this.map.setZoom(12);
        }
      });
    } else if (this.markers.length === 1) {
      this.map.setCenter(this.markers[0].getPosition());
      this.map.setZoom(12);
    }
  }

  static openInfoWindow(infoWindow, marker, member) {
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
    infoWindow.open(this.map, marker);
  }

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

  static disableFormInputs() {
    this.form.querySelector('#wsmd-my-location').setAttribute('disabled', 'disabled');
    this.form.querySelector('#wsmd-search-address').setAttribute('disabled', 'disabled');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  MemberDirectory.init();
});
