@extends('admin.layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Створити категорію</h1>

    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf

        <!-- Вкладки -->
        <ul class="nav nav-tabs mb-3" id="langTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="uk-tab" data-bs-toggle="tab" data-bs-target="#uk" type="button" role="tab">Українська</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ru-tab" data-bs-toggle="tab" data-bs-target="#ru" type="button" role="tab">Російська</button>
            </li>
        </ul>

        <div class="tab-content" id="langTabsContent">
            <!-- Українська -->
            <div class="tab-pane fade show active" id="uk" role="tabpanel">
                <input type="hidden" name="translations[uk][locale]" value="uk">

                <div class="mb-3">
                    <label class="form-label">Назва (uk)</label>
                    <input type="text" name="translations[uk][name]" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Title (uk)</label>
                    <input type="text" name="translations[uk][meta_title]" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Description (uk)</label>
                    <textarea name="translations[uk][meta_description]" class="form-control" required></textarea>
                </div>
            </div>

            <!-- Російська -->
            <div class="tab-pane fade" id="ru" role="tabpanel">
                <input type="hidden" name="translations[ru][locale]" value="ru">

                <div class="mb-3">
                    <label class="form-label">Назва (ru)</label>
                    <input type="text" name="translations[ru][name]" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Title (ru)</label>
                    <input type="text" name="translations[ru][meta_title]" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Meta Description (ru)</label>
                    <textarea name="translations[ru][meta_description]" class="form-control" required></textarea>
                </div>
            </div>
        </div>

        <!-- Загальні поля -->
        <div class="mb-3">
            <label class="form-label">Батьківська категорія</label>
            <select name="parent_id" class="form-select">
                <option value="">Немає</option>
                @foreach ($parentCategories as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->translations->first()?->name ?? 'Без назви' }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-check form-switch mb-3">
            <input type="hidden" name="status" value="0"> {{-- обов'язково --}}
            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusSwitch" checked>
            <label class="form-check-label" for="statusSwitch">Активна</label>
        </div>

        <button type="submit" class="btn btn-primary">Зберегти</button>
    </form>
</div>
@endsection
