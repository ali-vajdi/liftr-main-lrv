<div class="sidebar-wrapper sidebar-theme">
    <nav id="sidebar">
        <div class="shadow-bottom"></div>

        <ul class="list-unstyled menu-categories" id="accordionExample">

            <li class="menu {{ request()->routeIs('organization.dashboard*') ? 'active' : '' }}">
                <a href="{{ route('organization.dashboard') }}"
                    aria-expanded="{{ request()->routeIs('organization.dashboard*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-home">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9,22 9,12 15,12 15,22"></polyline>
                        </svg>
                        <span>داشبورد</span>
                    </div>
                </a>
            </li>

            <li class="menu {{ request()->routeIs('organization.packages.*') ? 'active' : '' }}">
                <a href="#packages" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('organization.packages.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-package">
                            <path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span>پکیج‌های من</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('organization.packages.*') ? 'show' : '' }}"
                    id="packages" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('organization.packages.view') ? 'active' : '' }}">
                        <a href="{{ route('organization.packages.view') }}">مشاهده پکیج‌ها</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('organization.users.*') ? 'active' : '' }}">
                <a href="#users" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('organization.users.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-users">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>کاربران سازمان</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('organization.users.*') ? 'show' : '' }}"
                    id="users" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('organization.users.view') ? 'active' : '' }}">
                        <a href="{{ route('organization.users.view') }}">مدیریت کاربران</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('organization.technicians.*') ? 'active' : '' }}">
                <a href="#technicians" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('organization.technicians.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-user-check">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                        <span>تکنیسین‌ها</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('organization.technicians.*') ? 'show' : '' }}"
                    id="technicians" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('organization.technicians.view') ? 'active' : '' }}">
                        <a href="{{ route('organization.technicians.view') }}">مدیریت تکنیسین‌ها</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('organization.buildings.*') ? 'active' : '' }}">
                <a href="#buildings" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('organization.buildings.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-building">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l8-4v18"></path>
                            <path d="M19 21V11l-6-4"></path>
                        </svg>
                        <span>ساختمان‌ها/پروژه‌ها</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('organization.buildings.*') ? 'show' : '' }}"
                    id="buildings" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('organization.buildings.view') ? 'active' : '' }}">
                        <a href="{{ route('organization.buildings.view') }}">مدیریت ساختمان‌ها/پروژه‌ها</a>
                    </li>
                    <li class="{{ request()->routeIs('organization.buildings.expiring') ? 'active' : '' }}">
                        <a href="{{ route('organization.buildings.expiring') }}">قراردادهای رو به اتمام</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('organization.services.*') ? 'active' : '' }}">
                <a href="#services" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('organization.services.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-settings">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6m9-9h-6m-6 0H3m15.364-6.364l-4.243 4.243m0 0L12.879 8.88m4.242 4.242L12.879 8.88m0 0L8.636 4.636M12.879 8.88l4.243 4.243"></path>
                        </svg>
                        <span>لیست سرویس‌ها</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('organization.services.*') ? 'show' : '' }}"
                    id="services" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('organization.services.pending') ? 'active' : '' }}">
                        <a href="{{ route('organization.services.pending') }}">سرویس‌های در انتظار</a>
                    </li>
                    <li class="{{ request()->routeIs('organization.services.assigned') ? 'active' : '' }}">
                        <a href="{{ route('organization.services.assigned') }}">سرویس‌های اختصاص داده شده</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('organization.profile') ? 'active' : '' }}">
                <a href="{{ route('organization.profile') }}"
                    aria-expanded="{{ request()->routeIs('organization.profile') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-user">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>پروفایل</span>
                    </div>
                </a>
            </li>
        </ul>
        
    </nav>
</div>