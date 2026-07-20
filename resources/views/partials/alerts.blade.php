@if(session('success') && !session()->has('success_modal'))
    <div class="alert alert-success alert-dismissible fade show app-alert" role="alert">
        <i class="bi bi-check-circle-fill app-alert__icon"></i>
        <div class="flex-grow-1">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show app-alert" role="alert">
        <i class="bi bi-exclamation-triangle-fill app-alert__icon"></i>
        <div class="flex-grow-1">{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="alert alert-warning app-alert" role="alert">
        <i class="bi bi-exclamation-circle-fill app-alert__icon"></i>
        <div>
            @if(request()->routeIs('login') && $errors->has('email') && $errors->count() === 1)
                <strong>Email atau password tidak sesuai. Silakan periksa kembali.</strong>
            @else
                <strong>Periksa kembali input Anda:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
