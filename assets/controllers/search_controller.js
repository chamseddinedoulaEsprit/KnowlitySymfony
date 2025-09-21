import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'input', 'select'];

    connect() {
        console.log('Search controller connected!');
    }

    submitForm(event) {
        event.preventDefault();
        const form = this.formTarget;
        const url = form.action + '?' + new URLSearchParams(new FormData(form)).toString();
        window.location.href = url;
    }

    resetFilters() {
        this.inputTarget.value = '';
        this.selectTarget.selectedIndex = 0;
        window.location.href = this.element.querySelector('[data-reset-url]').dataset.resetUrl;
    }
}