<header class="dashboard-page-header">
    <p class="dashboard-eyebrow">{{ $eyebrow ?? 'Dashboard' }}</p>
    <h1>{{ $title }}</h1>
    @isset($text)
        <p class="dashboard-page-text">{{ $text }}</p>
    @endisset
</header>
