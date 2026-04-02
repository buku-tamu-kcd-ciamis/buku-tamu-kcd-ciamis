@php
    $properties = $getState();
    if ($properties instanceof \Illuminate\Support\Collection) {
        $properties = $properties->toArray();
    }
@endphp

@if(!empty($properties))
    <style>
        .al-detail-grid {
            display: grid;
            gap: 0.75rem;
        }

        .al-detail-item {
            border: 1px solid #e4e4e7;
            border-radius: 0.85rem;
            background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%);
            padding: 0.8rem;
        }

        .al-detail-wrap {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
        }

        .al-detail-icon {
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .al-detail-icon :where(svg) {
            width: 1.1rem;
            height: 1.1rem;
            color: #71717a;
        }

        .al-detail-content {
            flex: 1;
            min-width: 0;
            display: grid;
            gap: 0.28rem;
        }

        .al-detail-key {
            font-size: 0.81rem;
            font-weight: 700;
            color: #27272a;
            line-height: 1.3;
        }

        .al-detail-value {
            font-size: 0.83rem;
            color: #52525b;
            line-height: 1.4;
            word-break: break-word;
        }

        .al-detail-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            color: #27272a;
            padding: 0.15rem 0.48rem;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .al-detail-empty {
            border: 1px dashed #d4d4d8;
            border-radius: 0.85rem;
            background: #fafafa;
            padding: 0.8rem;
            font-size: 0.82rem;
            color: #71717a;
            font-style: italic;
        }

        .dark .al-detail-item {
            border-color: #3f3f46;
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .al-detail-icon :where(svg) {
            color: #a1a1aa;
        }

        .dark .al-detail-key {
            color: #f4f4f5;
        }

        .dark .al-detail-value {
            color: #d4d4d8;
        }

        .dark .al-detail-chip {
            border-color: #52525b;
            background: #18181b;
            color: #e4e4e7;
        }

        .dark .al-detail-empty {
            border-color: #52525b;
            background: #18181b;
            color: #a1a1aa;
        }
    </style>

    <div class="al-detail-grid">
        @foreach($properties as $key => $value)
            <article class="al-detail-item">
                <div class="al-detail-wrap">
                    <div class="al-detail-icon">
                        @switch($key)
                            @case('ip_address')
                                <x-heroicon-o-globe-alt />
                                @break
                            @case('user_agent')
                                <x-heroicon-o-device-phone-mobile />
                                @break
                            @case('email')
                                <x-heroicon-o-envelope />
                                @break
                            @case('role')
                                <x-heroicon-o-shield-check />
                                @break
                            @case('user_name')
                            @case('nama_tamu')
                            @case('nama')
                                <x-heroicon-o-user />
                                @break
                            @case('nip')
                                <x-heroicon-o-identification />
                                @break
                            @case('jumlah')
                                <x-heroicon-o-calculator />
                                @break
                            @case('tipe')
                            @case('jenis_izin')
                            @case('kategori')
                                <x-heroicon-o-tag />
                                @break
                            @case('filter')
                                <x-heroicon-o-funnel />
                                @break
                            @default
                                <x-heroicon-o-information-circle />
                        @endswitch
                    </div>

                    <div class="al-detail-content">
                        <div class="al-detail-key">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                        </div>

                        <div class="al-detail-value">
                            @if(is_array($value))
                                @if(empty($value))
                                    <span class="al-detail-empty">Tidak ada filter</span>
                                @else
                                    <div class="space-y-1">
                                        @foreach($value as $subKey => $subValue)
                                            @if($subValue)
                                                <div class="flex items-center gap-2">
                                                    <span class="al-detail-chip">
                                                        {{ ucfirst(str_replace('_', ' ', $subKey)) }}
                                                    </span>
                                                    <span>{{ is_array($subValue) ? json_encode($subValue) : $subValue }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @elseif(is_bool($value))
                                <span class="al-detail-chip">
                                    {{ $value ? 'Ya' : 'Tidak' }}
                                </span>
                            @elseif(is_null($value) || $value === '')
                                <span class="al-detail-empty">(kosong)</span>
                            @else
                                {{ $value }}
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@else
    <p class="al-detail-empty">Tidak ada detail tambahan.</p>
@endif
