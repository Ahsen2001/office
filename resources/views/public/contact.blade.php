@extends('layouts.public')

@section('title', 'Contact Us - '.config('app.name'))

@section('content')
    <header class="public-page-header">
        <div class="container">
            <span class="public-eyebrow"><i class="fa-regular fa-envelope"></i> Contact the office</span>
            <h1 class="mt-2 mb-3">We are here to help</h1>
            <p>Contact the office for service guidance, document requirements, appointment questions, or help locating the correct department.</p>
        </div>
    </header>

    <section class="public-section">
        <div class="container">
            <div class="row g-4 mb-5">
                @foreach([
                    ['Address', $office['address'], 'fa-location-dot'],
                    ['Contact Number', $office['phone'], 'fa-phone'],
                    ['Email Address', $office['email'], 'fa-envelope'],
                    ['Working Hours', $office['hours'], 'fa-clock'],
                ] as [$label, $value, $icon])
                    <div class="col-md-6 col-xl-3">
                        <div class="card public-card"><div class="card-body"><span class="public-icon green mb-3"><i class="fa-solid {{ $icon }}"></i></span><div class="small text-muted">{{ $label }}</div><div class="fw-semibold">{{ $value }}</div></div></div>
                    </div>
                @endforeach
            </div>

            <div class="section-heading mb-4">
                <span class="public-eyebrow text-primary"><i class="fa-solid fa-paper-plane"></i> Send a message</span>
                <h2>Tell us how we can assist</h2>
                <p>Messages are saved securely for authorized administrators to review.</p>
            </div>

            <div class="row g-4 align-items-stretch contact-layout">
                <div class="col-lg-7">
                    <form method="POST" action="{{ route('public.contact.store') }}" class="public-card contact-form-card p-4">
                        @csrf
                        <div class="honeypot-field" aria-hidden="true">
                            <label for="website">Website</label>
                            <input id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label fw-semibold">Full name</label>
                                <input id="full_name" name="full_name" value="{{ old('full_name') }}" class="form-control @error('full_name') is-invalid @enderror" required>
                                @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email address</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone number</label>
                                <input id="phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="subject" class="form-label fw-semibold">Subject</label>
                                <input id="subject" name="subject" value="{{ old('subject') }}" class="form-control @error('subject') is-invalid @enderror" required>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label fw-semibold">Message</label>
                                <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <button class="btn btn-public-primary public-btn" type="submit"><i class="fa-solid fa-paper-plane me-1"></i> Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-5">
                    <div class="public-map contact-map h-100">
                        <div>
                            <span class="public-icon green mb-3"><i class="fa-solid fa-map-location-dot"></i></span>
                            <h2 class="h5">Google Map</h2>
                            <p class="mb-1">{{ $office['address'] }}</p>
                            <small>Map integration placeholder. Add an approved Google Maps embed URL when available.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
