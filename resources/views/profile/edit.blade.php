@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
    <div class="profile-settings-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
            <div class="profile-avatar">{{ str($user->name)->substr(0, 1)->upper() }}</div>
            <div class="flex-grow-1">
                <div class="text-uppercase small fw-bold opacity-75 mb-1">Account settings</div>
                <h1 class="h2 mb-1">{{ $user->name }}</h1>
                <div class="opacity-75">{{ $user->email }}</div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach($user->roles as $role)
                        <span class="badge rounded-pill bg-white text-primary">{{ $role->name }}</span>
                    @endforeach
                    @if($user->branch)
                        <span class="badge rounded-pill bg-white bg-opacity-25 text-white">{{ $user->branch->name }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route($user->primaryDashboardRoute()) }}" class="btn btn-light">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    @if(session('status') === 'profile-updated')
        <div class="alert alert-success">Profile information updated successfully.</div>
    @elseif(session('status') === 'password-updated')
        <div class="alert alert-success">Password updated successfully.</div>
    @endif

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card soft-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="metric-icon"><i class="fa-regular fa-address-card"></i></span>
                        <div><h2 class="h5 mb-1">Profile information</h2><p class="text-muted mb-0">Keep your staff account details current.</p></div>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="name" class="form-label">Full name</label>
                            <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required autocomplete="name">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="username">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Assigned branch</label>
                            <input value="{{ $user->branch?->name ?? 'Office-wide access' }}" class="form-control" disabled>
                            <div class="form-text">Branch assignments are managed by an administrator.</div>
                        </div>
                        <button class="btn btn-primary"><i class="fa-regular fa-floppy-disk me-1"></i> Save profile</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card soft-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="metric-icon success"><i class="fa-solid fa-key"></i></span>
                        <div><h2 class="h5 mb-1">Update password</h2><p class="text-muted mb-0">Use a strong, unique password.</p></div>
                    </div>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current password</label>
                            <input id="current_password" type="password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                            @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New password</label>
                            <input id="new_password" type="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                            @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm new password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                        <button class="btn btn-success"><i class="fa-solid fa-shield-halved me-1"></i> Update password</button>
                    </form>
                </div>
            </div>

            <div class="card border-danger-subtle rounded-4">
                <div class="card-body p-4">
                    <h2 class="h6 text-danger">Delete account</h2>
                    <p class="small text-muted">This permanently removes your login. Enter your password to confirm.</p>
                    <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Permanently delete your account?')">
                        @csrf
                        @method('DELETE')
                        <div class="input-group">
                            <input type="password" name="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="Current password" required>
                            <button class="btn btn-outline-danger">Delete</button>
                        </div>
                        @error('password', 'userDeletion')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
