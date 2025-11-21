@extends('layouts.app')

@section('title', 'Fixed Assets')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <livewire:accounting.fixed-asset-index />
    </div>
</div>

<!-- Modals -->
<div x-data="{ 
    showAssetForm: false,
    showDepreciationModal: false,
    editingAsset: null,
    assetId: null
}" x-init="$listen('open-asset-form', () => { showAssetForm = true; editingAsset = null; })"
     x-init="$listen('edit-asset', (e) => { showAssetForm = true; editingAsset = e.detail.id; })"
     x-init="$listen('asset-saved', () => { showAssetForm = false; editingAsset = null; $wire.$refresh(); })"
     x-init="$listen('open-depreciation-modal', () => { showDepreciationModal = true; })"
     @class="{ 'overflow-hidden': showAssetForm || showDepreciationModal }">
    
    <!-- Asset Form Modal -->
    <div x-show="showAssetForm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-500 bg-opacity-75"
         style="display: none;">
        
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                @if(isset($editingAsset))
                    <livewire:accounting.fixed-asset-form :asset="$editingAsset" />
                @else
                    <livewire:accounting.fixed-asset-form />
                @endif
            </div>
        </div>
    </div>

    <!-- Depreciation Modal -->
    <div x-show="showDepreciationModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-500 bg-opacity-75"
         style="display: none;">
        
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <livewire:accounting.depreciation-posting />
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<script>
    window.addEventListener('show-message', (event) => {
        const { type, message } = event.detail;
        
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' :
                    'fa-info-circle'
                } mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    });
</script>
@endsection