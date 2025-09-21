document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('blog-search');
    const blogCards = document.querySelectorAll('.blog-card');
    let timeoutId;

    function filterBlogs(searchTerm) {
        searchTerm = searchTerm.toLowerCase().trim();

        blogCards.forEach(card => {
            const title = card.getAttribute('data-blog-title');
            const content = card.getAttribute('data-blog-content');
            
            if (searchTerm === '' || title.includes(searchTerm) || content.includes(searchTerm)) {
                // Réinitialiser le style pour l'animation
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.display = '';
                
                // Forcer un reflow
                card.offsetHeight;
                
                // Appliquer l'animation d'apparition
                requestAnimationFrame(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                });
            } else {
                // Animation de disparition
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });

        // Vérifier s'il n'y a aucun résultat
        const visibleCards = Array.from(blogCards).filter(card => card.style.display !== 'none');
        const noResultsMessage = document.getElementById('no-results-message');
        
        if (visibleCards.length === 0) {
            if (!noResultsMessage) {
                const message = document.createElement('div');
                message.id = 'no-results-message';
                message.className = 'text-center py-5';
                message.innerHTML = `
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h3>Aucun résultat trouvé</h3>
                    <p class="text-muted">Essayez avec d'autres mots-clés</p>
                `;
                document.getElementById('blog-list').appendChild(message);
            }
        } else if (noResultsMessage) {
            noResultsMessage.remove();
        }
    }

    // Écouter les changements dans la barre de recherche avec debounce
    searchInput.addEventListener('input', function(e) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            filterBlogs(e.target.value);
        }, 300);
    });

    // Ajouter des styles pour les animations
    const style = document.createElement('style');
    style.textContent = `
        .blog-card {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
    `;
    document.head.appendChild(style);
});
