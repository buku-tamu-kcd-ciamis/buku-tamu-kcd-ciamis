<x-filament-panels::page>
    @php
        /** @var \Spatie\Activitylog\Models\Activity $record */
        $record = $this->getRecord();

        $eventLabel = match ($record->event) {
            'created' => 'Dibuat',
            'updated' => 'Diubah',
            'deleted' => 'Dihapus',
            default => $record->event ? ucfirst((string) $record->event) : '-',
        };

        $moduleLabel = \App\Filament\Resources\ActivityLogResource::getLogNameLabel((string) $record->log_name);

        $eventToneClass = match ($record->event) {
            'created' => 'al-pill-soft',
            'updated' => 'al-pill-warn',
            'deleted' => 'al-pill-danger',
            default => 'al-pill-neutral',
        };

        $properties = $record->properties;
        if ($properties instanceof \Illuminate\Support\Collection) {
            $properties = $properties->toArray();
        }

        $changedFields = count(array_keys($properties['attributes'] ?? []));
        $hasDiff = !empty($properties['attributes'] ?? []) || !empty($properties['old'] ?? []);
        $hasExtra = !empty($properties) && !$hasDiff;
    @endphp

    <style>
        .al-page {
            display: grid;
            gap: 1rem;
        }

        .al-hero {
            border-radius: 1rem;
            border: 1px solid #d4d4d8;
            background:
                radial-gradient(circle at top right, rgba(24, 24, 27, 0.08), transparent 45%),
                linear-gradient(135deg, #fafafa 0%, #f4f4f5 100%);
            padding: 1rem 1.05rem;
            display: grid;
            gap: 0.7rem;
        }

        .al-title {
            margin: 0;
            font-size: 1.14rem;
            line-height: 1.2;
            font-weight: 800;
            color: #18181b;
        }

        .al-subtitle {
            margin: 0;
            font-size: 0.88rem;
            color: #52525b;
        }

        .al-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .al-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            color: #27272a;
            padding: 0.27rem 0.68rem;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .al-pill-neutral {
            border-color: #d4d4d8;
            background: #f4f4f5;
            color: #27272a;
        }

        .al-pill-soft {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #334155;
        }

        .al-pill-warn {
            border-color: #fde68a;
            background: #fffbeb;
            color: #854d0e;
        }

        .al-pill-danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .al-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.55rem;
        }

        .al-stat {
            border-radius: 0.8rem;
            border: 1px solid #e4e4e7;
            background: #ffffff;
            padding: 0.6rem 0.7rem;
            display: grid;
            gap: 0.1rem;
        }

        .al-stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #71717a;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .al-stat-value {
            font-size: 0.9rem;
            font-weight: 800;
            color: #18181b;
            line-height: 1.25;
            word-break: break-word;
        }

        .al-card {
            border-radius: 0.95rem;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .al-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.8rem;
            border-bottom: 1px solid #e4e4e7;
            padding: 0.85rem 1rem;
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .al-card-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 800;
            color: #18181b;
        }

        .al-card-note {
            margin: 0;
            font-size: 0.78rem;
            color: #71717a;
        }

        .al-card-body {
            padding: 0.95rem;
        }

        .al-card-body :where(.fi-section) {
            border-radius: 0.85rem;
            border-color: #e4e4e7;
        }

        .al-card-body :where(.fi-section-header) {
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .dark .al-hero {
            border-color: #3f3f46;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.06), transparent 45%),
                linear-gradient(135deg, #0f0f10 0%, #18181b 100%);
        }

        .dark .al-title {
            color: #f4f4f5;
        }

        .dark .al-subtitle {
            color: #a1a1aa;
        }

        .dark .al-pill {
            border-color: #52525b;
            background: #18181b;
            color: #e4e4e7;
        }

        .dark .al-pill-neutral {
            border-color: #52525b;
            background: #27272a;
            color: #f4f4f5;
        }

        .dark .al-pill-soft {
            border-color: #475569;
            background: #1e293b;
            color: #cbd5e1;
        }

        .dark .al-pill-warn {
            border-color: #78350f;
            background: #451a03;
            color: #fde68a;
        }

        .dark .al-pill-danger {
            border-color: #7f1d1d;
            background: #450a0a;
            color: #fecaca;
        }

        .dark .al-stat {
            border-color: #3f3f46;
            background: #111113;
        }

        .dark .al-stat-label {
            color: #a1a1aa;
        }

        .dark .al-stat-value {
            color: #f4f4f5;
        }

        .dark .al-card {
            border-color: #3f3f46;
            background: #111113;
            box-shadow: none;
        }

        .dark .al-card-head {
            border-bottom-color: #3f3f46;
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .al-card-title {
            color: #f4f4f5;
        }

        .dark .al-card-note {
            color: #a1a1aa;
        }

        .dark .al-card-body :where(.fi-section) {
            border-color: #3f3f46;
        }

        .dark .al-card-body :where(.fi-section-header) {
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        @media (max-width: 1024px) {
            .al-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .al-hero {
                padding: 0.9rem;
            }

            .al-stats {
                grid-template-columns: 1fr;
            }

            .al-card-head {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <div class="al-page">
        <section class="al-hero">
            <h2 class="al-title">Detail Log Aktivitas</h2>
            <p class="al-subtitle">Audit trail aktivitas sistem untuk memudahkan pelacakan perubahan data dan tindakan pengguna.</p>

            <div class="al-pills">
                <span class="al-pill">Modul: {{ $moduleLabel }}</span>
                <span class="al-pill {{ $eventToneClass }}">Aksi: {{ $eventLabel }}</span>
                <span class="al-pill">User: {{ $record->causer?->name ?? 'System' }}</span>
                <span class="al-pill">Waktu: {{ $record->created_at?->format('d/m/Y H:i:s') ?? '-' }}</span>
            </div>

            <div class="al-stats">
                <article class="al-stat">
                    <span class="al-stat-label">Deskripsi</span>
                    <span class="al-stat-value">{{ $record->description ?: '-' }}</span>
                </article>
                <article class="al-stat">
                    <span class="al-stat-label">Model</span>
                    <span class="al-stat-value">{{ $record->subject_type ? class_basename($record->subject_type) : '-' }}</span>
                </article>
                <article class="al-stat">
                    <span class="al-stat-label">Jumlah Field Diubah</span>
                    <span class="al-stat-value">{{ $changedFields }}</span>
                </article>
                <article class="al-stat">
                    <span class="al-stat-label">Jenis Detail</span>
                    <span class="al-stat-value">
                        @if($hasDiff)
                            Perubahan Data
                        @elseif($hasExtra)
                            Detail Tambahan
                        @else
                            Ringkas
                        @endif
                    </span>
                </article>
            </div>
        </section>

        <section class="al-card">
            <header class="al-card-head">
                <h3 class="al-card-title">Rincian Aktivitas</h3>
                <p class="al-card-note">Informasi berikut bersumber dari data log yang tersimpan pada saat kejadian.</p>
            </header>

            <div class="al-card-body">
                {{ $this->infolist }}
            </div>
        </section>
    </div>
</x-filament-panels::page>
