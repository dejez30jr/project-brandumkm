<script>
document.addEventListener('click', function(e){

    const image = e.target.closest('.fi-in-image img');

    if(image){

        const modal = document.createElement('div');
        modal.className = 'image-preview-modal';

        modal.innerHTML = `
            <button class="image-preview-close">✕</button>
            <img src="${image.src}">
        `;

        document.body.appendChild(modal);

        modal.querySelector('.image-preview-close')
            .addEventListener('click', () => {
                modal.remove();
            });

        modal.addEventListener('click', (event) => {
            if(event.target === modal){
                modal.remove();
            }
        });
    }
});
</script>