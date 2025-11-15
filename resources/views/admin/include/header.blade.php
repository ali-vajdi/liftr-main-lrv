<div class="header-container fixed-top">
    <header class="header navbar navbar-expand-sm">

        <ul class="navbar-item theme-brand flex-row  text-center">
            <li class="nav-item theme-logo">
                <a href="{{ route('admin.dashboard') }}">
                    <img src="{{ asset('assets/img/90x90.jpg')}}" class="navbar-logo" alt="logo">
                </a>
            </li>
            <li class="nav-item theme-text">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"> پنل مدیریتی لیفتر </a>
            </li>
        </ul>

        <ul class="navbar-item flex-row ml-md-auto">
            <!-- Theme Toggle Button -->
            <li class="nav-item theme-toggle">
                <a href="javascript:void(0);" class="nav-link theme-toggle-btn" id="theme-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-moon dark-mode">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-sun light-mode">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                </a>
            </li>

            <li class="nav-item dropdown user-profile-dropdown">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <img src="{{ asset('assets/img/90x90.jpg') }}" alt="avatar">
                </a>
                <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                    <div class="">
                        <div class="dropdown-item">
                            <a href="user_profile.html"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg> <span class="user-name">نام کاربر</span></a>
                        </div>
                        <div class="dropdown-item">
                            <a href="javascript:void(0)" class="lock-screen-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-lock">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg> قفل صفحه
                            </a>
                        </div>
                        <div class="dropdown-item">
                            <a href="javascript:void(0)" class="logout-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg> خروج
                            </a>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </header>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle Functionality
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    
    // Function to set theme
    function setTheme(theme) {
        htmlElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        updateThemeIcon(theme);
    }

    // Function to update icon
    function updateThemeIcon(theme) {
        const darkIcon = document.querySelector('.dark-mode');
        const lightIcon = document.querySelector('.light-mode');
        
        if (theme === 'dark') {
            darkIcon.style.display = 'block';
            lightIcon.style.display = 'none';
        } else {
            darkIcon.style.display = 'none';
            lightIcon.style.display = 'block';
        }
    }

    // Initialize theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);

    // Toggle theme on click
    themeToggle.addEventListener('click', function() {
        const currentTheme = htmlElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    });

    // User name and logout/lock screen handlers are now in admin-custom.js
});
</script>

<style>
.theme-toggle {
    margin-right: 15px;
    display: flex;
    align-items: center;
}

.theme-toggle-btn {
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.theme-toggle-btn svg {
    width: 20px;
    height: 20px;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.dark-mode {
    display: none;
}

[data-theme="dark"] .dark-mode {
    display: block;
}

[data-theme="dark"] .light-mode {
    display: none;
}

[data-theme="light"] .dark-mode {
    display: none;
}

[data-theme="light"] .light-mode {
    display: block;
}

/* Ensure theme colors are applied */
[data-theme="dark"] {
    --text-primary: #ebedf2;
    --text-secondary: #888ea8;
    --text-light: #e0e6ed;
    --text-dark: #515365;
    --text-muted: #bfc9d4;
    --text-code: #e7515a;
    --text-link: #1b55e2;
    
    --bg-body: #060818;
    --bg-white: #0e1726;
    --bg-navbar: #060818;
    --bg-subheader: #1a1c2d;
    --bg-hover: #3b3f5c;
    --bg-hover-light: rgba(59, 63, 92, 0.45);
    
    --border-color: #3b3f5c;
}

[data-theme="light"] {
    --text-primary: #3b3f5c;
    --text-secondary: #888ea8;
    --text-light: #e0e6ed;
    --text-dark: #0e1726;
    --text-muted: #515365;
    --text-code: #e7515a;
    --text-link: #1b55e2;
    
    --bg-body: #f1f2f3;
    --bg-white: #fff;
    --bg-navbar: #0e1726;
    --bg-subheader: #fafafa;
    --bg-hover: #bfc9d4;
    --bg-hover-light: #bae7ff;
    
    --border-color: #d3d3d3;
}
</style>