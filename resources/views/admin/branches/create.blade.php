@extends('layouts.admin')
@section('title', 'Add Branch')
@section('page-title', 'Add Branch')
@section('content')<div class="card soft-card"><div class="card-body"><form method="POST" action="{{ route('admin.branches.store') }}">@include('admin.branches._form')</form></div></div>@endsection
