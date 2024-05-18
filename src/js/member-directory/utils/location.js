/**
 * Handle the search near me button
 * @param {Object} context - The context (MemberDirectory instance)
 * @returns {void}
 */
export const handleSearchNearMe = (context) => {
  context.form.classList.add('loading');
  context.clearErrorMessage();

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => handleGeolocationSuccess(context, position),
      () => context.showErrorMessage('Error: Navigation geolocation failed. Please enable location services and try again.')
    );
  } else {
    context.showErrorMessage('Error: Your browser doesn\'t support geolocation.');
  }
};

/**
 * Handle successful geolocation
 * @param {Object} context - The context (MemberDirectory instance)
 * @param {GeolocationPosition} position - The geolocation position
 * @returns {void}
 */
const handleGeolocationSuccess = (context, position) => {
  const userLocation = {
    lat: position.coords.latitude,
    lng: position.coords.longitude
  };

  context.sortMemberListByDistance(userLocation.lat, userLocation.lng);
  context.displayMembers(true);

  const nearestMarker = context.getNearestMarker(userLocation.lat, userLocation.lng);
  context.map.panTo(nearestMarker ? nearestMarker.getPosition() : userLocation);
  context.map.setZoom(12);
  context.form.classList.remove('loading');
};


