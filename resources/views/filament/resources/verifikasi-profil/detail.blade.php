<div class="space-y-4">
    {{-- Status --}}
    <div class="flex items-center gap-2">
        @if($record->isPending())
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                <x-heroicon-o-clock class="h-4 w-4" />
                Menunggu Verifikasi
            </span>
        @elseif($record->isApproved())
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                <x-heroicon-o-check-circle class="h-4 w-4" />
                Disetujui
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                <x-heroicon-o-x-circle class="h-4 w-4" />
                Ditolak
            </span>
        @endif
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $record->created_at->translatedFormat('d F Y, H:i') }}
        </span>
    </div>

    {{-- Pengaju --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Pengaju</h4>
        <p class="text-base font-medium text-gray-900 dark:text-white">{{ $record->user?->name ?? '-' }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->user?->email ?? '-' }}</p>
    </div>

    {{-- Perubahan --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Detail Perubahan</h4>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/30">
                <tr>
                    <th class="px-4 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Field</th>
                    <th class="px-4 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Data Lama</th>
                    <th class="px-4 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Data Baru</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @php $labels = \App\Models\ProfileChangeRequest::getFieldLabels(); @endphp
                @foreach($record->getChangedFields() as $field => $changes)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $labels[$field] ?? $field }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                            <span class="line-through">{{ $changes['old'] ?: '(kosong)' }}</span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $changes['new'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Info Review --}}
    @if($record->reviewed_at)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Diproses Oleh</h4>
            <p class="text-base font-medium text-gray-900 dark:text-white">{{ $record->reviewer?->name ?? '-' }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->reviewed_at->translatedFormat('d F Y, H:i') }}</p>
        </div>
    @endif

    {{-- Alasan Reject --}}
    @if($record->isRejected() && $record->alasan_reject)
        <div class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
            <h4 class="text-sm font-semibold text-red-700 dark:text-red-400 mb-1">Alasan Penolakan</h4>
            <p class="text-sm text-red-600 dark:text-red-300">{{ $record->alasan_reject }}</p>
        </div>
    @endif
</div>
