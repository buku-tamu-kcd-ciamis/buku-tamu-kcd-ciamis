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
                            <div class="bg-yellow-100 dark:bg-yellow-900/40 rounded-lg p-2">
                                <span class="font-medium text-yellow-900 dark:text-yellow-100">
                                    {{ \App\Models\ProfileChangeRequest::getFieldLabels()[$field] ?? $field }}:
                                </span>
                                <div class="mt-1">
                                    <span class="line-through text-yellow-600 dark:text-yellow-400">{{ $changes['old'] ?: '(kosong)' }}</span>
                                    <span class="text-yellow-800 dark:text-yellow-200 mx-1">&rarr;</span>
                                    <span class="font-semibold text-yellow-900 dark:text-yellow-100">{{ $changes['new'] }}</span>
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

    {{-- Riwayat Pengajuan (hanya untuk Ketua KCD) --}}
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
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        <x-heroicon-o-clock class="h-3.5 w-3.5" />
                                        Menunggu Verifikasi
                                    </span>
                                @elseif($request->isApproved())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        <x-heroicon-o-check-circle class="h-3.5 w-3.5" />
                                        Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                        <x-heroicon-o-x-circle class="h-3.5 w-3.5" />
                                        Ditolak
                                    </span>
                                @endif
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $request->created_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            </div>
                            @if($request->reviewed_at)
                                <span class="text-xs text-gray-400">
                                    Diproses: {{ $request->reviewed_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            @endif
                        </div>

                        {{-- Changed fields --}}
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($request->getChangedFields() as $field => $changes)
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded">
                                    {{ \App\Models\ProfileChangeRequest::getFieldLabels()[$field] ?? $field }}:
                                    <span class="line-through">{{ $changes['old'] ?: '(kosong)' }}</span>
                                    &rarr;
                                    <span class="font-medium">{{ $changes['new'] }}</span>
                                </span>
                            @endforeach
                        </div>

                        @if($request->isRejected() && $request->alasan_reject)
                            <div class="mt-2 text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded p-2">
                                <span class="font-medium">Alasan:</span> {{ $request->alasan_reject }}
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
