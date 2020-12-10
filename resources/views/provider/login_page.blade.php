<html>
<head>

</head>
<body>

<form method="post" action="{{ route('openid_provider.login.accept') }}">
    <input type="hidden" name="login_challenge" value="{{ $login_challenge }}"/><br/>
    <label>Subject <input type="text" name="subject" placeholder="subject"/><br/></label>
    <label><input type="checkbox" name="remember"/> Remember</label><br/>
    <input type="submit"/>
</form>

</body>
</html>