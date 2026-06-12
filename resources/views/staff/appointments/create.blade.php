@extends('layouts.admin')

@section('title', 'Book Appointment')
@section('page-title', 'Book Appointment')

@section('content')
    <div class="card soft-card"><div class="card-body">
        <form method="POST" action="{{ $application ? route('staff.appointments.store', $application) : route('staff.appointments.general.store') }}">
            @include('staff.appointments._form')
        </form>
    </div></div>
@endsection
