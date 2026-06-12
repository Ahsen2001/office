@extends('layouts.admin')

@section('title', 'Add Service')
@section('page-title', 'Add Service')

@section('content')
    <div class="card soft-card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.services.store') }}">
                @include('admin.services._form')
            </form>
        </div>
    </div>
@endsection
