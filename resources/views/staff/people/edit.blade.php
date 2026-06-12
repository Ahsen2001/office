@extends('layouts.admin')

@section('title', 'Edit Person')
@section('page-title', 'Edit Person')

@section('content')
    <div class="card soft-card">
        <div class="card-body">
            <form method="POST" action="{{ route('staff.people.update', $person) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('staff.people._form')
            </form>
        </div>
    </div>
@endsection
