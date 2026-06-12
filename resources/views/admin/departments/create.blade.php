@extends('layouts.admin')

@section('title', 'Add Department')
@section('page-title', 'Add Department')

@section('content')
    <div class="card soft-card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.departments.store') }}">
                @include('admin.departments._form')
            </form>
        </div>
    </div>
@endsection
