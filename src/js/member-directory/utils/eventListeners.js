import { initGoogleMap } from './googleMap';
import { displayMembers } from './displayMembers';
import { loadMoreMembers } from './loadMoreMembers';
import { handleMemberItemClick } from './handleMemberItemClick';
import { handleSearchNearMe } from './location';
import { resetMarkerAnimation } from './markerUtils';

/**
 * Set up event listeners
 * @param {Object} context - The context (MemberDirectory instance)
 * @returns {void}
 */
export const setupEventListeners = (context) => {

  // Prevent form submission
  context.form.addEventListener('submit', (e) => {
    e.preventDefault();
  });

  // When members data is ready
  document.addEventListener('wsmd-members-data-ready', () => {
    initGoogleMap(context);

    // If no members found
    if (!context.memberList.length) {
      context.memberDirectory.querySelector('#wsmd-member-list-container').innerHTML = context.memberDirectory.getAttribute('data-no-members-found-msg');
      context.disableFormInputs();
      return;
    }

    // Display members
    context.randomizeMemberList();
    displayMembers(context);
    context.initMapPlacesService();

    // Load more members
    context.memberDirectory.querySelector('#wsmd-member-list-load-more').addEventListener('click', (e) => {
      e.preventDefault();
      loadMoreMembers(context);
    });

    // My location button
    context.form.querySelector('#wsmd-my-location').addEventListener('click', (e) => {
      resetMarkerAnimation(context);
      handleSearchNearMe(context);
    });

    // Member item click
    context.memberDirectory.querySelector('#wsmd-member-list-container').addEventListener('click', (e) => {
      resetMarkerAnimation(context);
      handleMemberItemClick(context, e);
    });
  });
};
