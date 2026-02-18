<x-filament-panels::page>
    {{-- Banner Pending Request --}}
    @php
        $pendingRequest = $this->getPendingRequest();
        $latestRequests = $this->getLatestRequests();
    @endphp

    @if($pendingRequest)
        <div class="rounded-xl border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 p-4 mb-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <x-heroicon-o-clock class="h-6 w-6 text-yellow-500" />
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">Menunggu Verifikasi Super Admin</h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        Anda telah mengajukan perubahan data pada {{ $pendingRequest->created_at->translatedFormat('d F Y, H:i') }}.
                        Perubahan akan diterapkan setelah diverifikasi oleh Super Admin.
                    </p>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        @foreach($pendingRequest->getChangedFields() as $field => $changes)
                            <div class="bg-yellow-100 dark:bg-yellow-900/50 rounded-lg p-2 border border-yellow-200 dark:border-yellow-700/50">
                                <span class="font-medium text-yellow-900 dark:text-yellow-100">
                                    {{ \App\Models\ProfileChangeRequest::getFieldLabels()[$field] ?? $field }}:
                                </span>
                                <div class="mt-1">
                                    <span class="line-through text-yellow-700 dark:text-yellow-300">{{ $changes['old'] ?: '(kosong)' }}</span>
                                    <span class="text-yellow-900 dark:text-yellow-100 mx-1">&rarr;</span>
                                    <span class="font-semibold text-yellow-900 dark:text-yellow-50">{{ $changes['new'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Form --}}
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                @if($this->isSuperAdmin)
                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-shield-check class="h-4 w-4" />
                        Perubahan langsung diterapkan (Super Admin)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-shield-exclamation class="h-4 w-4" />
                        Perubahan memerlukan persetujuan Super Admin
                    </span>
                @endif
            </div>
            <x-filament::button
                type="submit"
                icon="{{ $this->isSuperAdmin ? 'heroicon-o-check-circle' : 'heroicon-o-paper-airplane' }}"
                :disabled="$pendingRequest !== null && !$this->isSuperAdmin"
            >
                {{ $this->isSuperAdmin ? 'Simpan Perubahan' : 'Ajukan Perubahan' }}
            </x-filament::button>
        </div>
    </form>

    {{-- Riwayat Pengajuan (hanya untuk Kepala Cabang Dinas) --}}
    @if(!$this->isSuperAdmin && $latestRequests->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                Riwayat Pengajuan
            </h3>
            <div class="space-y-3">
                @foreach($latestRequests as $request)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($request->isPending())
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold shadow-sm" style="background-color: #eab308; color: #fff;">
                                        <x-heroicon-o-clock class="h-4 w-4" />
                                        Menunggu Verifikasi
                                    </span>
                                @elseif($request->isApproved())
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold shadow-sm" style="background-color: #22c55e; color: #fff;">
                                        <x-heroicon-o-check-circle class="h-4 w-4" />
                                        Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold shadow-sm" style="background-color: #ef4444; color: #fff;">
                                        <x-heroicon-o-x-circle class="h-4 w-4" />
                                        Ditolak
                                    </span>
                                @endif
                                <span class="text-xs text-gray-600 dark:text-gray-300">
                                    {{ $request->created_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            </div>
                            @if($request->reviewed_at)
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Diproses: {{ $request->reviewed_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            @endif
                        </div>

                        {{-- Changed fields --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($request->getChangedFields() as $field => $changes)
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2.5 py-1.5 rounded border border-gray-200 dark:border-gray-600">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ \App\Models\ProfileChangeRequest::getFieldLabels()[$field] ?? $field }}:</span>
                                    <span class="line-through text-gray-500 dark:text-gray-400">{{ $changes['old'] ?: '(kosong)' }}</span>
                                    <span class="mx-1 text-gray-400">&rarr;</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $changes['new'] }}</span>
                                </span>
                            @endforeach
                        </div>

                        @if($request->isRejected() && $request->alasan_reject)
                            <div class="mt-3 text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                <span class="font-semibold">Alasan:</span> {{ $request->alasan_reject }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($latestRequests->hasPages())
                <div class="mt-4">
                    {{ $latestRequests->links() }}
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
