<form action="{{ route('auth.signup') }}" method="post">
@csrf
<input type="email" name="email">
<input type="text" name="username">
<input type="password" name="password">
<input type="password" name="password_confirmation">
<button type="submit"></button>
</form>