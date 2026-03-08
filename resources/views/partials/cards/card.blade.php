<article
    @isset($id) id="{{ $id }}" @endisset
    class="card{{ !empty($class) ? ' '.$class : '' }}"
>
    @isset($image)
        <img
            src="{{ $image['src'] }}"
            alt="{{ $image['alt'] }}"
            class="{{ $image['class'] ?? 'card-media' }}"
        >
    @endisset

    @isset($title)
        <h3>{{ $title }}</h3>
    @endisset

    @isset($text)
        <p>{{ $text }}</p>
    @endisset

    {{ $slot ?? '' }}
</article>
