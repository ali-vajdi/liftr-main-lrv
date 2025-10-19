<div class="sidebar-wrapper sidebar-theme">
    <nav id="sidebar">
        <div class="shadow-bottom"></div>

        <ul class="list-unstyled menu-categories" id="accordionExample">
            <li class="menu {{ request()->routeIs('admin.moderators.*') ? 'active' : '' }}">
                <a href="#moderators" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('admin.moderators.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-user-check">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                        <span>مدیریت ادمین</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('admin.moderators.*') ? 'show' : '' }}"
                    id="moderators" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.moderators.view') ? 'active' : '' }}">
                        <a href="{{ route('admin.moderators.view') }}">مدیریت مدیران</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}">
                <a href="#organizations" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('admin.organizations.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-building">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l8-4v18"></path>
                            <path d="M19 21V11l-6-4"></path>
                        </svg>
                        <span>مدیریت شرکت‌ها</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('admin.organizations.*') ? 'show' : '' }}"
                    id="organizations" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.organizations.view') ? 'active' : '' }}">
                        <a href="{{ route('admin.organizations.view') }}">مدیریت شرکت‌ها</a>
                    </li>
                </ul>
            </li>
        </ul>
        
    </nav>
</div>