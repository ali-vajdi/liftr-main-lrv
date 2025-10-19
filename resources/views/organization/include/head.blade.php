<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
<link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.ico') }}" />
<link href="{{ asset('assets/css/loader.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/js/loader.js') }}"></script>

<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="{{ asset('bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/plugins.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/elements/custom-pagination.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/scrollspyNav.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('plugins/sweetalerts/promise-polyfill.js') }}"></script>
<link href="{{ asset('plugins/sweetalerts/sweetalert.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/sweetalerts/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/components/custom-sweetalert.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">

<link rel="stylesheet" href="{{ asset('assets/js/libs/jalalidatepicker.min.css') }}">
<!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM STYLES -->
<!-- <link href="{{ asset('plugins/apex/apexcharts.css') }}" rel="stylesheet" type="text/css"> -->
<!-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/widgets/modules-widgets.css') }}">     -->
<!-- <link href="{{ asset('assets/css/dashboard/dash_1.css') }}" rel="stylesheet" type="text/css" /> -->
<!-- END PAGE LEVEL PLUGINS/CUSTOM STYLES -->

<!-- END GLOBAL MANDATORY STYLES -->

<!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM STYLES -->

<style>
    .layout-px-spacing {
        min-height: calc(100vh - 166px) !important;
    }


    /* Enhanced Widget Heading Styles - Modern Card Design */
    .widget .widget-heading {
        position: relative;
        padding: 1.5rem 2rem;
        margin-bottom: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 0.75rem 0.75rem 0 0;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        overflow: hidden;
    }

    .widget .widget-heading::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%, rgba(255, 255, 255, 0.1) 100%);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }

    .widget .widget-heading:hover::before {
        transform: translateX(100%);
    }

    .widget .widget-heading h5,
    .widget .widget-heading h6 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #ffffff;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        letter-spacing: 0;
        position: relative;
        z-index: 2;
    }

    .widget .widget-heading .dropdown {
        position: absolute;
        left: 2rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
    }

    .widget .widget-heading .dropdown a {
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
        padding: 0.5rem;
        border-radius: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }

    .widget .widget-heading .dropdown a:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Dark Mode Support for Widget Heading */
    [data-theme="dark"] .widget .widget-heading {
        background: linear-gradient(135deg, #4c63d2 0%, #5a4fcf 100%);
        box-shadow: 0 4px 15px rgba(76, 99, 210, 0.4);
    }

    [data-theme="dark"] .widget .widget-heading h5,
    [data-theme="dark"] .widget .widget-heading h6 {
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    [data-theme="dark"] .widget .widget-heading .dropdown a {
        color: rgba(255, 255, 255, 0.9);
        background: rgba(255, 255, 255, 0.15);
    }

    [data-theme="dark"] .widget .widget-heading .dropdown a:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.25);
    }
</style>