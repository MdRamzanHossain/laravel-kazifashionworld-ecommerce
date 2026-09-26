<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Upload or Overwrite System Files</h2>
            <p class="text-sm text-gray-500 mb-6">
                Use this tool to manually upload files directly to your server (e.g., overriding blade templates, php files). 
                Specify the exact absolute path (or a directory path) where the file should be saved. 
                <strong>Warning:</strong> This will overwrite existing files without confirmation.
            </p>

            <form wire:submit="uploadFile" class="space-y-4">
                <div>
                    <label for="uploadPath" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Target Path (Directory or Exact File Path)
                    </label>
                    <input 
                        type="text" 
                        wire:model="uploadPath" 
                        id="uploadPath" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm text-gray-900 dark:text-white"
                        placeholder="e.g. {{ base_path('resources/views/livewire/header.blade.php') }}"
                    >
                    @error('uploadPath') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div x-data="{
                        handleFileSelect(event) {
                            const file = event.target.files[0];
                            if (!file) return;
                            @this.set('fileName', file.name);
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                @this.set('base64File', e.target.result);
                            };
                            reader.readAsDataURL(file);
                        }
                    }"
                    @file-uploaded.window="$refs.fileInput.value = ''"
                >
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Select File
                    </label>
                    <input 
                        type="file" 
                        x-ref="fileInput"
                        @change="handleFileSelect"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900 dark:file:text-primary-300"
                    >
                    @error('base64File') <span class="text-sm text-red-600 mt-1 block">Please select a valid file.</span> @enderror
                </div>

                <div class="pt-4">
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="uploadFile">Upload & Save File</span>
                        <span wire:loading wire:target="uploadFile">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="p-6 bg-amber-50 dark:bg-amber-900/30 rounded-xl border border-amber-200 dark:border-amber-800">
            <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300 mb-2">Example Paths for your Server</h3>
            <ul class="list-disc pl-5 text-xs text-amber-700 dark:text-amber-400 space-y-1">
                <li><strong>Base Path:</strong> {{ base_path() }}</li>
                <li><strong>Views Path:</strong> {{ resource_path('views') }}</li>
                <li><strong>Livewire Components:</strong> {{ app_path('Livewire') }}</li>
                <li><strong>Public Assets:</strong> {{ public_path() }}</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>