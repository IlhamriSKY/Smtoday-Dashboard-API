<script>
    var iklantexts = @json(array_values($iklantextsPerMonth));
    var iklanimages = @json(array_values($iklanimagesPerMonth));
    var months = @json(array_keys($iklantextsPerMonth));
    var trans = {
        chartLabel: "{{ __('Iklantext History')  }}",
        new: "{{ __('new') }}",
        iklantext: "{{ __('iklantext') }}",
        iklantexts: "{{ __('iklantexts') }}"
    };
    // var iklanimages = @json(array_values($iklanimagesPerMonth));
    // var months = @json(array_keys($iklanimagesPerMonth));
    // var trans = {
    //     chartLabel: "{{ __('Iklanimage History')  }}",
    //     new: "{{ __('new') }}",
    //     iklanimage: "{{ __('iklanimage') }}",
    //     iklanimages: "{{ __('iklanimages') }}"
    // };
</script>
{!! HTML::script('assets/js/chartiklan.min.js') !!}
{!! HTML::script('assets/js/as/dashboard-iklan.js') !!}
