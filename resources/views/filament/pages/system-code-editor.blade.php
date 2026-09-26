<x-filament-panels::page>
        @if(!$isUnlocked)
        <div class="bg-white dark:bg-gray-900 p-8 rounded-2xl shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 max-w-md mx-auto mt-20 text-center relative overflow-hidden">
            <!-- Decorative background gradient -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-500/10 rounded-full blur-3xl"></div>
            
            <div class="mx-auto w-20 h-20 bg-red-50 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-6 relative z-10 shadow-inner ring-1 ring-red-100 dark:ring-red-900">
                <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3 relative z-10">Restricted Area</h2>
            <p class="text-sm text-gray-500 mb-8 relative z-10 font-medium">Please enter your master security PIN to unlock the live file editor.</p>
            
            <form wire:submit.prevent="unlockEditor" class="relative z-10">
                <input 
                    type="password" 
                    wire:model="pinCode" 
                    class="w-full text-center text-3xl tracking-[0.5em] font-mono py-4 rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white mb-6 bg-gray-50 dark:bg-gray-950 transition-all" 
                    placeholder="••••••" 
                    autofocus
                    maxlength="10"
                >
                
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-red-600/30 transition-all transform hover:-translate-y-0.5 active:scale-95 flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                    Unlock Editor
                </button>
            </form>
            
            <p class="text-xs text-gray-400 mt-8 relative z-10">Default PIN is <strong>123456</strong><br>(You can change this in the .env file)</p>
        </div>
    @else
        <div class="space-y-6">
        <!-- Warning Alert -->
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 rounded-xl flex items-start gap-4">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <h3 class="font-bold text-red-800 dark:text-red-300">Danger Zone</h3>
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">Editing raw PHP and Blade files directly on a production server can instantly crash the website if there is a syntax error. Please proceed with extreme caution.</p>
            </div>
        </div>

        <!-- Load File Section -->
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <h2 class="text-lg font-bold mb-4">1. Open a File</h2>
            
            <!-- Intelligent Search Box -->
            <div class="mb-4 relative z-50">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search for a file (Type 3+ letters)</label>
                <div class="relative flex items-center">
                    <svg class="w-5 h-5 absolute left-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input 
                        type="text" 
                        wire:model.live.debounce.500ms="searchQuery" 
                        class="w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                        placeholder="Search by name (e.g. 404.blade.php, app.css, Product.php)..." 
                        autocomplete="off"
                    />
                    
                    <div wire:loading wire:target="searchQuery" class="absolute right-3">
                        <svg class="animate-spin h-5 w-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                @if(!empty($searchResults))
                <div class="absolute w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-2xl max-h-80 overflow-y-auto overflow-x-hidden z-50 ring-1 ring-black/5">
                    <div class="py-1">
                        @foreach($searchResults as $result)
                            @php
                                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $result);
                            @endphp
                            <button 
                                type="button" 
                                wire:click="selectFile('{{ addslashes($result) }}')" 
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex flex-col border-b border-gray-100 dark:border-gray-700/50 last:border-0"
                            >
                                <span class="font-bold text-gray-900 dark:text-white text-sm break-all">{{ basename($result) }}</span>
                                <span class="text-xs text-gray-400 mt-0.5 break-all font-mono">{{ $relativePath }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if(strlen($searchQuery) >= 3 && empty($searchResults))
                <div class="absolute w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl p-4 text-center z-50 text-gray-500 text-sm">
                    No files found matching "{{ $searchQuery }}"
                </div>
                @endif
            </div>

            <!-- Manual Path Input -->
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 mt-6">Or enter exact absolute path:</label>
            <div class="flex flex-col sm:flex-row gap-4 relative z-0">
                <input 
                    type="text" 
                    wire:model="filePath" 
                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white font-mono text-sm"
                    placeholder="Enter absolute file path on server..." 
                />
                <x-filament::button wire:click="loadFile" color="gray" size="lg">
                    Load Content
                </x-filament::button>
            </div>
            <p class="text-xs text-gray-400 mt-2 font-mono">Server Root: {{ base_path() }}</p>
        </div>

        <!-- Edit File Section -->
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">2. Edit Source Code (VS Code Mode)</h2>
                <x-filament::button wire:click="saveFile" color="primary" size="lg">
                    Save Changes
                </x-filament::button>
            </div>
            
            <div class="text-sm font-mono text-brand-600 dark:text-brand-400 mb-2 font-bold flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Editing: {{ $filePath ? basename($filePath) : 'No file selected' }}
            </div>
            
            <!-- Ace Editor Integration via AlpineJS -->
            <div 
                x-data="{
                    content: @entangle('fileContent'),
                    editor: null,
                    initEditor() {
                        if (!window.ace) {
                            let script = document.createElement('script');
                            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.3/ace.js';
                            script.onload = () => this.setupAce();
                            document.head.appendChild(script);
                        } else {
                            this.setupAce();
                        }
                    },
                    setupAce() {
                        this.editor = ace.edit(this.$refs.editorNode);
                        this.editor.setTheme('ace/theme/monokai');
                        this.editor.session.setMode('ace/mode/php');
                        this.editor.setOptions({
                            fontSize: '14px',
                            showPrintMargin: false,
                            wrap: true,
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true
                        });
                        
                        this.editor.setValue(this.content || '', -1);
                        
                        this.editor.session.on('change', () => {
                            this.content = this.editor.getValue();
                        });
                        
                        this.$watch('content', (val) => {
                            if (this.editor.getValue() !== val) {
                                this.editor.setValue(val || '', -1);
                            }
                            
                            let path = $wire.get('filePath');
                            if (path) {
                                if (path.endsWith('.blade.php') || path.endsWith('.html')) {
                                    this.editor.session.setMode('ace/mode/html');
                                } else if (path.endsWith('.php')) {
                                    this.editor.session.setMode('ace/mode/php');
                                } else if (path.endsWith('.css')) {
                                    this.editor.session.setMode('ace/mode/css');
                                } else if (path.endsWith('.js')) {
                                    this.editor.session.setMode('ace/mode/javascript');
                                } else if (path.endsWith('.json')) {
                                    this.editor.session.setMode('ace/mode/json');
                                }
                            }
                        });
                    }
                }"
                x-init="initEditor()"
                class="w-full flex-1 rounded-lg border border-gray-700 overflow-hidden relative shadow-inner"
                style="min-height: 75vh;"
                wire:ignore
            >
                <div x-ref="editorNode" class="absolute inset-0"></div>
            </div>
            
            <p class="text-xs text-gray-500 mt-4 text-center">Powered by Ace Editor. Saving will instantly overwrite the file on the server.</p>
        </div>
    </div>
    @endif
</x-filament-panels::page>