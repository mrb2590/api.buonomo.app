@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-md-9 col-lg-8 col-xl-7">
            <passport-clients></passport-clients>
        </div>
        <div class="col-sm-12 col-md-9 col-lg-8 col-xl-7">
            <passport-authorized-clients></passport-authorized-clients>
        </div>
        <div class="col-sm-12 col-md-9 col-lg-8 col-xl-7">
            <passport-personal-access-tokens></passport-personal-access-tokens>
        </div>
    </div>
</div>
@endsection
