<ul class="mk-directory">
    @foreach (config('opstellen.types') as $slug => $name)
        <li>
            <a class="mk-directory__item" href="{{ route('opstellen.show', $slug) }}">
                <span class="mk-directory__name">{{ $name }} opstellen</span>
            </a>
        </li>
    @endforeach
</ul>
