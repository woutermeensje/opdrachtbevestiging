@php
    $size = $size ?? 20;
    $strokeWidth = $strokeWidth ?? 1.8;
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    class="{{ $class ?? '' }}"
    aria-hidden="true"
>
    @switch($name)
        @case('plus-square')
            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
            <path d="M12 8v8"></path>
            <path d="M8 12h8"></path>
            @break

        @case('file-text')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <path d="M14 2v6h6"></path>
            <path d="M16 13H8"></path>
            <path d="M16 17H8"></path>
            <path d="M10 9H8"></path>
            @break

        @case('user-circle')
            <circle cx="12" cy="8" r="4"></circle>
            <path d="M6 20a6 6 0 0 1 12 0"></path>
            @break

        @case('log-out')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <path d="M16 17l5-5-5-5"></path>
            <path d="M21 12H9"></path>
            @break

        @case('home')
            <path d="M3 10.5 12 3l9 7.5"></path>
            <path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"></path>
            @break

        @case('building')
            <rect x="4" y="3" width="16" height="18" rx="2"></rect>
            <path d="M8 7h.01"></path>
            <path d="M12 7h.01"></path>
            <path d="M16 7h.01"></path>
            <path d="M8 11h.01"></path>
            <path d="M12 11h.01"></path>
            <path d="M16 11h.01"></path>
            <path d="M10 21v-4h4v4"></path>
            @break
    @endswitch
</svg>
