@extends('layouts.admin')
@section('title', 'Create Branch Staff')
@section('page-title', 'Create Branch Staff')
@section('content')
<div class="card soft-card"><div class="card-body p-4">
    <h1 class="h4 mb-1">New {{ $branch->name }} officer</h1>
    <p class="text-muted mb-4">This account will be restricted to {{ $branch->name }}.</p>
    <form method="POST" action="{{ route('branch-head.staff.store') }}">
        @include('branch-head.staff._form')
    </form>
</div></div>
@endsection
