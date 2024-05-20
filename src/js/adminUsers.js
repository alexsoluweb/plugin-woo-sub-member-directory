import '@src/scss/tomselect.scss';
import TomSelect from 'tom-select';

// Main entry point
document.addEventListener('DOMContentLoaded', () =>
{
    /** @type {HTMLSelectElement} */
    const selectElement = document.querySelector('#wsmd_taxonomies');
    if (selectElement) {
        new TomSelect(selectElement, {
            optgroupLabelField: 'label',
            allowEmptyOption: true,
            sortField: 'text',
            plugins: ["remove_button"],
        });
    }
});
