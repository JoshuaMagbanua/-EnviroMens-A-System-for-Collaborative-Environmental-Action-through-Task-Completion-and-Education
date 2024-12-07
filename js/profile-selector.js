document.addEventListener('DOMContentLoaded', function() {
    const radioButtons = document.querySelectorAll('input[name="profile_picture"]');
    const previewImage = document.getElementById('profilePreview');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const imgSrc = this.parentElement.querySelector('img').src;
                previewImage.src = imgSrc;
                
                // Add animation class to preview
                previewImage.classList.add('preview-update');
                setTimeout(() => {
                    previewImage.classList.remove('preview-update');
                }, 300);
            }
        });
    });
}); 