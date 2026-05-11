/**
 * Auto Logout System
 * Logout automatically after 10 minutes of inactivity
 * Works on all pages except login/register/admin login pages
 */

(function() {
    'use strict';
    
    // Don't run on login/register/admin pages
    const currentPath = window.location.pathname.toLowerCase();
    const excludedPaths = ['/login.html', '/register.html', '/login_admin.html', '/admin/login_admin.html'];
    
    if (excludedPaths.some(path => currentPath.includes(path))) {
        return; // Exit if on excluded pages
    }
    
    // Check if user is logged in (has userId in localStorage)
    const userId = localStorage.getItem('userId');
    if (!userId) {
        return; // Exit if user is not logged in
    }
    
    // Configuration
    const LOGOUT_TIME = 10 * 60 * 1000; // 10 minutes in milliseconds
    let inactivityTimer = null;
    
    /**
     * Reset the inactivity timer
     * Called whenever user activity is detected
     */
    function resetInactivityTimer() {
        // Clear existing timer
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
        
        // Set new timer
        inactivityTimer = setTimeout(() => {
            // User inactive for 10 minutes - logout
            console.log('Auto logout: 10 minutes of inactivity detected');
            // ลบเฉพาะคีย์ล็อกอิน ไม่ล้างข้อมูลระบบอื่นทั้ง localStorage
            localStorage.removeItem('userId');
            localStorage.removeItem('userName');
            localStorage.removeItem('username');
            
            // Determine redirect path based on current location
            const isInPageFolder = currentPath.includes('/Page/');
            const redirectPath = isInPageFolder ? '../login.html' : 'login.html';
            
            window.location.href = redirectPath;
        }, LOGOUT_TIME);
    }
    
    /**
     * Track user activity
     * Resets the inactivity timer when user interacts with the page
     */
    function trackUserActivity() {
        resetInactivityTimer();
    }
    
    /**
     * Initialize auto-logout system
     */
    function initializeAutoLogout() {
        // Reset timer on page load
        resetInactivityTimer();
        
        // Track various user activities
        const events = [
            'mousemove',
            'keypress',
            'keydown',
            'click',
            'scroll',
            'touchstart',
            'touchmove',
            'mousedown'
        ];
        
        // Add event listeners for all activity types
        events.forEach(event => {
            document.addEventListener(event, trackUserActivity, { passive: true });
        });
        
        // Also track window focus to reset timer when user returns to tab
        window.addEventListener('focus', trackUserActivity);
        
        // Track when user switches tabs
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // User returned to tab - reset timer
                trackUserActivity();
            }
        });
        
        console.log('Auto-logout system initialized (10 minutes inactivity)');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAutoLogout);
    } else {
        // DOM already loaded
        initializeAutoLogout();
    }
    
    // Cleanup function (optional, for when navigating away)
    window.addEventListener('beforeunload', function() {
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
    });
    
})();

