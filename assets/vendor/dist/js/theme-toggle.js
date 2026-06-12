(function ($) {
    "use strict";

    $(document).ready(function() {
        const body = $('body');
        const navbar = $('.main-header.navbar');
        const toggleButton = $('#dark-mode-toggle');

        // Function to set the mode and save it in local storage
        function setMode(isDark) {
            if (isDark) {
                body.addClass('dark-mode');
                // Set navbar to dark and remove light classes
                navbar.removeClass('navbar-light navbar-white').addClass('navbar-dark');
                localStorage.setItem('themeMode', 'dark');
            } else {
                body.removeClass('dark-mode');
                // Set navbar to light and remove dark class
                navbar.removeClass('navbar-dark').addClass('navbar-light navbar-white');
                localStorage.setItem('themeMode', 'light');
            }
        }

        // Check for saved preference on load
        const savedMode = localStorage.getItem('themeMode');
        if (savedMode === 'dark') {
             // If preference is dark, ensure dark mode is applied
             setMode(true);
        } else if (savedMode === 'light') {
            // If preference is light, apply light mode
            setMode(false);
        } else {
             // If no preference saved, initialize based on current body class
             setMode(body.hasClass('dark-mode'));
        }
        
        // Handle button click (the actual toggle action)
        toggleButton.on('click', function(e) {
            e.preventDefault();
            const currentModeIsDark = body.hasClass('dark-mode');
            setMode(!currentModeIsDark); // Toggle the mode
        });
    });
})(jQuery);