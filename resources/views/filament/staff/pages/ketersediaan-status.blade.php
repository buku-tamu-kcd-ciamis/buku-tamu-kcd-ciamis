<x-filament-panels::page>
    <div class="max-w-2xl mx-auto space-y-8">
        {{-- Current Status Card --}}
        <div class="rounded-2xl border-2 bg-white dark:bg-gray-800 shadow-lg overflow-hidden p-8 text-center
            {{ match($availability_status) {
                'available' => 'border-green-300 dark:border-green-700',
                'busy' => 'border-yellow-300 dark:border-yellow-700',
                'out_of_office' => 'border-red-300 dark:border-red-700',
                default => 'border-gray-300 dark:border-gray-700',
            } }}">
            
            <div class="mb-4">
                @switch($availability_status)
                    @case('available')
                        <div class="w-20 h-20 mx-auto rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                            <x-heroicon-o-check-circle class="w-10 h-10 text-green-500" />
                        </div>
                        <h3 class="text-2xl font-bold text-green-600 dark:text-green-400">Tersedia</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Anda siap menerima tamu</p>
                        @break
                    @case('busy')
                        <div class="w-20 h-20 mx-auto rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mb-4">
                            <x-heroicon-o-clock class="w-10 h-10 text-yellow-500" />
                        </div>
                        <h3 class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">Sibuk</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Anda sedang dalam kesibukan</p>
                        @break
                    @case('out_of_office')
                        <div class="w-20 h-20 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
                            <x-heroicon-o-x-circle class="w-10 h-10 text-red-500" />
                        </div>
                        <h3 class="text-2xl font-bold text-red-600 dark:text-red-400">Tidak di Kantor</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Anda sedang tidak berada di kantor</p>
                        @break
                @endswitch
            </div>
        </div>

        {{-- Status Options --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Available --}}
            <button 
                wire:click="updateStatus('available')" 
                class="group relative rounded-2xl border-2 p-6 text-center transition-all duration-300 hover:shadow-lg
                    {{ $availability_status === 'available' 
                        ? 'border-green-400 bg-green-50 dark:bg-green-900/20 dark:border-green-600 shadow-md ring-2 ring-green-200 dark:ring-green-800' 
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-green-300 dark:hover:border-green-600' }}"
            >
                <div class="w-14 h-14 mx-auto rounded-xl flex items-center justify-center mb-3 transition-all duration-300
                    {{ $availability_status === 'available' 
                        ? 'bg-green-500 text-white shadow-lg' 
                        : 'bg-green-100 dark:bg-green-900/30 text-green-500 group-hover:bg-green-500 group-hover:text-white' }}">
                    <x-heroicon-o-check-circle class="w-7 h-7" />
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-white">Tersedia</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Siap menerima tamu</p>
                @if($availability_status === 'available')
                    <div class="absolute top-3 right-3">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                    </div>
                @endif
            </button>

            {{-- Busy --}}
            <button 
                wire:click="updateStatus('busy')" 
                class="group relative rounded-2xl border-2 p-6 text-center transition-all duration-300 hover:shadow-lg
                    {{ $availability_status === 'busy' 
                        ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-600 shadow-md ring-2 ring-yellow-200 dark:ring-yellow-800' 
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-yellow-300 dark:hover:border-yellow-600' }}"
            >
                <div class="w-14 h-14 mx-auto rounded-xl flex items-center justify-center mb-3 transition-all duration-300
                    {{ $availability_status === 'busy' 
                        ? 'bg-yellow-500 text-white shadow-lg' 
                        : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-500 group-hover:bg-yellow-500 group-hover:text-white' }}">
                    <x-heroicon-o-clock class="w-7 h-7" />
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-white">Sibuk</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dalam kesibukan</p>
                @if($availability_status === 'busy')
                    <div class="absolute top-3 right-3">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                        </span>
                    </div>
                @endif
            </button>

            {{-- Out of Office --}}
            <button 
                wire:click="updateStatus('out_of_office')" 
                class="group relative rounded-2xl border-2 p-6 text-center transition-all duration-300 hover:shadow-lg
                    {{ $availability_status === 'out_of_office' 
                        ? 'border-red-400 bg-red-50 dark:bg-red-900/20 dark:border-red-600 shadow-md ring-2 ring-red-200 dark:ring-red-800' 
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-red-300 dark:hover:border-red-600' }}"
            >
                <div class="w-14 h-14 mx-auto rounded-xl flex items-center justify-center mb-3 transition-all duration-300
                    {{ $availability_status === 'out_of_office' 
                        ? 'bg-red-500 text-white shadow-lg' 
                        : 'bg-red-100 dark:bg-red-900/30 text-red-500 group-hover:bg-red-500 group-hover:text-white' }}">
                    <x-heroicon-o-x-circle class="w-7 h-7" />
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-white">Tidak di Kantor</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sedang tidak ada</p>
                @if($availability_status === 'out_of_office')
                    <div class="absolute top-3 right-3">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                    </div>
                @endif
            </button>
        </div>

        {{-- Info --}}
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
            <div class="flex items-start gap-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" />
                <div class="text-sm text-blue-700 dark:text-blue-300">
                    <p class="font-medium">Tentang Status Ketersediaan</p>
                    <p class="mt-1 text-blue-600 dark:text-blue-400">Status ini akan terlihat oleh sesama pegawai di halaman <strong>Direktori Pegawai</strong>. Petugas piket juga dapat melihat status ini untuk membantu mengarahkan tamu yang ingin menemui Anda.</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
