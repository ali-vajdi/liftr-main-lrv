<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>پنل شرکتی لیفتر - @yield('title', 'پنل شرکت')</title>
    @include('organization.include.head')

    @yield('page-styles')
</head>

<body>
    <!-- BEGIN LOADER -->
    @include('organization.include.loading')
    <!--  END LOADER -->

    <!--  BEGIN NAVBAR  -->
    @include('organization.include.header')
    <!--  END NAVBAR  -->

    <!--  BEGIN NAVBAR  -->
    @include('organization.include.navbar')
    <!--  END NAVBAR  -->

    <!--  BEGIN MAIN CONTAINER  -->
    <div class="main-container" id="container">

        <div class="overlay"></div>
        <div class="search-overlay"></div>

        <!--  BEGIN SIDEBAR  -->
        @include('organization.include.sidebar')
        <!--  END SIDEBAR  -->

        <!--  BEGIN CONTENT PART  -->
        <div id="content" class="main-content">
            <div class="layout-px-spacing">

                <div class="row layout-top-spacing">
                    <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                        <div class="widget-content widget-content-area br-6">
                            @yield('content')
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <!--  END CONTENT PART  -->

    </div>
    <!-- END MAIN CONTAINER -->

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="{{ asset('assets/js/libs/jquery-3.1.1.min.js')}}"></script>
    <script src="{{ asset('bootstrap/js/popper.min.js')}}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.min.js')}}"></script>
    <script src="{{ asset('plugins/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
    <script src="{{ asset('assets/js/app.js')}}"></script>
    <script>
        $(document).ready(function () {
            App.init();

            // Set up AJAX headers for all requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                }
            });

            // Check if user is authenticated
            var token = localStorage.getItem('organization_token');
            if (!token && window.location.pathname !== '/login') {
                window.location.href = "/login";
            }

            // Check if screen is locked
            if (localStorage.getItem('screen_locked') === 'true' &&
                window.location.pathname !== '/lock-screen' &&
                window.location.pathname !== '/login') {
                window.location.href = "/lock-screen";
            }
        });
    </script>
    <script src="{{ asset('plugins/highlight/highlight.pack.js')}}"></script>
    <script src="{{ asset('assets/js/custom.js')}}"></script>
    <script src="{{ asset('assets/js/admin-custom.js')}}"></script>

    <script src="{{ asset('assets/js/scrollspyNav.js')}}"></script>
    <script src="{{ asset('plugins/sweetalerts/sweetalert2.min.js')}}"></script>
    <script src="{{ asset('plugins/sweetalerts/custom-sweetalert.js')}}"></script>
    <script src="{{ asset('plugins/select2/select2.min.js')}}"></script>

    <script src="{{ asset('assets/js/libs/jalalidatepicker.min.js') }}"></script>
    <!-- <script src="{{ asset('plugins/apex/apexcharts.min.js')}}"></script> -->
    <!-- <script src="{{ asset('assets/js/widgets/modules-widgets.js')}}"></script> -->
    <!-- choose one -->
<!-- <script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script> -->
    <!-- END GLOBAL MANDATORY SCRIPTS -->

    <!-- Page specific scripts -->
    @yield('page-scripts')
    @stack('page-scripts')
</body>

</html>
