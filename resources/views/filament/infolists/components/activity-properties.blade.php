@php
    $properties = $getState();
    if ($properties instanceof \Illuminate\Support\Collection) {
        $properties = $properties->toArray();
    }

    $attributes = $properties['attributes'] ?? [];
    $old = $properties['old'] ?? [];
@endphp

@if($attributes || $old)
    <style>
        .al-diff-grid {
            display: grid;
            gap: 0.75rem;
        }

        .al-diff-item {
            border: 1px solid #e4e4e7;
            border-radius: 0.85rem;
            background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%);
            padding: 0.8rem;
            display: grid;
            gap: 0.55rem;
        }

        .al-diff-key {
            margin: 0;
            font-size: 0.82rem;
            font-weight: 700;
            color: #27272a;
            text-transform: capitalize;
        }

        .al-diff-values {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.6rem;
        }

        .al-diff-box {
            border: 1px solid #d4d4d8;
            border-radius: 0.7rem;
            background: #ffffff;
            padding: 0.55rem 0.65rem;
            display: grid;
            gap: 0.3rem;
        }

        .al-diff-label {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            border-radius: 999px;
            border: 1px solid #d4d4d8;
            padding: 0.14rem 0.46rem;
            font-size: 0.68rem;
            font-weight: 700;
            color: #52525b;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .al-diff-text {
            font-size: 0.82rem;
            color: #27272a;
            word-break: break-word;
            line-height: 1.4;
        }

        .al-diff-empty {
            border: 1px dashed #d4d4d8;
            border-radius: 0.85rem;
            background: #fafafa;
            padding: 0.8rem;
            font-size: 0.82rem;
            color: #71717a;
        }

        .dark .al-diff-item {
            border-color: #3f3f46;
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .al-diff-key {
            color: #f4f4f5;
        }

        .dark .al-diff-box {
            border-color: #52525b;
            background: #18181b;
        }

        .dark .al-diff-label {
            border-color: #52525b;
            color: #a1a1aa;
        }

        .dark .al-diff-text {
            color: #e4e4e7;
        }

        .dark .al-diff-empty {
            border-color: #52525b;
            background: #18181b;
            color: #a1a1aa;
        }

        @media (max-width: 768px) {
            .al-diff-values {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="al-diff-grid">
        @foreach($attributes as $key => $newValue)
            @php
                $oldValue = $old[$key] ?? null;
                $hasChanged = $oldValue !== $newValue;
            @endphp
            
            @if($hasChanged)
                <article class="al-diff-item">
                    <p class="al-diff-key">{{ str_replace('_', ' ', $key) }}</p>

                    <div class="al-diff-values">
                        <div class="al-diff-box">
                            <span class="al-diff-label">Sebelum</span>
                            <div class="al-diff-text">{{ $oldValue ?: '(kosong)' }}</div>
                        </div>

                        <div class="al-diff-box">
                            <span class="al-diff-label">Sesudah</span>
                            <div class="al-diff-text">{{ $newValue ?: '(kosong)' }}</div>
                        </div>
                    </div>
                </article>
            @endif
        @endforeach
    </div>
@else
    <p class="al-diff-empty">Tidak ada perubahan data yang tercatat.</p>
@endif
