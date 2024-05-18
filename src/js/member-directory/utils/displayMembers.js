/**
 * Display the member list
 * @param {Object} context - The context (MemberDirectory instance)
 * @param {boolean} reset - Reset the list
 * @returns {void}
 */
export const displayMembers = (context, reset = false) => {
  const memberListContainer = context.memberDirectory.querySelector('#wsmd-member-list-container');
  const memberList = context.memberDirectory.querySelector('#wsmd-member-list');

  if (reset) {
    memberList.innerHTML = '';
    context.memberListOffset = 0;
  }

  const members = context.displayedMembers;

  // Clear the results number
  const resultsElement = memberListContainer.querySelector('.wsmd-member-list-results');
  if (resultsElement) {
    resultsElement.remove();
  }

  // Add the results number to the member list container
  const resultElement = document.createElement('div');
  resultElement.classList.add('wsmd-member-list-results');
  resultElement.innerHTML = `${members.length} results`;
  memberListContainer.prepend(resultElement);
  
  const start = context.memberListOffset;
  const end = context.memberListOffset + context.memberListPerPage;
  const membersToDisplay = members.slice(start, end);

  membersToDisplay.forEach((member, index) => {
    const memberItem = createMemberItem(context, member);
    memberList.appendChild(memberItem);

    setTimeout(() => {
      memberItem.style.opacity = '1';
      memberItem.style.transform = 'translateY(0)';
      memberItem.style.transitionDelay = `${index * 0.1}s`;
    }, 1);
  });

  context.memberListOffset += context.memberListPerPage;
  toggleLoadMoreButton(context, members);
};

/**
 * Create a member item element
 * @param {Object} context - The context (MemberDirectory instance)
 * @param {Object} member - Member data
 * @returns {HTMLElement}
 */
const createMemberItem = (context, member) => {
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
  // Show taxonomies
  if (member.wsmd_taxonomies) {
    content += `
      <div class="wsmd-member-item-taxonomies">
        <marker class="wsmd-icon-tag"></marker>
        ${member.wsmd_taxonomies.map(taxonomy => `<span class="wsmd-member-item-taxonomy">${taxonomy}</span>`).join('')}
      </div>`;
  }

  content += `</div>`;

  memberItem.innerHTML = content;

  return memberItem;
};

/**
 * Toggle the visibility of the load more button
 * @param {Object} context - The context (MemberDirectory instance)
 * @param {Object[]} members - The members list
 * @returns {void}
 */
const toggleLoadMoreButton = (context, members) => {
  const loadMoreButton = context.memberDirectory.querySelector('#wsmd-member-list-load-more');
  loadMoreButton.style.display = context.memberListOffset >= members.length ? 'none' : 'block';
};
