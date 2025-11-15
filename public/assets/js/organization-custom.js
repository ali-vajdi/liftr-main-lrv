// Organization custom JavaScript
// Helper function to get organization data from API
function getOrganizationData(callback) {
    var token = localStorage.getItem('organization_token');
    if (!token) {
        if (callback) callback(null, 'No token found');
        return;
    }

    $.ajax({
        url: '/api/organization/check-auth',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.user && response.user.organization) {
                if (callback) callback(response.user.organization, null);
            } else {
                if (callback) callback(null, 'Organization not found');
            }
        },
        error: function(xhr) {
            if (xhr.status === 401) {
                localStorage.removeItem('organization_token');
                localStorage.removeItem('organization_user');
                window.location.href = '/login';
            } else {
                if (callback) callback(null, 'Error fetching organization data');
            }
        }
    });
}

// Helper function to get dashboard data from API
function getDashboardData(callback) {
    var token = localStorage.getItem('organization_token');
    if (!token) {
        if (callback) callback(null, 'No token found');
        return;
    }

    $.ajax({
        url: '/api/organization/dashboard-data',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.data) {
                if (callback) callback(response.data, null);
            } else {
                if (callback) callback(null, 'No data received');
            }
        },
        error: function(xhr) {
            if (xhr.status === 401) {
                localStorage.removeItem('organization_token');
                localStorage.removeItem('organization_user');
                window.location.href = '/login';
            } else {
                if (callback) callback(null, 'Error fetching dashboard data');
            }
        }
    });
}

$(document).ready(function() {
    // Set user name from localStorage (for organization users)
    var user = JSON.parse(localStorage.getItem('organization_user'));
    if (user) {
        $('.user-name').text(user.name || user.full_name || 'کاربر');
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

