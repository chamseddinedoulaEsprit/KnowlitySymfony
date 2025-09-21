document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('blog-search');
    if (!searchInput) return;

    let timeoutId;

    function filterBlogs(searchTerm) {
        searchTerm = searchTerm.toLowerCase().trim();
        
        // Pour index2.html.twig
        const blogCards = document.querySelectorAll('.blog-grid');
        if (blogCards.length > 0) {
            blogCards.forEach(card => {
                const title = card.querySelector('.blog-title').textContent.toLowerCase();
                const content = card.querySelector('.blog-excerpt').textContent.toLowerCase();
                const author = card.querySelector('.blog-author').textContent.toLowerCase();
                const comments = Array.from(card.querySelectorAll('.comment-item p')).map(p => p.textContent.toLowerCase());
                
                if (searchTerm === '' || 
                    title.includes(searchTerm) || 
                    content.includes(searchTerm) || 
                    author.includes(searchTerm) ||
                    comments.some(comment => comment.includes(searchTerm))) {
                    card.style.display = '';
                    card.classList.add('animate__animated', 'animate__fadeIn');
                } else {
                    card.style.display = 'none';
                }
            });

            // Vérifier s'il n'y a aucun résultat
            const visibleCards = Array.from(blogCards).filter(card => card.style.display !== 'none');
            const container = document.querySelector('.blog-grid-container');
            const existingMessage = document.getElementById('no-results-message');

            if (visibleCards.length === 0) {
                if (!existingMessage) {
                    const message = document.createElement('div');
                    message.id = 'no-results-message';
                    message.className = 'text-center py-5 animate__animated animate__fadeIn';
                    message.innerHTML = `
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h3>Aucun résultat trouvé</h3>
                        <p class="text-muted">Essayez avec d'autres mots-clés</p>
                    `;
                    container.appendChild(message);
                }
            } else if (existingMessage) {
                existingMessage.remove();
            }
        }
    }

    searchInput.addEventListener('input', function(e) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            filterBlogs(e.target.value);
        }, 300);
    });
});
