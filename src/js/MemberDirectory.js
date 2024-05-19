import "@src/scss/member-directory.scss";
import { MarkerClusterer } from "@googlemaps/markerclusterer";
import mapStyles, { svgMarker } from "./mapStyles";
import "@src/scss/tomselect.scss";
import TomSelect from "tom-select";

class MemberDirectory
{
    /** @type {HTMLDivElement} */
    static memberDirectory = null;
    /** @type {google.maps.Map} */
    static map = null;
    /** @type {HTMLFormElement} */
    static form = null;
    /** @type {HTMLParagraphElement} */
    static formMessage = null;
    /** @type {google.maps.Marker[]} */
    static markers = [];
    /** @type {Map<string, google.maps.Marker>} */
    static memberIdToMarkerMap = null;
    /** @type {Object[]} */
    static memberList = null;
    /** @type {Object[]} */
    static filteredMembers = null;
    /** @type {number} */
    static memberListOffset = 0;
    /** @type {number} */
    static memberListPerPage = 9;
    /** @type {google.maps.Marker} */
    static activeMarker = null;
    /** @type {TomSelect} */
    static taxonomiesSelect = null;
    /** @type {google.maps.InfoWindow} */
    static infoWindow = null;
    /** @type {MarkerClusterer} */
    static markerClusterer = null;
    /** @type {google.maps.places.Autocomplete} */
    static autocomplete = null;
    /** @type {Object[]} */
    static taxonomies = null;
    /** @type {number} */
    static maxShowMoreTags = 6;
    /** @type {string} */
    static filterLogicOperator = "AND";
    /** @type {number} */
    static maxZoomLevel = 16;

    /**
     * Initialize the application
     * @returns {void}
     */
    static init()
    {
        this.memberDirectory = document.querySelector("#wsmd-member-directory");
        if (!this.memberDirectory) return;
        this.form = this.memberDirectory.querySelector("#wsmd-form");
        this.formMessage = this.form.querySelector("#wsmd-form-message");
        this.infoWindow = new google.maps.InfoWindow();
        this.memberIdToMarkerMap = new Map();
        this.autocomplete = new google.maps.places.Autocomplete(this.form.querySelector("#wsmd-search-address"));
        this.taxonomiesSelect = this.initTomSelect();

        // Prevent default form submission
        this.form.addEventListener("submit", (e) =>
        {
            e.preventDefault();
        });

        // Fetch members data and initialize the application
        this.fetchMembersData()
            .then(() =>
            {
                this.initGoogleMap();

                // No members found
                if (!this.memberList.length) {
                    this.displayNBResults();
                    this.disableFormInputs();
                    return;
                }

                this.getTaxonomiesFromSelect();
                this.randomizeMemberList();
                this.displayMembers();
                this.displayNBResults();

                // Autocomplete place changed event
                this.autocomplete.addListener("place_changed", () =>
                {
                    this.resetMarkerAnimation();
                    this.clearErrorMessage();
                    this.handleSearchNearPlace();
                });

                // Load more members
                this.memberDirectory.querySelector("#wsmd-member-list-load-more").addEventListener("click", (e) =>
                {
                    this.clearErrorMessage();
                    this.displayMembers();
                });

                // My location button
                this.form.querySelector("#wsmd-my-location").addEventListener("click", (e) =>
                {
                    this.resetMarkerAnimation();
                    this.clearErrorMessage();
                    this.handleSearchNearMe();
                });

                // Member item click event
                this.memberDirectory.querySelector("#wsmd-member-list-container")
                    .addEventListener("click", /** @param {MouseEvent} event */(event) =>
                    {
                        this.resetMarkerAnimation();
                        this.clearErrorMessage();
                        this.handleMemberItemClick(event);
                    });

                // Taxonomies select change event
                this.taxonomiesSelect.on("change", () =>
                {
                    this.resetMarkerAnimation();
                    this.clearErrorMessage();
                    this.filterMembersByTaxonomies();
                    this.displayNBResults();
                });
            })
            .catch((error) =>
            {
                this.showErrorMessage(error.message);
            });
    }

    /**
     * Display the number of results
     * @returns {void}
     */
    static displayNBResults()
    {
        const nbResultsElement = this.memberDirectory.querySelector("#wsmd-member-list-results");
        const nbResults = this.filteredMembers ? this.filteredMembers.length : this.memberList.length;
        const localizeStrings = JSON.parse(this.memberDirectory.getAttribute('data-localize-strings'));

        if (nbResults === 0) {
            nbResultsElement.innerHTML = localizeStrings.no_results;
        } else if (nbResults === 1) {
            nbResultsElement.innerHTML = '1' + " " + localizeStrings.one_result;
        } else {
            nbResultsElement.innerHTML = nbResults + " " + localizeStrings.multiple_results;
        }
    }

    /**
     * Get taxonomies from the select element
     * @returns {void}
     */
    static getTaxonomiesFromSelect()
    {
        this.taxonomies = Object.keys(this.taxonomiesSelect.options).map((key) =>
        {
            return {
                id: key,
                name: this.taxonomiesSelect.options[key].$option.text,
            };
        });
    }

    /**
     * Initialize Tom Select
     * @returns {TomSelect} - The Tom Select instance or null
     */
    static initTomSelect()
    {
        /** @type {HTMLSelectElement} */
        const selectElement = document.querySelector("#wsmd_taxonomies");
        if (selectElement) {
            return new TomSelect(selectElement, {
                placeholder: selectElement.getAttribute("data-placeholder"),
                allowEmptyOption: true,
                plugins: ["remove_button"],
            });
        }
        return null;
    }

    /**
     * Filter members by selected taxonomies
     * @returns {void}
     */
    static filterMembersByTaxonomies()
    {
        // Get selected taxonomies
        // @ts-ignore
        const selectedTaxonomies = this.taxonomiesSelect.getValue().map(Number);

        // If no taxonomies are selected, show all members
        if (selectedTaxonomies.length === 0) {
            this.filteredMembers = this.memberList;
        } else {
            this.filteredMembers = this.memberList.filter((member) =>
            {
                const memberTaxonomies = member.wsmd_taxonomies || [];
                if (this.filterLogicOperator === "OR") {
                    // OR logic
                    return selectedTaxonomies.some((taxonomy) =>
                        memberTaxonomies.includes(taxonomy)
                    );
                } else {
                    // AND logic
                    return selectedTaxonomies.every((taxonomy) =>
                        memberTaxonomies.includes(taxonomy)
                    );
                }
            });
        }

        this.displayMembers(true);
        this.updateMapMarkers();
    }


    /**
     * Handle member item click event
     * @param {MouseEvent} event - The mouse event
     * @returns {void}
     */
    static handleMemberItemClick(event)
    {
        // Handle "Show More" taxonomies button click
        // @ts-ignore
        if (event.target.classList.contains("wsmd-taxonomies-show-more")) {
            const button = event.target;
            // @ts-ignore
            const remainingTaxonomyIds = button.getAttribute('data-remaining').split(',').map(Number);
            // @ts-ignore
            this.showMoreTaxonomies(button, remainingTaxonomyIds);
            return;
        }

        // Handle member item click
        // @ts-ignore
        if (event.target.closest(".wsmd-member-item")) {
            // @ts-ignore
            const memberItem = event.target.closest(".wsmd-member-item");
            const memberId = memberItem.dataset.memberId;
            const marker = this.memberIdToMarkerMap.get(memberId);
            if (marker) {
                this.map.panTo(marker.getPosition());
                this.map.setZoom(this.maxZoomLevel);
                this.memberDirectory.querySelector("#wsmd-map").scrollIntoView({ behavior: "smooth" });
                marker.setAnimation(google.maps.Animation.BOUNCE);
                this.activeMarker = marker;
            }
        }
    }

    /**
     * Display the member list
     * @param {boolean} reset - Reset the list
     * @returns {void}
     */
    static displayMembers(reset = false)
    {
        const memberList = this.memberDirectory.querySelector("#wsmd-member-list");

        if (reset) {
            memberList.innerHTML = "";
            this.memberListOffset = 0;
        }

        // Use the filtered members if available, otherwise use the member list
        const members = this.filteredMembers || this.memberList;

        const start = this.memberListOffset;
        const end = this.memberListOffset + this.memberListPerPage;
        const membersToDisplay = members.slice(start, end);

        membersToDisplay.forEach((member, index) =>
        {
            const memberItem = this.createMemberItem(member);
            memberList.appendChild(memberItem);

            setTimeout(() =>
            {
                memberItem.style.opacity = "1";
                memberItem.style.transform = "translateY(0)";
                memberItem.style.transitionDelay = `${index * 0.1}s`;
            }, 1);
        });

        this.memberListOffset += this.memberListPerPage;
        this.toggleLoadMoreButton(members);
    }

    /**
     * Create a member item element
     * @param {Object} member - Member data
     * @returns {HTMLElement}
     */
    static createMemberItem(member)
    {
        const memberItem = document.createElement("div");
        memberItem.classList.add("wsmd-member-item");
        memberItem.dataset.memberId = member.wsmd_id;
        memberItem.style.opacity = "0";
        memberItem.style.transform = "translateY(100px)";
        memberItem.style.transition = "opacity 0.5s ease, transform 0.5s ease";

        let content = `
      <div class="wsmd-member-item-header">
        <h3 class="wsmd-member-item-company">${member.wsmd_company}</h3>
        <p class="wsmd-member-item-occupation">${member.wsmd_occupation}</p>
      </div>
      <hr>
      <div class="wsmd-member-item-body">
        <div class="wsmd-member-item-address">
          <marker class="wsmd-icon-map-marker"></marker>
          <span class="wsmd-info">${member.wsmd_address}, ${member.wsmd_city}, ${member.wsmd_province_state}, ${member.wsmd_country}, ${member.wsmd_postal_zip_code}</span>
        </div>`;

        if (member.wsmd_website) {
            content += `
        <div class="wsmd-member-item-website">
          <marker class="wsmd-icon-external-link"></marker>
          <span class="wsmd-info">${member.wsmd_website}</span>
        </div>`;
        }
        if (member.wsmd_phone) {
            content += `
        <div class="wsmd-member-item-phone">
          <marker class="wsmd-icon-phone"></marker>
          <span class="wsmd-info">${member.wsmd_phone}</span>
        </div>`;
        }
        if (member.wsmd_email) {
            content += `
        <div class="wsmd-member-item-email">
          <marker class="wsmd-icon-email"></marker>
          <span class="wsmd-info">${member.wsmd_email}</span>
        </div>`;
        }
        // Show taxonomies
        if (member.wsmd_taxonomies) {
            content += `
        <div class="wsmd-member-item-taxonomies">
          ${this.buildMemberTaxonomies(member.wsmd_taxonomies)}
        </div>`;
        }

        content += `</div>`;

        memberItem.innerHTML = content;

        return memberItem;
    }

    /**
     * Build member taxonomies
     * @param {number[]} taxonomyIds - Taxonomy IDs
     * @returns {string} - The taxonomies HTML
     */
    static buildMemberTaxonomies(taxonomyIds)
    {
        // Limit the number of taxonomy tags to show
        const limitedTaxonomyIds = taxonomyIds.slice(0, this.maxShowMoreTags);
        const remainingTaxonomyIds = taxonomyIds.slice(this.maxShowMoreTags);

        // Create HTML for the first few tags
        const tagsHtml = limitedTaxonomyIds
            .map((taxonomyId) =>
            {
                const taxonomy = this.taxonomies.find((t) => t.id === String(taxonomyId));
                return `<span class="wsmd-taxonomy">${taxonomy ? taxonomy.name : ""}</span>`;
            })
            .join("");

        // If there are more than the limit, add a "Show More" button
        let showMoreButton = '';
        if (remainingTaxonomyIds.length > 0) {
            showMoreButton = `<button class="wsmd-taxonomies-show-more" data-remaining="${remainingTaxonomyIds.join(',')}">[...]</button>`;
        }

        return tagsHtml + showMoreButton;
    }

    /**
     * Show more taxonomies
     * @param {HTMLButtonElement} button - The "Show More" button
     * @param {number[]} remainingTaxonomyIds - The remaining taxonomy IDs
     * @returns {void}
     */
    static showMoreTaxonomies(button, remainingTaxonomyIds)
    {
        // Create HTML for the remaining tags
        const remainingTagsHtml = remainingTaxonomyIds
            .map((taxonomyId) =>
            {
                const taxonomy = this.taxonomies.find((t) => t.id === String(taxonomyId));
                return `<span class="wsmd-taxonomy">${taxonomy ? taxonomy.name : ""}</span>`;
            })
            .join("");

        // Append the remaining tags and remove the "Show More" button
        const parent = button.parentElement;
        if (parent) {
            parent.insertAdjacentHTML("beforeend", remainingTagsHtml);
            button.remove();
        }
    }

    /**
     * Toggle the visibility of the load more button
     * @param {Object[]} members - The members list
     * @returns {void}
     */
    static toggleLoadMoreButton(members)
    {
        /** @type {HTMLButtonElement} */
        const loadMoreButton = this.memberDirectory.querySelector("#wsmd-member-list-load-more");
        loadMoreButton.style.display = this.memberListOffset >= members.length ? "none" : "block";
    }

    /**
     * Handle the search near place button
     * @returns {void}
     */
    static handleSearchNearPlace()
    {
        const place = this.autocomplete.getPlace();

        if (!place.geometry) {
            this.showErrorMessage("No details available for input: " + place.name);
            return;
        }

        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();

        this.sortMemberListByDistance(lat, lng);
        this.displayMembers(true);

        const nearestMarker = this.getNearestMarker(lat, lng);
        this.map.panTo(nearestMarker ? nearestMarker.getPosition() : { lat, lng });
        this.map.setZoom(12);
    }

    /**
     * Handle the search near me button
     * @returns {void}
     */
    static handleSearchNearMe()
    {
        this.showSpinner();

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                // Success callback
                (position) =>
                {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };

                    this.sortMemberListByDistance(userLocation.lat, userLocation.lng);
                    this.displayMembers(true);

                    const nearestMarker = this.getNearestMarker(userLocation.lat, userLocation.lng);
                    this.map.panTo(nearestMarker ? nearestMarker.getPosition() : userLocation);
                    this.map.setZoom(12);
                    this.hideSpinner();
                },
                // Error callback
                () =>
                {
                    this.showErrorMessage(
                        "Error: Navigation geolocation failed. Please enable location services and try again."
                    );
                    this.hideSpinner();
                },
            );
        } else {
            this.showErrorMessage("Error: Your browser doesn't support geolocation.");
        }
    }

    /**
     * Sort member list by distance
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @returns {void}
     */
    static sortMemberListByDistance(lat, lng)
    {
        this.memberList.sort((a, b) =>
        {
            const distanceA = this.calculateDistance(
                lat,
                lng,
                parseFloat(a.wsmd_geocode.split(",")[0]),
                parseFloat(a.wsmd_geocode.split(",")[1])
            );
            const distanceB = this.calculateDistance(
                lat,
                lng,
                parseFloat(b.wsmd_geocode.split(",")[0]),
                parseFloat(b.wsmd_geocode.split(",")[1])
            );
            return distanceA - distanceB;
        });
    }

    /**
     * Get the nearest marker to a given latitude and longitude
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @returns {google.maps.Marker} - The nearest marker
     */
    static getNearestMarker(lat, lng)
    {
        let nearestMarker = null;
        let minDistance = Infinity;

        this.getVisibleMarkers().forEach((marker) =>
        {
            const d = google.maps.geometry.spherical.computeDistanceBetween(
                new google.maps.LatLng(lat, lng),
                marker.getPosition()
            );
            if (d < minDistance) {
                minDistance = d;
                nearestMarker = marker;
            }
        });

        return nearestMarker;
    }

    /**
     * Calculate the distance between two points using the Haversine formula
     * @param {number} lat1 - Latitude of the first point
     * @param {number} lng1 - Longitude of the first point
     * @param {number} lat2 - Latitude of the second point
     * @param {number} lng2 - Longitude of the second point
     * @returns {number} Distance in kilometers
     */
    static calculateDistance(lat1, lng1, lat2, lng2)
    {
        const R = 6371;
        const dLat = ((lat2 - lat1) * Math.PI) / 180;
        const dLng = ((lng2 - lng1) * Math.PI) / 180;
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLng / 2) *
            Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    /**
     * Initialize the Google Map
     * @returns {void}
     */
    static initGoogleMap()
    {
        this.map = new google.maps.Map(document.querySelector("#wsmd-map"), {
            styles: mapStyles,
            mapTypeControlOptions: {
                mapTypeIds: ["roadmap"],
            },
        });

        const bounds = new google.maps.LatLngBounds();

        this.markers = this.memberList.map((member) =>
        {
            const marker = new google.maps.Marker({
                position: {
                    lat: parseFloat(member.wsmd_geocode.split(",")[0]),
                    lng: parseFloat(member.wsmd_geocode.split(",")[1]),
                },
                map: this.map,
                title: member.wsmd_company,
                icon: svgMarker(),
            });

            this.memberIdToMarkerMap.set(member.wsmd_id, marker);
            bounds.extend(marker.getPosition());

            marker.addListener("click", () =>
            {
                this.toggleInfoWindow(marker, member);
            });

            return marker;
        });

        this.updateMarkerClusterer(bounds);
    }

    /**
     * Update map markers based on the filtered members
     * @returns {void}
     */
    static updateMapMarkers()
    {
        const members = this.filteredMembers || this.memberList;
        const bounds = new google.maps.LatLngBounds();

        this.markers.forEach((marker) =>
        {
            marker.setVisible(false);
        });

        members.forEach((member) =>
        {
            const marker = this.memberIdToMarkerMap.get(member.wsmd_id);
            if (marker) {
                marker.setVisible(true);
                bounds.extend(marker.getPosition());
            }
        });

        this.updateMarkerClusterer(bounds);
    }

    /**
     * Update the MarkerClusterer instance and set map bounds
     * @param {google.maps.LatLngBounds} bounds - The bounds to set for the map
     * @returns {void}
     */
    static updateMarkerClusterer(bounds)
    {
        if (this.markerClusterer) {
            this.markerClusterer.clearMarkers();
            this.markerClusterer = null;
        }

        const visibleMarkers = this.getVisibleMarkers();

        if (visibleMarkers.length >= 1) {
            this.markerClusterer = new MarkerClusterer({
                markers: visibleMarkers,
                map: this.map,
            });
            this.map.fitBounds(bounds);

            // Set max zoom level to 16
            google.maps.event.addListenerOnce(this.map, "idle", () =>
            {
                if (this.map.getZoom() > this.maxZoomLevel) {
                    this.map.setZoom(this.maxZoomLevel);
                }
            });
        } else {
            this.map.setCenter({ lat: 0, lng: 0 });
            this.map.setZoom(2);
        }
    }

    /**
     * Toggle info window with member details
     * @param {google.maps.Marker} marker - The marker instance
     * @param {Object} member - Member data
     * @returns {void}
     */
    static toggleInfoWindow(marker, member)
    {
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
            <span class="wsmd-info">${member.wsmd_address}, ${member.wsmd_city}, ${member.wsmd_province_state}, ${member.wsmd_country}, ${member.wsmd_postal_zip_code}</span>
          </span>`;

        if (member.wsmd_website) {
            content += `
        <span class="wsmd-map-info-window-website">
          <marker class="wsmd-icon-external-link"></marker>
          <span class="wsmd-info">${member.wsmd_website}</span>
        </span>`;
        }
        if (member.wsmd_phone) {
            content += `
        <span class="wsmd-map-info-window-phone">
          <marker class="wsmd-icon-phone"></marker>
          <span class="wsmd-info">${member.wsmd_phone}</span>
        </span>`;
        }
        if (member.wsmd_email) {
            content += `
        <span class="wsmd-map-info-window-email">
          <marker class="wsmd-icon-email"></marker>
          <span class="wsmd-info">${member.wsmd_email}</span>
        </span>`;
        }

        content += `
        </div>
      </div>`;

        // Toggle info window
        if (this.infoWindow.getContent() === content) {
            this.infoWindow.setContent("");
            this.infoWindow.close();
        } else {
            this.infoWindow.setContent(content);
            this.infoWindow.open(this.map, marker);
        }
    }

    /**
     * Fetch members data from AJAX endpoint
     * @returns {Promise<void>}
     */
    static async fetchMembersData()
    {
        const data = new FormData(this.form);
        const ajaxUrl = this.form.getAttribute("action");

        try {
            const response = await fetch(ajaxUrl, {
                method: "POST",
                body: data,
            });
            const result = await response.json();

            if (result.success) {
                this.memberList = Object.keys(result.data.members).map((key) => ({
                    wsmd_id: key,
                    ...result.data.members[key],
                }));
                return Promise.resolve();
            } else {
                return Promise.reject(new Error(result.data.message));
            }
        } catch (error) {
            return Promise.reject(
                new Error("An error occurred while fetching members data")
            );
        }
    }

    /**
     * Get visible Markers
     * @returns {google.maps.Marker[]} - The visible markers
     */
    static getVisibleMarkers()
    {
        return this.markers.filter((marker) => marker.getVisible());
    }

    /**
     * Disable form inputs
     * @returns {void}
     */
    static disableFormInputs()
    {
        this.form.querySelector("#wsmd-my-location").setAttribute("disabled", "disabled");
        this.form.querySelector("#wsmd-search-address").setAttribute("disabled", "disabled");
        this.form.querySelector("#wsmd_taxonomies").setAttribute("disabled", "disabled");
    }

    /**
     * Show the spinner
     * @returns {void}
     */
    static showSpinner()
    {
        const spinner = document.getElementById("map-spinner");
        if (spinner) {
            spinner.classList.add("show");
        }
    }

    /**
     * Hide the spinner
     * @returns {void}
     */
    static hideSpinner()
    {
        const spinner = document.getElementById("map-spinner");
        if (spinner) {
            spinner.classList.remove("show");
        }
    }

    /**
     * Clear error message
     * @returns {void}
     */
    static clearErrorMessage()
    {
        this.formMessage.innerHTML = "";
        this.formMessage.classList.remove("error");
    }

    /**
     * Show error message
     * @param {string} message - The error message
     * @returns {void}
     */
    static showErrorMessage(message)
    {
        this.formMessage.innerHTML = message;
        this.formMessage.classList.add("error");
    }

    /**
     * Reset marker animation
     * @returns {void}
     */
    static resetMarkerAnimation()
    {
        if (this.activeMarker) {
            this.activeMarker.setAnimation(null);
        }
    }

    /**
     * Randomize member list
     * @returns {void}
     */
    static randomizeMemberList()
    {
        this.memberList.sort(() => Math.random() - 0.5);
    }
}

// Initialize the application
// This provide Google Maps callback function
// @ts-ignore
window.WSMD = window.WSMD || {};
WSMD.initApp = function ()
{
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () =>
        {
            MemberDirectory.init();
        });
    } else {
        MemberDirectory.init();
    }
};
