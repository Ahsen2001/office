@extends('layouts.admin')

@section('title', 'Add User')
@section('page-title', 'Add User')

@section('content')
    <div class="card soft-card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @include('admin.users._form')
            </form>
        </div>
    </div>
@endsection
