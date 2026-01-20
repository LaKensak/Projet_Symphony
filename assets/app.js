import './bootstrap.js';
/*
 * Application JavaScript
 */

import './styles/app.css';

// Initialize Bootstrap tooltips and popovers if needed
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });

    console.log('Application initialized');
});
