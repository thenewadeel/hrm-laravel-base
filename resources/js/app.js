import './bootstrap';
import './theme.js';

// Import Alpine.js
import Alpine from 'alpinejs';

// Make Alpine globally available
window.Alpine = Alpine;

let alpineStarted = false;

function startAlpine() {
    if (alpineStarted || window.Alpine.__started) {
        console.log('Alpine already initialized, skipping...');
        return;
    }
    
    alpineStarted = true;
    Alpine.start();
    console.log('Alpine started successfully');
}

// Wait for Livewire to be fully initialized before starting Alpine
document.addEventListener('livewire:init', () => {
    // Give Livewire a moment to fully initialize components
    setTimeout(() => {
        startAlpine();
    }, 100);
});

// Fallback: If livewire:init doesn't fire, start Alpine anyway
document.addEventListener('DOMContentLoaded', () => {
    // Start Alpine after a delay if Livewire hasn't initialized
    setTimeout(() => {
        if (!alpineStarted && !window.Alpine.__started) {
            startAlpine();
        }
    }, 1000);
});
