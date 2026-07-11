<form action="{{ route('auth.login') }}" method="post">
@csrf
<input type="email" name="email">
<input type="password" name="password">
<button type="submit"></button>
</form>