/**
 * Fetch members data from AJAX endpoint
 * @param {Object} context - The context (MemberDirectory instance)
 * @returns {void}
 */
export const fetchMembersData = (context) => {
  const data = new FormData(context.form);
  const ajaxUrl = context.form.getAttribute('action');

  fetch(ajaxUrl, {
    method: 'POST',
    body: data,
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const event = new CustomEvent('wsmd-members-data-ready');
        context.memberList = Object.keys(data.data.members).map(key => ({
          wsmd_id: key,
          ...data.data.members[key]
        }));
        context.displayedMembers = context.memberList.slice(); // Initially, displayed members are the same as the member list
        document.dispatchEvent(event);
      } else {
        context.showErrorMessage(data.data.message);
      }
    })
    .catch(() => {
      context.showErrorMessage('An error occurred while fetching members data');
    });
};
