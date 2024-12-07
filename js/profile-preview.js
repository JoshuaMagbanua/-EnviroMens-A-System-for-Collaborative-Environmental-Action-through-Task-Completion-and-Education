document.addEventListener('DOMContentLoaded', function() {
    const profileSelectButton = document.getElementById('profileSelectButton');
    const optionsContainer = document.querySelector('.profile-options-container');
    const profilePreview = document.getElementById('profilePreview');
    const profileOptions = document.querySelectorAll('.profile-option');
    const selectedProfileText = document.getElementById('selectedProfileText');
    const profilePictureInput = document.getElementById('profilePictureInput');

    // Show options when clicking the select button
    profileSelectButton.addEventListener('click', function(e) {
        e.preventDefault();
        optionsContainer.style.display = optionsContainer.style.display === 'none' ? 'block' : 'none';
    });

    // Handle option selection
    profileOptions.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.dataset.value;
            const imgSrc = this.querySelector('img').src;
            const text = this.querySelector('span').textContent;
            
            // Update hidden input value
            profilePictureInput.value = value;
            
            // Update selected display
            selectedProfileText.textContent = text;
            
            // Update preview
            profilePreview.src = imgSrc;
            
            // Hide options container
            optionsContainer.style.display = 'none';
        });
    });

    // Close options when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileSelectButton.contains(e.target) && !optionsContainer.contains(e.target)) {
            optionsContainer.style.display = 'none';
        }
    });
}); 