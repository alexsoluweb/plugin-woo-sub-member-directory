/**
 * Filter members by selected taxonomies
 * @param {Object} context - The context (MemberDirectory instance)
 * @returns {void}
 */
export const filterMembersByTaxonomies = (context) => {
  const selectedTaxonomies = context.taxonomiesSelect.getValue().map(Number);

  if (selectedTaxonomies.length === 0) {
    context.displayedMembers = context.memberList;
  } else {
    context.displayedMembers = context.memberList.filter(member => {
      const memberTaxonomies = member.wsmd_taxonomies || [];
      return selectedTaxonomies.some(taxonomy => memberTaxonomies.includes(taxonomy));
    });
  }

  if (context.displayedMembers.length === 0) {
    context.memberDirectory.querySelector('#wsmd-member-list-container').innerHTML = context.memberDirectory.getAttribute('data-no-members-found-msg');
    context.memberDirectory.querySelector('#wsmd-member-list-load-more').style.display = 'none';
    context.markers.forEach(marker => marker.setVisible(false));
    context.updateMarkerClusterer(new google.maps.LatLngBounds());
    context.markerClusterer = null;
    return;
  }

  context.displayMembers(true, context.displayedMembers);
  context.updateMapMarkers(context.displayedMembers);
};
