import { setMarkerAnimation } from './markerUtils';

/**
 * Handle member item click event
 * @param {Object} context - The context (MemberDirectory instance)
 * @param {MouseEvent} e - The mouse event
 * @returns {void}
 */
export const handleMemberItemClick = (context, e) => {
  const memberItem = e.target.closest('.wsmd-member-item');
  if (memberItem) {
    const memberId = memberItem.dataset.memberId;
    const marker = context.memberIdToMarkerMap.get(memberId);
    if (marker) {
      context.map.panTo(marker.getPosition());
      context.map.setZoom(12);
      context.memberDirectory.querySelector('#wsmd-map').scrollIntoView({ behavior: 'smooth' });
      setMarkerAnimation(context, marker);
    }
  }
};
