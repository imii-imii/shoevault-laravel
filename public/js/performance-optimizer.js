// Performance detection and optimization script
(function() {
    'use strict';
    
    // Detect if we're on a low-performance environment
    function isLowPerformanceEnvironment() {
        // Check for common indicators of shared hosting or low-end hardware
        const userAgent = navigator.userAgent.toLowerCase();
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        
        // Performance indicators
        const indicators = {
            slowConnection: connection && (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g'),
            limitedMemory: navigator.deviceMemory && navigator.deviceMemory < 4,
            mobileDevice: /android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent),
            oldBrowser: !window.requestAnimationFrame || !window.CSS || !window.CSS.supports
        };
        
        return Object.values(indicators).some(indicator => indicator);
    }
    
    // Apply performance optimizations
    function applyPerformanceOptimizations() {

        // Reduce animation durations
        const style = document.createElement('style');
        style.textContent = `
            * {
                animation-duration: 0.2s !important;
                transition-duration: 0.2s !important;
            }
            
            .slide-content {
                transition: transform 0.3s ease-out !important;
            }
            
            /* Disable heavy effects */
            *[class*="pulse"],
            *[class*="bounce"],
            *[class*="zoom"] {
                animation: none !important;
            }
            
            /* Simplify hover effects */
            button:hover,
            .btn:hover {
                transform: none !important;
                filter: brightness(1.1) !important;
            }
        `;
        document.head.appendChild(style);
        
        // Disable scroll animations if they exist
        if (window.AOS) {
            window.AOS.init({
                duration: 300,
                once: true,
                disable: 'mobile'
            });
        }
    }
    
    // Check performance and apply optimizations
    if (isLowPerformanceEnvironment()) {
        document.addEventListener('DOMContentLoaded', applyPerformanceOptimizations);
    }
    
    // Monitor performance and apply optimizations if needed
    if ('PerformanceObserver' in window) {
        try {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const slowEntries = entries.filter(entry => entry.duration > 100);
                
                if (slowEntries.length > 3) {
                    console.warn('⚠️ Performance issues detected, applying optimizations...');
                    applyPerformanceOptimizations();
                }
            });
            
            observer.observe({ entryTypes: ['paint', 'layout-shift'] });
        } catch (e) {
            // PerformanceObserver not supported, apply optimizations anyway
            applyPerformanceOptimizations();
        }
    }
})();