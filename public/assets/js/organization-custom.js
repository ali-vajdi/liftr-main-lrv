// Organization custom JavaScript
$(document).ready(function() {
    // Set user name from localStorage (for organization users)
    var user = JSON.parse(localStorage.getItem('organization_user'));
    if (user) {
        $('.user-name').text(user.full_name);
    }

    // Lock screen (for organization users)
    $(document).on('click', '.lock-screen-link', function(e) {
        var orgToken = localStorage.getItem('organization_token');
        if (!orgToken) {
            return; // Not an organization user, let admin handler take over
        }
        
        e.preventDefault();
        e.stopPropagation();
        
        $.ajax({
            url: '/api/organization/lock-screen',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + orgToken
            },
            success: function() {
                localStorage.setItem('screen_locked', 'true');
                window.location.href = "/lock-screen";
            },
            error: function(xhr, status, error) {
                alert("خطا در قفل کردن صفحه");
            }
        });
    });

    // Logout (for organization users)
    $(document).on('click', '.logout-link', function(e) {
        var orgToken = localStorage.getItem('organization_token');
        if (!orgToken) {
            return; // Not an organization user, let admin handler take over
        }
        
        e.preventDefault();
        e.stopPropagation();
        
        $.ajax({
            url: '/api/organization/logout',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + orgToken
            },
            success: function() {
                localStorage.removeItem('organization_token');
                localStorage.removeItem('organization_user');
                localStorage.removeItem('screen_locked');
                window.location.href = "/login";
            },
            error: function(xhr, status, error) {
                // Even if there's an error, we should still redirect to login
                localStorage.removeItem('organization_token');
                localStorage.removeItem('organization_user');
                localStorage.removeItem('screen_locked');
                window.location.href = "/login";
            }
        });
    });
    
    // Handle sidebar menu
    $('.menu a.dropdown-toggle').on('click', function(e) {
        if (!$(this).parent().hasClass('active')) {
            $('.menu.active').removeClass('active');
            $(this).parent().addClass('active');
        }
    });
});

