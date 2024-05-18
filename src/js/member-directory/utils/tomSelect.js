import '@src/scss/tomselect.scss';
import TomSelect from 'tom-select';
import { filterMembersByTaxonomies } from './filterMembers';

/**
 * Initialize Tom Select
 * @param {Object} context - The context (MemberDirectory instance)
 * @returns {void}
 */
export const initTomSelect = (context) => {
  const selectElement = document.querySelector('#wsmd_taxonomies');
  if (selectElement) {
    context.taxonomiesSelect = new TomSelect(selectElement, {
      placeholder: selectElement.getAttribute('data-placeholder'),
      allowEmptyOption: true,
      plugins: ['remove_button'],
      onChange: () => {
        filterMembersByTaxonomies(context);
      }
    });
  }
};
