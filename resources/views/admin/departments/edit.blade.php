@extends('layouts.admin')

@section('title', 'Edit Department')
@section('page-title', 'Edit Department')

@section('content')
    <div class="card soft-card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                @method('PUT')
                @include('admin.departments._form')
            </form>
        </div>
    </div>
@endsection
