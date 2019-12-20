<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('page-title') - {{ setting('app_name') }}</title>

    {!! HTML::style('assets/css/app.css') !!}
    {!! HTML::style('assets/css/fontawesome-all.min.css') !!}

    @yield('header-scripts')

    @hook('auth:styles')
</head>
<body class="auth">

    <div class="container">
        @yield('content')

        <!-- <footer id="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="text-center">
                            <p>CopyRight © - {{ setting('app_name') }} {{ date('Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer> -->
    </div>

    {!! HTML::script('assets/js/vendor.js') !!}
    {!! HTML::script('assets/js/as/app.js') !!}
    {!! HTML::script('assets/js/as/btn.js') !!}
    @yield('scripts')
    @hook('auth:scripts')
</body>
</html>
