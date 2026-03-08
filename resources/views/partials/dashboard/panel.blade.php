<section class="dashboard-panel{{ !empty($class) ? ' '.$class : '' }}">
    @isset($title)
        <h2>{{ $title }}</h2>
    @endisset

    {!! $slot ?? '' !!}
</section>
