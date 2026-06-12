@extends('layouts.admin')

@section('title', 'Edit Appointment')
@section('page-title', 'Edit Appointment')

@section('content')
    <div class="card soft-card"><div class="card-body">
        <form method="POST" action="{{ route('staff.appointments.update', $appointment) }}">
            @method('PUT')
            @include('staff.appointments._form')
        </form>
    </div></div>
@endsection
