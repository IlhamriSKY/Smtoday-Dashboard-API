<nav class="navbar fixed-top align-items-start navbar-expand-lg pl-0 pr-0 py-0" >

    <div class="navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand mr-0" href="{{ url('/') }}">
            <img src="{{ url('assets/img/sm-logo.png') }}" class="logo-lg" height="35" alt="{{ setting('app_name') }}">
            <img src="{{ url('assets/img/sm-logo-no-text.png') }}" class="logo-sm" height="35" alt="{{ setting('app_name') }}">
        </a>
    </div>

    <div>
        @if (app('impersonate')->isImpersonating())
            <a href="{{ route('impersonate.leave') }}" class="navbar-toggler text-danger hidden-md">
                <i class="fas fa-user-secret"></i>
            </a>
        @endif

        <button class="navbar-toggler" type="button" id="sidebar-toggle">
            <i class="fas fa-align-right text-muted"></i>
        </button>

        <button class="navbar-toggler mr-3"
                type="button"
                data-toggle="collapse"
                data-target="#top-navigation"
                aria-controls="top-navigation"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <i class="fas fa-bars text-muted"></i>
        </button>
    </div>

    <div class="collapse navbar-collapse" id="top-navigation">
        <div class="row ml-2">
            <div class="col-lg-12 d-flex align-items-left align-items-md-center flex-column flex-md-row py-3">
                <h4 class="page-header mb-0">
                    @yield('page-heading')
                </h4>

                <ol class="breadcrumb mb-0 font-weight-light">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}" class="text-muted">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>

                    @yield('breadcrumbs')
                </ol>
            </div>
        </div>

        <ul class="navbar-nav ml-auto pr-3 flex-row">
            @if (app('impersonate')->isImpersonating())
                <li class="nav-item d-flex align-items-center visible-lg">
                    <a href="{{ route('impersonate.leave') }}" class="btn text-danger">
                        <i class="fas fa-user-secret mr-2"></i>
                        @lang('Stop Impersonating')
                    </a>
                </li>
            @endif

            @hook('navbar:items')

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle"
                   href="#"
                   id="navbarDropdown"
                   role="button"
                   data-toggle="dropdown"
                   aria-haspopup="true"
                   aria-expanded="false">
                    <img src="{{ auth()->user()->present()->avatar }}"
                         width="50"
                         height="50"
                         class="rounded-circle img-thumbnail img-responsive">
                </a>
                <div class="dropdown-menu dropdown-menu-right position-absolute p-0" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item py-2" href="{{ route('profile') }}">
                        <i class="fas fa-user text-muted mr-2"></i>
                        @lang('My Profile')
                    </a>

                    @if (config('session.driver') == 'database')
                        <a href="{{ route('profile.sessions') }}" class="dropdown-item py-2">
                            <i class="fas fa-list text-muted mr-2"></i>
                            @lang('Active Sessions')
                        </a>
                    @endif

                    @hook('navbar:dropdown')

                    <div class="dropdown-divider m-0"></div>

                    <a class="dropdown-item py-2" href="{{ route('auth.logout') }}">
                        <i class="fas fa-sign-out-alt text-muted mr-2"></i>
                        @lang('Logout')
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>
