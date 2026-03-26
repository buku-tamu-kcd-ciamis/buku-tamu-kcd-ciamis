<x-filament-panels::page>
    @php
        $documents = collect($this->getDocuments());
        $totalDocuments = $documents->count();
        $totalSizeBytes = (int) $documents->sum('size_bytes');
        $totalTypes = $documents->pluck('extension')->unique()->count();
        $latestUpdate = $documents->max('updated_at_unix');
        $types = $documents->pluck('extension_key')->unique()->sort()->values();

        $formatBytes = function (int $bytes): string {
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 1) . ' GB';
            }
            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 1) . ' MB';
            }
            if ($bytes >= 1024) {
                return number_format($bytes / 1024, 1) . ' KB';
            }

            return $bytes . ' B';
        };
    @endphp

    <div
        class="staff-download-page"
        x-data="{
            query: '',
            type: 'all',
            matchesDoc(text, extension) {
                const q = this.query.trim().toLowerCase();
                const typeMatch = this.type === 'all' || this.type === extension;

                if (!typeMatch) {
                    return false;
                }

                return q === '' || text.includes(q);
            },
            visibleCount() {
                if (!this.$refs.list) {
                    return 0;
                }

                return Array.from(this.$refs.list.children)
                    .filter((item) => getComputedStyle(item).display !== 'none').length;
            },
        }"
    >
        <section class="staff-download-hero">
            <div class="staff-download-hero-main">
                <p class="staff-download-kicker">Arsip Internal</p>
                <h2 class="staff-download-hero-title">Pusat Download Dokumen Staff</h2>
                <p class="staff-download-hero-text">
                    Temukan template, panduan, dan dokumen operasional terbaru dalam satu halaman.
                </p>
                <p class="staff-download-hero-updated">
                    Pembaruan terakhir:
                    <strong>{{ $latestUpdate ? date('d M Y H:i', $latestUpdate) : 'Belum ada dokumen' }}</strong>
                </p>
            </div>

            <div class="staff-download-hero-stats">
                <div class="staff-download-stat-card">
                    <span class="staff-download-stat-label">Total Dokumen</span>
                    <span class="staff-download-stat-value">{{ $totalDocuments }}</span>
                </div>
                <div class="staff-download-stat-card">
                    <span class="staff-download-stat-label">Total Ukuran</span>
                    <span class="staff-download-stat-value">{{ $formatBytes($totalSizeBytes) }}</span>
                </div>
                <div class="staff-download-stat-card">
                    <span class="staff-download-stat-label">Jenis File</span>
                    <span class="staff-download-stat-value">{{ $totalTypes }}</span>
                </div>
            </div>
        </section>

        @if($totalDocuments > 0)
            <section class="staff-download-toolbar" aria-label="Filter dokumen">
                <label class="staff-download-search">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                    <input
                        type="text"
                        x-model="query"
                        placeholder="Cari nama dokumen..."
                    >
                </label>

                <label class="staff-download-filter">
                    <span>Tipe</span>
                    <select x-model="type">
                        <option value="all">Semua Tipe</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}">{{ strtoupper($type) }}</option>
                        @endforeach
                    </select>
                </label>
            </section>

            <section class="staff-download-grid" x-ref="list">
                @foreach($documents as $doc)
                    <article
                        class="staff-download-card is-{{ $doc['tone'] }}"
                        x-show="matchesDoc(@js(strtolower($doc['display_name'] . ' ' . $doc['name'])), @js($doc['extension_key']))"
                        x-transition.opacity.duration.180ms
                    >
                        <div class="staff-download-card-head">
                            <div class="staff-download-icon-wrap">
                                <x-dynamic-component :component="$doc['icon']" class="h-6 w-6" />
                            </div>

                            <span class="staff-download-ext">{{ $doc['extension'] }}</span>
                        </div>

                        <div class="staff-download-meta">
                            <h3 class="staff-download-name" title="{{ $doc['display_name'] }}">{{ $doc['display_name'] }}</h3>
                            <p class="staff-download-info">{{ $doc['size'] }} • Diperbarui {{ $doc['updated_at'] }}</p>
                        </div>

                        <a href="{{ $doc['url'] }}" target="_blank" rel="noopener" class="staff-download-action">
                            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                            Unduh Dokumen
                        </a>
                    </article>
                @endforeach
            </section>

            <div class="staff-download-empty-filter" x-cloak x-show="visibleCount() === 0">
                <x-heroicon-o-funnel class="h-6 w-6" />
                <p>Tidak ada dokumen yang cocok dengan pencarian/filter saat ini.</p>
            </div>
        @else
            <section class="staff-download-empty">
                <div class="staff-download-empty-icon">
                    <x-heroicon-o-folder-open class="h-10 w-10" />
                </div>
                <h3>Belum Ada Dokumen</h3>
                <p>
                    Dokumen internal belum tersedia. Admin dapat menambahkan file melalui folder
                    <span>storage/app/public/dokumen/</span>.
                </p>
            </section>
        @endif
    </div>
</x-filament-panels::page>