import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['tooltip', 'content'];

    steps = [
        { id: 'intro', message: 'Bienvenue sur la page du cours ! Cliquez sur "Suivant" pour commencer.', target: null },
        { id: 'back-button', message: 'Cliquez ici pour retourner à la liste des cours.', target: '[data-course-guide-step="back-button"]' },
        { id: 'course-details', message: 'Voici les détails du cours : titre, langue, prix, etc.', target: '[data-course-guide-step="course-details"]' },
        { id: 'enroll-favorite', message: 'Inscrivez-vous ou ajoutez ce cours à vos favoris ici.', target: '[data-course-guide-step="enroll-button"], [data-course-guide-step="favorite-enrolled"]' },
        { id: 'description', message: 'Lisez la description complète du cours ici.', target: '[data-course-guide-step="description"]' },
        { id: 'chapters', message: 'Explorez les chapitres disponibles ici (si inscrit).', target: '[data-course-guide-step="chapters"]' },
        { id: 'end', message: 'Vous êtes prêt à commencer ! Fermez ce guide.', target: null }
    ];

    connect() {
        if (!localStorage.getItem('courseGuideCompleted')) {
            this.currentStep = 0;
            this.showStep();
        }
    }

    showStep() {
        const step = this.steps[this.currentStep];
        this.contentTarget.textContent = step.message;
        this.tooltipTarget.classList.remove('d-none');

        // Supprime la surbrillance précédente
        document.querySelectorAll('.guide-highlight').forEach(el => el.classList.remove('guide-highlight'));

        // Réinitialise les styles du tooltip
        this.tooltipTarget.style.transform = '';
        this.tooltipTarget.style.top = '';
        this.tooltipTarget.style.left = '';

        if (step.target) {
            const targetElement = document.querySelector(step.target);
            if (targetElement) {
                const rect = targetElement.getBoundingClientRect();
                const tooltipRect = this.tooltipTarget.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const viewportWidth = window.innerWidth;

                // Positionnement intelligent
                let top = rect.bottom + window.scrollY + 10;
                let left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);

                // Ajuste si le tooltip dépasse en bas
                if (top + tooltipRect.height > viewportHeight + window.scrollY) {
                    top = rect.top + window.scrollY - tooltipRect.height - 10;
                    this.tooltipTarget.style.setProperty('--arrow-top', 'auto');
                    this.tooltipTarget.style.setProperty('--arrow-bottom', '-6px');
                    this.tooltipTarget.style.setProperty('--arrow-shadow', '2px 2px 6px rgba(0, 0, 0, 0.1)');
                } else {
                    this.tooltipTarget.style.setProperty('--arrow-top', '-6px');
                    this.tooltipTarget.style.setProperty('--arrow-bottom', 'auto');
                    this.tooltipTarget.style.setProperty('--arrow-shadow', '-2px -2px 6px rgba(0, 0, 0, 0.1)');
                }

                // Ajuste si le tooltip dépasse à droite ou à gauche
                if (left + tooltipRect.width > viewportWidth + window.scrollX) {
                    left = viewportWidth + window.scrollX - tooltipRect.width - 10;
                } else if (left < window.scrollX) {
                    left = window.scrollX + 10;
                }

                this.tooltipTarget.style.top = `${top}px`;
                this.tooltipTarget.style.left = `${left}px`;
                targetElement.classList.add('guide-highlight');
            }
        } else {
            // Centre le tooltip pour les étapes sans cible
            this.tooltipTarget.style.top = '50%';
            this.tooltipTarget.style.left = '50%';
            this.tooltipTarget.style.transform = 'translate(-50%, -50%)';
            this.tooltipTarget.style.setProperty('--arrow-top', 'auto');
            this.tooltipTarget.style.setProperty('--arrow-bottom', 'auto');
            this.tooltipTarget.style.setProperty('--arrow-shadow', 'none');
        }
    }

    next() {
        if (this.currentStep < this.steps.length - 1) {
            this.currentStep++;
            this.showStep();
        }
    }

    previous() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.showStep();
        }
    }

    close() {
        this.tooltipTarget.classList.add('d-none');
        document.querySelectorAll('.guide-highlight').forEach(el => el.classList.remove('guide-highlight'));
        localStorage.setItem('courseGuideCompleted', 'true');
    }
}