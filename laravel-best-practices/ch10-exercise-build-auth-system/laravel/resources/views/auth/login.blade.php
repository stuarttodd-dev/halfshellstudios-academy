<form method="post" action="{{ route('login.attempt') }}">
    @csrf
    <label>Email <input type="email" name="email" value="{{ old('email') }}"></label>
    <label>Password <input type="password" name="password"></label>
    <label><input type="checkbox" name="remember" value="1"> Remember</label>
    <button type="submit">Log in</button>
    @if ($errors->any())
        <p>{{ $errors->first() }}</p>
    @endif
</form>
