<x-filament-panels::page>
    <div class="space-y-6">

        <!-- Top Metrics Cards -->
        @php
            $totalBackups = count($backups);
            $totalBytes = array_sum(array_column($backups, 'size'));
            $totalStorage = \App\Services\BackupService::formatBytes($totalBytes);
            $latestBackup = !empty($backups) ? $backups[0]['created_at'] : 'None';
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Card 1: Total Backups -->
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-4">
                    <div class="rounded-lg bg-pink-50 p-3 text-pink-600 dark:bg-pink-950/50 dark:text-pink-400">
                        <x-filament::icon icon="heroicon-o-circle-stack" class="h-6 w-6" />
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Backups</span>
                        <div class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $totalBackups }} files</div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Disk Space Used -->
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-4">
                    <div class="rounded-lg bg-indigo-50 p-3 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        <x-filament::icon icon="heroicon-o-server-stack" class="h-6 w-6" />
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Backup Storage Used</span>
                        <div class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $totalStorage }}</div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Latest Backup -->
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-4">
                    <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6" />
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Latest Backup</span>
                        <div class="text-sm font-bold tracking-tight text-gray-950 dark:text-white">{{ $latestBackup }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backups List Table -->
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-950 dark:text-white">Existing Backup Archives</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Manage codebase snapshots, database SQL dumps, and full site archives stored on disk.</p>
                </div>
                <button 
                    type="button" 
                    wire:click="loadBackups" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 rounded-lg transition cursor-pointer"
                >
                    <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4" />
                    <span>Refresh</span>
                </button>
            </div>

            @if(empty($backups))
                <div class="p-12 text-center space-y-3">
                    <div class="mx-auto w-12 h-12 rounded-full bg-pink-50 dark:bg-pink-950/50 text-pink-600 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-shield-check" class="h-6 w-6" />
                    </div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">No backup archives created yet</h4>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">Click "Backup Source Code", "Backup Database", or "Full Snapshot" in top header to generate your first backup.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-white/5">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300">Type</th>
                                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300">File Name</th>
                                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300">File Size</th>
                                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300">Created At</th>
                                <th class="px-6 py-3 font-semibold text-gray-700 dark:text-gray-300 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach($backups as $backup)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                    <!-- Type -->
                                    <td class="px-6 py-4">
                                        @if(str_contains($backup['filename'], 'code_backup_'))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950 dark:text-blue-300">
                                                <x-filament::icon icon="heroicon-m-code-bracket" class="h-3.5 w-3.5" />
                                                <span>Codebase</span>
                                            </span>
                                        @elseif(str_contains($backup['filename'], 'full_snapshot_') || str_contains($backup['filename'], 'full'))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950 dark:text-indigo-300">
                                                <x-filament::icon icon="heroicon-m-archive-box" class="h-3.5 w-3.5" />
                                                <span>Full Snapshot</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300">
                                                <x-filament::icon icon="heroicon-m-circle-stack" class="h-3.5 w-3.5" />
                                                <span>Database Dump</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Filename -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 font-mono font-bold text-gray-900 dark:text-white">
                                            <x-filament::icon icon="heroicon-m-document-text" class="h-4 w-4 text-gray-400" />
                                            <span>{{ $backup['filename'] }}</span>
                                        </div>
                                    </td>

                                    <!-- Size -->
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-semibold">
                                        {{ $backup['formatted_size'] }}
                                    </td>

                                    <!-- Created -->
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        <div>{{ $backup['created_at'] }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $backup['relative_age'] }}</div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <!-- Download -->
                                        <a 
                                            href="{{ route('admin.backups.download', $backup['filename']) }}" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 dark:bg-pink-950 dark:text-pink-300 rounded-lg font-bold text-xs transition cursor-pointer"
                                            title="Download Backup to PC"
                                        >
                                            <x-filament::icon icon="heroicon-m-arrow-down-tray" class="h-3.5 w-3.5" />
                                            <span>Download</span>
                                        </a>

                                        <!-- Restore (Universal: Code, DB, or Full) -->
                                        <button 
                                            type="button" 
                                            wire:click="restore('{{ $backup['filename'] }}')"
                                            wire:confirm="WARNING: Restoring {{ $backup['filename'] }} will overwrite current source code and/or database records with this snapshot. All active caches will be cleared. Are you sure you want to proceed?"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 rounded-lg font-bold text-xs transition cursor-pointer"
                                            title="Restore files and/or database from this archive"
                                        >
                                            <x-filament::icon icon="heroicon-m-arrow-path-rounded-square" class="h-3.5 w-3.5" />
                                            <span>Restore</span>
                                        </button>

                                        <!-- Delete -->
                                        <button 
                                            type="button" 
                                            wire:click="delete('{{ $backup['filename'] }}')"
                                            wire:confirm="Permanently delete {{ $backup['filename'] }} from disk storage?"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 rounded-lg font-bold text-xs transition cursor-pointer"
                                            title="Delete backup archive"
                                        >
                                            <x-filament::icon icon="heroicon-m-trash" class="h-3.5 w-3.5" />
                                            <span>Delete</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Automation & Cron Guide Box -->
        <div class="rounded-xl bg-gray-900 text-gray-300 p-6 border border-gray-800 space-y-3">
            <div class="flex items-center gap-2 text-white font-bold text-sm">
                <span>⏰</span>
                <span>Automated Backup Scheduling (Cron / Task Scheduler)</span>
            </div>
            <p class="text-xs text-gray-400">
                You can configure automatic daily or weekly backups on your server by setting up an OS cron job or Windows Task Scheduler to run the following Artisan commands:
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-1">
                <div class="bg-black/50 p-3 rounded-lg border border-gray-800 font-mono text-xs text-blue-400">
                    <span class="text-gray-500 block mb-1"># Codebase Snapshot:</span>
                    php artisan app:backup-run --type=code
                </div>
                <div class="bg-black/50 p-3 rounded-lg border border-gray-800 font-mono text-xs text-emerald-400">
                    <span class="text-gray-500 block mb-1"># Daily Database Backup:</span>
                    php artisan app:backup-run --type=db
                </div>
                <div class="bg-black/50 p-3 rounded-lg border border-gray-800 font-mono text-xs text-indigo-400">
                    <span class="text-gray-500 block mb-1"># Weekly Full Snapshot:</span>
                    php artisan app:backup-run --type=full
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
