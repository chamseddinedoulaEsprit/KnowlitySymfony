import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal'];

    connect() {
        console.log('Stats-modal controller connected!');
    }

    showModal(event) {
        console.log('Show modal clicked!');
        if (this.hasModalTarget) {
            this.modalTarget.classList.remove('d-none');
        } else {
            console.error('Modal target not found!');
        }
    }

    hideModal(event) {
        console.log('Hide modal clicked!');
        if (this.hasModalTarget) {
            this.modalTarget.classList.add('d-none');
        } else {
            console.error('Modal target not found!');
        }
    }
}