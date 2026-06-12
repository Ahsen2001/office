@extends('layouts.admin')
@section('title', 'Edit Application')
@section('page-title', 'Edit Application')
@section('content')
<div class="card soft-card"><div class="card-body">
    <form method="POST" action="{{ route('staff.applications.update', $application) }}">@method('PUT') @include('staff.applications._form')</form>
</div></div>
@endsection
