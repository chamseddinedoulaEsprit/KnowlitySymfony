import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal'];

    connect() {
        console.log('Stats-modal controller connected!');
    }

    showModal() {
        console.log('Show modal clicked!');
        this.modalTarget.classList.remove('d-none');
    }

    hideModal() {
        console.log('Hide modal clicked!');
        this.modalTarget.classList.add('d-none');
    }
}