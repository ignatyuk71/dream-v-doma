@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Редагувати категорію</h1>

    <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
        @csrf
        @method('PUT')

        <ul class="nav nav-tabs mb-3" id="langTabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#ua">Українська</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#ru">Російська</button>
            </li>
        </ul>

        <div class="tab-content">
            @foreach (['ua', 'ru'] as $locale)
            <div class="tab-pane fade {{ $locale == 'ua' ? 'show active' : '' }}" id="{{ $locale }}">
                <input type="hidden" name="translations[{{ $locale }}][locale]" value="{{ $locale }}">

                <div class="mb-3">
                    <label class="form-label">Назва ({{ $locale }})</label>
                    <input type="text" name="translations[{{ $locale }}][name]" class="form-control"
                        value="{{ old("translations.$locale.name", $translations[$locale]->name ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Title ({{ $locale }})</label>
                    <input type="text" name="translations[{{ $locale }}][meta_title]" class="form-control"
                        value="{{ old("translations.$locale.meta_title", $translations[$locale]->meta_title ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Description ({{ $locale }})</label>
                    <textarea name="translations[{{ $locale }}][meta_description]" class="form-control" required>{{ old("translations.$locale.meta_description", $translations[$locale]->meta_description ?? '') }}</textarea>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label class="form-label">Батьківська категорія</label>
            <select name="parent_id" class="form-select">
                <option value="">Немає</option>
                @foreach ($parentCategories as $parent)
                    <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>
                        {{ $parent->translations->firstWhere('locale', 'ua')?->name ?? '—' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-check form-switch mb-3">
            <input type="hidden" name="status" value="0">
            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusSwitch"
                {{ old('status', $category->status) ? 'checked' : '' }}>
            <label class="form-check-label" for="statusSwitch">Активна</label>
        </div>

        <button type="submit" class="btn btn-success">💾 Зберегти</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">↩️ Назад</a>
    </form>
</div>
@endsection
