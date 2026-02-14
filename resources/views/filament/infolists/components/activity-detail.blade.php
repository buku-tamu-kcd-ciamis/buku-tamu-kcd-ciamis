@php
    $properties = $getState();
    if ($properties instanceof \Illuminate\Support\Collection) {
        $properties = $properties->toArray();
    }
@endphp

@if(!empty($properties))
    <div class="space-y-3">
        @foreach($properties as $key => $value)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        @switch($key)
                            @case('ip_address')
                                <x-heroicon-o-globe-alt class="w-5 h-5 text-gray-400" />
                                @break
                            @case('user_agent')
                                <x-heroicon-o-device-phone-mobile class="w-5 h-5 text-gray-400" />
                                @break
                            @case('email')
                                <x-heroicon-o-envelope class="w-5 h-5 text-gray-400" />
                                @break
                            @case('role')
                                <x-heroicon-o-shield-check class="w-5 h-5 text-gray-400" />
                                @break
                            @case('user_name')
                            @case('nama_tamu')
                            @case('nama')
                                <x-heroicon-o-user class="w-5 h-5 text-gray-400" />
                                @break
                            @case('nip')
                                <x-heroicon-o-identification class="w-5 h-5 text-gray-400" />
                                @break
                            @case('jumlah')
                                <x-heroicon-o-calculator class="w-5 h-5 text-gray-400" />
                                @break
                            @case('tipe')
                            @case('jenis_izin')
                            @case('kategori')
                                <x-heroicon-o-tag class="w-5 h-5 text-gray-400" />
                                @break
                            @case('filter')
                                <x-heroicon-o-funnel class="w-5 h-5 text-gray-400" />
                                @break
                            @default
                                <x-heroicon-o-information-circle class="w-5 h-5 text-gray-400" />
                        @endswitch
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            @if(is_array($value))
                                @if(empty($value))
                                    <span class="text-gray-400 italic">Tidak ada filter</span>
                                @else
                                    <div class="space-y-1">
                                        @foreach($value as $subKey => $subValue)
                                            @if($subValue)
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                        {{ ucfirst(str_replace('_', ' ', $subKey)) }}
                                                    </span>
                                                    <span>{{ is_array($subValue) ? json_encode($subValue) : $subValue }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @elseif(is_bool($value))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $value ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' }}">
                                    {{ $value ? 'Ya' : 'Tidak' }}
                                </span>
                            @elseif(is_null($value) || $value === '')
                                <span class="text-gray-400 italic">(kosong)</span>
                            @else
                                {{ $value }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400 italic">Tidak ada detail tambahan.</p>
@endif
