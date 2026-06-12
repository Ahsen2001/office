@extends('layouts.admin')

@section('title', 'Edit Service')
@section('page-title', 'Edit Service')

@section('content')
    <div class="card soft-card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.services.update', $service) }}">
                @method('PUT')
                @include('admin.services._form')
            </form>
        </div>
    </div>
@endsection
