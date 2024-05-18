import mapStyles, { svgMarker } from "../../map-style";
import { updateMarkerClusterer, openInfoWindow } from './markerUtils';

/**
 * Initialize the Google Map
 * @param {Object} context - The context (MemberDirectory instance)
 * @returns {void}
 */
export const initGoogleMap = (context) => {

  // Initialize the map
  context.map = new google.maps.Map(document.querySelector('#wsmd-map'), {
    styles: mapStyles,
    mapTypeControlOptions: {
      mapTypeIds: ['roadmap']
    }
  });

  const bounds = new google.maps.LatLngBounds();

  // Set markers
  context.markers = context.memberList.map(member => {
    const marker = new google.maps.Marker({
      position: {
        lat: parseFloat(member.wsmd_geocode.split(',')[0]),
        lng: parseFloat(member.wsmd_geocode.split(',')[1]),
      },
      map: context.map,
      title: member.wsmd_company,
      icon: svgMarker,
    });

    context.memberIdToMarkerMap.set(member.wsmd_id, marker);
    bounds.extend(marker.getPosition());

    marker.addListener('click', () => {
      openInfoWindow(context.infoWindow, marker, member);
    });

    return marker;
  });

  updateMarkerClusterer(context, bounds);
};
