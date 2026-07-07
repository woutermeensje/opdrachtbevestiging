<section class="auth-card">
    <h1 class="form-title">Inloggen</h1>
    <p class="form-subtitle">Log in om je opdrachtbevestigingen te beheren.</p>

    @include('partials.forms.status')
    @include('partials.forms.errors')

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <label for="email">E-mailadres</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required>

        <label for="password">Wachtwoord</label>
        <input id="password" name="password" type="password" required>

        <p class="auth-inline-link">
            <a href="{{ route('password.request') }}">Wachtwoord vergeten?</a>
        </p>

        <div class="actions">
            <a href="{{ route('register') }}" class="btn btn-secondary">Maak een account</a>
            <button type="submit" class="btn btn-primary">Inloggen</button>
        </div>
    </form>
</section>
