import { displayMembers } from './displayMembers';

/**
 * Load more members
 * @param {Object} context - The context (MemberDirectory instance)
 * @returns {void}
 */
export const loadMoreMembers = (context) => {
  displayMembers(context, false);
};
