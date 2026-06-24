@extends('layouts.app')

@section('title', __('Family Tree'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('Family Tree') }}</h1>
        <p class="text-gray-600 mt-1">{{ __('Explore your interactive family tree. Zoom, pan and click on nodes to see more information.') }}</p>
        @if($people->count() === 0)
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-800">{{ __('No people registered.') }} <a href="/admin" class="underline font-medium">{{ __('Go to admin panel') }}</a> {{ __('to add family members.') }}</p>
            </div>
        @endif
    </div>
</div>

<div class="relative">
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        <div id="tree-container">
            <div class="loading">
                <svg class="animate-spin h-8 w-8 text-blue-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Loading family tree...') }}
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 absolute inset-0 pointer-events-none">
        <div class="controls pointer-events-auto">
            <button onclick="zoomIn()" title="{{ __('Zoom in') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <button onclick="zoomOut()" title="{{ __('Zoom out') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
            </button>
            <button onclick="resetZoom()" title="{{ __('Reset') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 self-center"></div>
            <button onclick="exportSVG()" title="{{ __('Export SVG') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<div id="person-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div id="modal-content" class="p-6"></div>
    </div>
</div>
@endsection

@if($people->count() > 0)
    @push('scripts')
    <script>
    window.__TREE_CONFIG = {
        apiUrl: '/api/tree/full',
        translations: {
            noData: '{{ __("No data available") }}',
            errorLoading: '{{ __("Error loading tree") }}',
            children: '{{ __("children") }}',
            birth: '{{ __("Birth:") }}',
            death: '{{ __("Death:") }}',
            gender: '{{ __("Gender:") }}',
            male: '{{ __("Male") }}',
            female: '{{ __("Female") }}',
            notSpecified: '{{ __("Not specified") }}',
            childrenLabel: '{{ __("Children:") }}',
            biography: '{{ __("Biography") }}',
            viewFullTree: '{{ __("View full tree of") }}',
            n: '{{ __("N.") }}',
        }
    };
    </script>
    @endpush
@endif
