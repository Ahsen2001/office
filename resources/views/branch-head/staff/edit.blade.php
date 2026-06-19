@extends('layouts.admin')
@section('title', 'Edit Branch Staff')
@section('page-title', 'Edit Branch Staff')
@section('content')
<div class="card soft-card"><div class="card-body p-4">
    <h1 class="h4 mb-4">Edit {{ $staff->name }}</h1>
    <form method="POST" action="{{ route('branch-head.staff.update', $staff) }}">
        @method('PUT')
        @include('branch-head.staff._form')
    </form>
</div></div>
@endsection
