<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webroster ADMS Server</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicons -->
    <link href="{{ asset('favicon.png') }}" rel="icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>

        td.text-wrap {
            word-break: break-word;
        }

        .w-20 {
            width: 20% !important;
        }
        
        @media (max-width: 991.98px) {
            .navbar-collapse {
                position: fixed;
                top: 56px; /* Adjust this value based on your navbar height */
                left: -100%;
                padding-left: 15px;
                padding-right: 15px;
                padding-bottom: 15px;
                width: 75%;
                height: 100%;
                background-color: #f8f9fa;
                transition: all 0.3s ease-in-out;
                z-index: 1000;
            }

            .navbar-collapse.show {
                left: 0;
            }

            /* Fix dropdown positioning on mobile */
            .navbar-collapse .dropdown-menu {
                position: static !important;
                float: none;
                width: 100%;
                background-color: transparent;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .navbar-collapse .dropdown-item {
                color: #6c757d;
                padding: 8px 15px;
                border-left: 3px solid transparent;
                transition: all 0.2s ease;
                margin: 2px 0;
                border-radius: 4px;
            }

            .navbar-collapse .dropdown-item:hover {
                background-color: #e9ecef;
                color: #495057;
                border-left-color: #007bff;
            }

            /* Ensure dropdown is visible when open */
            .navbar-collapse .dropdown.show .dropdown-menu {
                display: block !important;
                background-color: rgba(248, 249, 250, 0.95);
                border-radius: 8px;
                margin: 8px 0;
                padding: 8px 0;
            }

            /* Additional mobile dropdown styling */
            .navbar-collapse .dropdown {
                position: relative;
            }

            .navbar-collapse .dropdown-menu {
                max-height: none;
                overflow: visible;
            }

            /* Prevent dropdown from being cut off */
            .navbar-collapse {
                overflow-y: auto;
                overflow-x: hidden;
            }

            /* Animate dropdown chevron */
            .navbar-collapse .dropdown.show .dropdown-toggle .fa-chevron-down {
                transform: rotate(180deg);
                transition: transform 0.2s ease;
            }

            .navbar-collapse .dropdown-toggle .fa-chevron-down {
                transition: transform 0.2s ease;
            }

            body.menu-open {
                overflow: hidden;
            }

            .navbar-toggler {
                z-index: 1001;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('images/webroster_bw_logo-tr.png') }}" alt="Logo" height="30">
                Webroster ADMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('devices.index') }}">Device</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('devices.monitor') }}">
                            <i class="fas fa-traffic-light"></i> Monitor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('agentes.index') }}">Employees</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Utilities <i class="fas fa-chevron-down ms-1"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ route('devices.attendance') }}">Attendance</a></li>
                            <li><a class="dropdown-item" href="{{ route('devices.oficinas') }}">Oficinas</a></li>
                            <li><a class="dropdown-item" href="{{ route('devices.deviceLog') }}">Device Log</a></li>
                            <li><a class="dropdown-item" href="{{ route('devices.fingerLog') }}">Finger Log</a></li>
                            <li><a class="dropdown-item" href="{{ route('devices.fingerprints') }}">Fingerprints</a></li> 
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                    </li>
                </ul>
            </div>
            <span class="navbar-text d-none d-lg-block">
                Mindware.com.mx
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.navbar-toggler').on('click', function() {
                $('body').toggleClass('menu-open');
            });

            // Handle mobile dropdown toggling
            $('.dropdown-toggle').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $dropdown = $(this).closest('.dropdown');
                const $dropdownMenu = $dropdown.find('.dropdown-menu');
                
                // Close other open dropdowns
                $('.dropdown').not($dropdown).removeClass('show');
                $('.dropdown-menu').not($dropdownMenu).removeClass('show');
                
                // Toggle current dropdown
                $dropdown.toggleClass('show');
                $dropdownMenu.toggleClass('show');
            });

            // Handle dropdown item clicks
            $('.dropdown-item').on('click', function() {
                if ($(window).width() < 992) {
                    // Close navbar after selecting an item on mobile
                    setTimeout(() => {
                        $('.navbar-collapse').removeClass('show');
                        $('body').removeClass('menu-open');
                        $('.dropdown').removeClass('show');
                        $('.dropdown-menu').removeClass('show');
                    }, 100);
                }
            });

            // Close navbar when clicking on regular nav links (not dropdowns)
            $('.nav-link:not(.dropdown-toggle)').on('click', function() {
                if ($(window).width() < 992) {
                    $('.navbar-collapse').removeClass('show');
                    $('body').removeClass('menu-open');
                }
            });

            // Close dropdowns when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown').removeClass('show');
                    $('.dropdown-menu').removeClass('show');
                }
            });
        });
    </script>
    @yield('scripts')
</body>
</html>