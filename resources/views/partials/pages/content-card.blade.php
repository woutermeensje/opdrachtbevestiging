<article class="card page-card{{ !empty($class) ? ' '.$class : '' }}">
    @isset($title)
        <h2>{{ $title }}</h2>
    @endisset

    {!! $slot ?? '' !!}
</article>
