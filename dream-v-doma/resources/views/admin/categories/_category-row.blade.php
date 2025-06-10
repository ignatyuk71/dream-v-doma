<tr>
    <td>{{ $category->id }}</td>
    <td class="ps-{{ $level * 2 }}">
        @if ($level > 0)
            <span class="text-secondary">↳</span>
        @endif
        {{ $category->translations->firstWhere('locale', 'uk')?->name ?? '-' }}
    </td>
    <td><code>{{ $category->slug }}</code></td>
    <td>
        <span class="badge bg-{{ $category->status ? 'success' : 'secondary' }}">
            {{ $category->status ? 'Активна' : 'Неактивна' }}
        </span>
    </td>
    <td class="text-end">
        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-secondary">✏️</a>
        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Ви впевнені?')" style="display:inline-block">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-secondary">🗑️</button>
        </form>
    </td>
</tr>

@foreach ($categories->where('parent_id', $category->id) as $child)
    @include('admin.categories._category-row', ['category' => $child, 'categories' => $categories, 'level' => $level + 1])
@endforeach
