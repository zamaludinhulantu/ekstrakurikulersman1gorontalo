@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-3" role="alert">
        <i class="bi bi-check-circle-fill fs-5 mt-1"></i>
        <div class="flex-grow-1">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
        <div class="flex-grow-1">{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-warning d-flex align-items-start gap-3" role="alert">
        <i class="bi bi-exclamation-circle-fill fs-5 mt-1"></i>
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
