<form method="post" action="{{ route('register.attempt') }}">
    @csrf
    <label>Name <input name="name" value="{{ old('name') }}"></label>
    <label>Email <input type="email" name="email" value="{{ old('email') }}"></label>
    <label>Password <input type="password" name="password"></label>
    <label>Confirm <input type="password" name="password_confirmation"></label>
    <button type="submit">Register</button>
    @if ($errors->any())
        <p>{{ $errors->first() }}</p>
    @endif
</form>
