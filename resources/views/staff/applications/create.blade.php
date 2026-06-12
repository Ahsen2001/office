@extends('layouts.admin')
@section('title', 'Create Application')
@section('page-title', 'Create Application')
@section('content')
<div class="card soft-card"><div class="card-body">
    <form method="POST" action="{{ route('staff.applications.store') }}">@include('staff.applications._form')</form>
</div></div>
@endsection
