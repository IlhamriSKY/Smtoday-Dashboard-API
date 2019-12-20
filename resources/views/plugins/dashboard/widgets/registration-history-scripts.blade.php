<script>
    var users = @json(array_values($usersPerMonth));
    var months = @json(array_keys($usersPerMonth));
    var trans = {
        chartLabel: "{{ __('Registration History')  }}",
        new: "{{ __('new') }}",
        user: "{{ __('user') }}",
        users: "{{ __('users') }}"
    };
</script>
{!! HTML::script('assets/js/chart.min.js') !!}
{!! HTML::script('assets/js/as/dashboard-admin.js') !!}
