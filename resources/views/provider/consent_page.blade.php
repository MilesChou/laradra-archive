<html>
<head>

</head>
<body>

<form method="post" action="{{ route('openid_provider.consent.provider') }}">
    <input type="hidden" name="consent_challenge" value="{{ $consent_challenge }}"/><br/>
    @foreach($scopes as $scope)
        <label><input type="checkbox" name="{{ $scope }}"/> {{ $scope }}</label><br/>
    @endforeach
    <br/>
    <label><input type="checkbox" name="remember"/> Remember</label><br/>
    <label><input type="submit" value="Cancel"/> <input type="submit" value="Confirm"/></label>
</form>

</body>
</html>