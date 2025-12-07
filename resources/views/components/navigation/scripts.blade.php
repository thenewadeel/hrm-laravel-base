<script>
    // Close mobile menu when clicking outside
    document.addEventListener('livewire:init', () => {
        Livewire.on('close-mobile-menu', () => {
            // This will be handled by Alpine's x-show directive
            const mobileMenu = document.querySelector('[x-data*="mobileMenuOpen"]');
            if (mobileMenu && mobileMenu._x_dataStack) {
                mobileMenu._x_dataStack[0].mobileMenuOpen = false;
            }
        });
    });
</script>
