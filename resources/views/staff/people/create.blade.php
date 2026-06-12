@extends('layouts.admin')

@section('title', 'Register Person')
@section('page-title', 'Register Person')

@section('content')
    <div class="card soft-card">
        <div class="card-body">
            <form method="POST" action="{{ route('staff.people.store') }}" enctype="multipart/form-data">
                @include('staff.people._form')
            </form>
        </div>
    </div>
@endsection
