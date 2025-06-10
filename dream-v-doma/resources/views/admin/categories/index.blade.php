@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2">
            ✅ <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📂 Категорії</h2>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-primary">
            ➕ Додати категорію
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px">#</th>
                        <th>Назва (uk)</th>
                        <th>Slug</th>
                        <th>Статус</th>
                        <th class="text-end" style="width: 120px">Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories->where('parent_id', null) as $category)
                        @include('admin.categories._category-row', ['category' => $category, 'categories' => $categories, 'level' => 0])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
