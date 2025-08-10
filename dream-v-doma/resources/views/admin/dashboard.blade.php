@extends('admin.layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  {{-- Card: Congrats --}}
  <div class="col-span-1 lg:col-span-1 bg-white p-6 rounded-xl shadow">
    <h2 class="text-xl font-semibold">🎉 Congratulations Katie!</h2>
    <p class="text-sm text-gray-500">Best seller of the month</p>
    <div class="text-2xl font-bold text-indigo-600 mt-2">$42.8k</div>
    <p class="text-sm text-gray-400">78% of target 🚀</p>
    <a href="#" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded-md">View Sales</a>
  </div>

  {{-- Card: New Visitors --}}
  <div class="bg-white p-6 rounded-xl shadow">
    <h3 class="text-sm text-gray-500">New Visitors</h3>
    <div class="text-2xl font-bold text-red-500">23%</div>
    <p class="text-sm text-gray-400">↓ 8.75% from last week</p>
    {{-- Graph placeholder --}}
    <div class="h-16 mt-3 bg-gray-100 rounded"></div>
  </div>

  {{-- Card: Activity --}}
  <div class="bg-white p-6 rounded-xl shadow">
    <h3 class="text-sm text-gray-500">Activity</h3>
    <div class="text-2xl font-bold text-green-500">82%</div>
    <p class="text-sm text-gray-400">↑ 19.6% from last week</p>
    <div class="h-16 mt-3 bg-gray-100 rounded"></div>
  </div>
</div>
@endsection