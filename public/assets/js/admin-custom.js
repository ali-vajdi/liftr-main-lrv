// Admin custom JavaScript
$(document).ready(function() {
    // Set user name from localStorage
    var user = JSON.parse(localStorage.getItem('admin_user'));
    if (user) {
        $('.user-name').text(user.full_name);
    }

    // Lock screen
    $(document).on('click', '.lock-screen-link', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '/api/admin/lock-screen',
            type: 'POST',
            success: function() {
                // Store lock state in localStorage
                localStorage.setItem('screen_locked', 'true');
                window.location.href = "/admin/lock-screen";
            },
            error: function(xhr, status, error) {
                alert("خطا در قفل کردن صفحه");
            }
        });
    });

    // Logout
    $(document).on('click', '.logout-link', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '/api/admin/logout',
            type: 'POST',
            success: function() {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
                localStorage.removeItem('screen_locked');
                window.location.href = "/admin/login";
            },
            error: function(xhr, status, error) {
                // Even if there's an error, we should still redirect to login
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
                localStorage.removeItem('screen_locked');
                window.location.href = "/admin/login";
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