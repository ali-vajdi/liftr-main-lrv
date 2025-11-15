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

            <li class="menu {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                <a href="#packages" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('admin.packages.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-package">
                            <path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span>مدیریت تعرفه‌ها</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('admin.packages.*') ? 'show' : '' }}"
                    id="packages" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.packages.view') ? 'active' : '' }}">
                        <a href="{{ route('admin.packages.view') }}">مدیریت تعرفه‌ها</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('admin.sms.*') ? 'active' : '' }}">
                <a href="#sms" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('admin.sms.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-message-square">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span>مدیریت پیامک‌ها</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('admin.sms.*') ? 'show' : '' }}"
                    id="sms" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.sms.view') ? 'active' : '' }}">
                        <a href="{{ route('admin.sms.view') }}">مدیریت پیامک‌ها</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('admin.unit-checklists.*') ? 'active' : '' }}">
                <a href="#unit-checklists" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('admin.unit-checklists.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-check-square">
                            <polyline points="9 11 12 14 22 4"></polyline>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                        <span>چک لیست های واحد</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('admin.unit-checklists.*') ? 'show' : '' }}"
                    id="unit-checklists" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.unit-checklists.view') ? 'active' : '' }}">
                        <a href="{{ route('admin.unit-checklists.view') }}">مدیریت چک لیست های واحد</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('admin.description-checklists.*') ? 'active' : '' }}">
                <a href="#description-checklists" data-toggle="collapse"
                    aria-expanded="{{ request()->routeIs('admin.description-checklists.*') ? 'true' : 'false' }}"
                    class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-file-text">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span>چک لیست های توضیحات</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-chevron-right">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ request()->routeIs('admin.description-checklists.*') ? 'show' : '' }}"
                    id="description-checklists" data-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.description-checklists.view') ? 'active' : '' }}">
                        <a href="{{ route('admin.description-checklists.view') }}">مدیریت چک لیست های توضیحات</a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                <a href="{{ route('admin.transactions.view') }}">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-dollar-sign">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <span>حسابداری و تراکنش‌ها</span>
                    </div>
                </a>
            </li>
        </ul>
        
    </nav>
</div>