@component('mail::message')

# {{ $announcement->title }}

{!! $announcement->body !!}

@lang('Thanks'),<br>
{{ config('app.name') }}

@endcomponent
