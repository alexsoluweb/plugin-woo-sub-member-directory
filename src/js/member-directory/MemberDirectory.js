// Import necessary styles
import '@src/scss/member-directory.scss';

// Import services and utilities
import { fetchMembersData } from './services/fetchMembersData';
import { setupEventListeners } from './utils/eventListeners';
import { initTomSelect } from './utils/tomSelect';

class MemberDirectory {
  static memberDirectory = null;
  static map = null;
  static form = null;
  static formMessage = null;
  static markers = [];
  static memberIdToMarkerMap = new Map();
  static memberList = [];
  static displayedMembers = [];
  static memberListOffset = 0;
  static memberListPerPage = 9;
  static activeMarker = null;
  static taxonomiesSelect = null;
  static infoWindow = null;
  static markerClusterer = null;

  /**
   * Initialize the application
   * @returns {void}
   */
  static init() {
    this.memberDirectory = document.querySelector('#wsmd-member-directory');
    if (!this.memberDirectory) return;

    this.form = this.memberDirectory.querySelector('#wsmd-form');
    this.formMessage = this.form.querySelector('#wsmd-form-message');
    this.infoWindow = new google.maps.InfoWindow();

    fetchMembersData(this);
    setupEventListeners(this);
    initTomSelect(this);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  MemberDirectory.init();
});
