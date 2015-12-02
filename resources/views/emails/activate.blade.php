Click here to activate your account:

<form class="form-horizontal" role="form" method="POST" action="{{ url('/activation') }}">
    {!! csrf_field() !!}
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-lg btn-warning">
                Activate Account
            </button>
        </div>
    </div>
</form>