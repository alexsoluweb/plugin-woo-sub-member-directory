import '@node/tom-select/dist/scss/tom-select.scss';
import TomSelect from 'tom-select';

// Main entry point
document.addEventListener('DOMContentLoaded', () => {

	/** @type {HTMLSelectElement} */
	const selectElement = document.querySelector('#wsmd_taxonomies');
    if (selectElement) {
        new TomSelect(selectElement, {
            placeholder: selectElement.getAttribute('data-placeholder'),
            allowEmptyOption: true,
			plugins: ['remove_button'],
        });
    }
});
