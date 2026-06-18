@extends('layouts.admin')
@section('title', 'Edit Branch')
@section('page-title', 'Edit Branch')
@section('content')<div class="card soft-card"><div class="card-body"><form method="POST" action="{{ route('admin.branches.update', $branch) }}">@method('PUT') @include('admin.branches._form')</form></div></div>@endsection
