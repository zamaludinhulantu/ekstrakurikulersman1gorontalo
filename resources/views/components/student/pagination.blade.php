@props([
    'paginator',
    'noun' => 'data',
    'perPage' => null,
    'perPageOptions' => [10, 20, 50, 100],
])

@if($paginator->total() > 0)
    <div {{ $attributes->class(['student-pagination']) }}>
        <p>
            Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}
            dari {{ $paginator->total() }} {{ $noun }}
        </p>

        @if($perPage)
            <form method="get" class="student-pagination__size">
                @foreach(request()->except(['page', 'per_page']) as $key => $value)
                    @if(is_scalar($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="student_per_page">Per halaman</label>
                <select id="student_per_page" name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
        @endif

        <div class="student-pagination__links">{{ $paginator->links() }}</div>
    </div>
@endif
