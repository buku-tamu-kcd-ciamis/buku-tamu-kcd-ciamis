<x-filament-panels::page>
    @php
        $tamu = $this->getTamu();
        $allKunjungan = $this->getAllKunjungan();
        $totalKunjungan = $allKunjungan->count();
        $profilePhotoUrl = $tamu?->foto_selfie_url;

        $phone = $tamu->nomor_hp;
        $formattedPhone = '-';
        if ($phone) {
            $cleaned = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($cleaned, '0')) {
                $cleaned = substr($cleaned, 1);
            }
            $formattedPhone = '+62 ' . $cleaned;
        }

        $statusCounts = $allKunjungan->groupBy('status')->map->count();

        $statusConfig = [
            'menunggu'   => ['badge' => 'vrt-badge-yellow', 'dot' => 'vrt-dot-yellow'],
            'diproses'   => ['badge' => 'vrt-badge-blue', 'dot' => 'vrt-dot-blue'],
            'selesai'    => ['badge' => 'vrt-badge-green', 'dot' => 'vrt-dot-green'],
            'ditolak'    => ['badge' => 'vrt-badge-red', 'dot' => 'vrt-dot-red'],
            'dibatalkan' => ['badge' => 'vrt-badge-red', 'dot' => 'vrt-dot-red'],
        ];
        $statusLabels = \App\Models\BukuTamu::STATUS_LABELS;
    @endphp

    <div class="vrt-page">

        {{-- ===== PROFIL PENGUNJUNG ===== --}}
        <div class="vrt-card">
            <div class="vrt-card-header">
                <div class="vrt-avatar">
                    @if($profilePhotoUrl)
                        <img src="{{ $profilePhotoUrl }}" alt="Foto {{ $tamu->nama_lengkap }}" class="vrt-avatar-image" loading="lazy">
                    @else
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @endif
                </div>
                <div class="vrt-header-info">
                    <p class="vrt-overline">Profil Pengunjung</p>
                    <h2 class="vrt-title">{{ $tamu->nama_lengkap }}</h2>
                </div>
            </div>

            <div class="vrt-profile-grid">
                <div class="vrt-info-item">
                    <p class="vrt-label">{{ $tamu->jenis_id ?? 'KTP' }}</p>
                    <p class="vrt-value">{{ $tamu->nik }}</p>
                </div>
                <div class="vrt-info-item">
                    <p class="vrt-label">No. HP</p>
                    <p class="vrt-value">{{ $formattedPhone }}</p>
                </div>
                <div class="vrt-info-item">
                    <p class="vrt-label">Jabatan</p>
                    <p class="vrt-value">{{ $tamu->jabatan ?? '-' }}</p>
                </div>
                <div class="vrt-info-item">
                    <p class="vrt-label">Instansi</p>
                    <p class="vrt-value">{{ $tamu->instansi ?? '-' }}</p>
                </div>
                <div class="vrt-info-item vrt-col-span-2">
                    <p class="vrt-label">Kabupaten/Kota</p>
                    <p class="vrt-value">{{ $tamu->kabupaten_kota ?? '-' }}</p>
                </div>
                <div class="vrt-info-item vrt-col-span-2">
                    <p class="vrt-label">Email</p>
                    <p class="vrt-value">{{ $tamu->email ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- ===== STATISTIK ===== --}}
        <div class="vrt-stat-grid">
            <div class="vrt-stat-card">
                <div>
                    <p class="vrt-stat-label">Total</p>
                    <p class="vrt-stat-value">{{ $totalKunjungan }}</p>
                </div>
                <div class="vrt-stat-icon vrt-stat-icon-gray">
                    <div class="vrt-dot"></div>
                </div>
            </div>
            <div class="vrt-stat-card">
                <div>
                    <p class="vrt-stat-label">Menunggu</p>
                    <p class="vrt-stat-value">{{ $statusCounts->get('menunggu', 0) }}</p>
                </div>
                <div class="vrt-stat-icon vrt-stat-icon-yellow">
                    <div class="vrt-dot"></div>
                </div>
            </div>
            <div class="vrt-stat-card">
                <div>
                    <p class="vrt-stat-label">Diproses</p>
                    <p class="vrt-stat-value">{{ $statusCounts->get('diproses', 0) }}</p>
                </div>
                <div class="vrt-stat-icon vrt-stat-icon-blue">
                    <div class="vrt-dot"></div>
                </div>
            </div>
            <div class="vrt-stat-card">
                <div>
                    <p class="vrt-stat-label">Selesai</p>
                    <p class="vrt-stat-value">{{ $statusCounts->get('selesai', 0) }}</p>
                </div>
                <div class="vrt-stat-icon vrt-stat-icon-green">
                    <div class="vrt-dot"></div>
                </div>
            </div>
            <div class="vrt-stat-card">
                <div>
                    <p class="vrt-stat-label">Ditolak</p>
                    <p class="vrt-stat-value">{{ $statusCounts->get('ditolak', 0) + $statusCounts->get('dibatalkan', 0) }}</p>
                </div>
                <div class="vrt-stat-icon vrt-stat-icon-red">
                    <div class="vrt-dot"></div>
                </div>
            </div>
        </div>

        {{-- ===== RIWAYAT KUNJUNGAN ===== --}}
        @php
            $paginatedKunjungan = $this->getKunjunganPaginated();
        @endphp
        <div class="vrt-card">
            <div class="vrt-history-header">
                <h3>Riwayat Kunjungan</h3>
                <span>{{ $totalKunjungan }} Catatan</span>
            </div>

            <div x-data="{ openItem: null }" class="vrt-timeline">
                @forelse($paginatedKunjungan as $index => $item)
                @php
                    $globalIndex = ($paginatedKunjungan->currentPage() - 1) * $paginatedKunjungan->perPage() + $index;
                    $sts = $statusConfig[$item->status] ?? $statusConfig['dibatalkan'];
                @endphp
                <div class="vrt-timeline-item" :class="openItem === {{ $index }} ? 'vrt-expanded' : ''">
                    <div class="vrt-timeline-indicator">
                        <div class="vrt-timeline-dot {{ $sts['dot'] }}"></div>
                        @if(!$loop->last)
                        <div class="vrt-timeline-line"></div>
                        @endif
                    </div>

                    <div class="vrt-timeline-content">
                        <div class="vrt-timeline-header">
                            <div class="vrt-timeline-title">
                                <h4>{{ $item->keperluan }}</h4>
                                <p>
                                    {{ $item->created_at->translatedFormat('d F Y, H:i') }}
                                    <span>&bull;</span>
                                    {{ $item->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="vrt-timeline-actions">
                                <span class="vrt-badge {{ $sts['badge'] }}">
                                    {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                                </span>
                                <button @click="openItem = (openItem === {{ $index }} ? null : {{ $index }})" class="vrt-expand-btn" :class="openItem === {{ $index }} ? 'active' : ''">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="openItem === {{ $index }}" x-collapse>
                            <div class="vrt-timeline-details">
                                <div class="vrt-detail-card">
                                    <div class="vrt-detail-grid">
                                        <div class="vrt-info-item">
                                            <p class="vrt-label">Staff Yang Dituju</p>
                                            <p class="vrt-value">{{ $item->staff_dituju ?? '-' }}</p>
                                        </div>
                                        @if($item->nama_penerima)
                                        <div class="vrt-info-item">
                                            <p class="vrt-label">Penerima</p>
                                            <p class="vrt-value">{{ $item->nama_penerima }}</p>
                                        </div>
                                        @endif
                                        @if($item->kabupaten_kota)
                                        <div class="vrt-info-item">
                                            <p class="vrt-label">Kabupaten/Kota</p>
                                            <p class="vrt-value">{{ $item->kabupaten_kota }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    @if($item->keperluan)
                                    <div class="vrt-detail-note">
                                        <p class="vrt-label">Keperluan</p>
                                        <p class="vrt-value">{{ $item->keperluan }}</p>
                                    </div>
                                    @endif
                                    @if($item->catatan)
                                    <div class="vrt-detail-note">
                                        <p class="vrt-label">Catatan</p>
                                        <p class="vrt-value">{{ $item->catatan }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="vrt-empty-state">
                    Belum ada riwayat kunjungan.
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($paginatedKunjungan->hasPages() || $paginatedKunjungan->total() > 0)
            <div class="vrt-pagination-container">
                <div class="vrt-pagination-wrapper">
                    <div class="vrt-pagination-info">
                        @if($paginatedKunjungan->total() > 0)
                        <p>
                            Menampilkan <span>{{ $paginatedKunjungan->firstItem() }}</span>
                            - <span>{{ $paginatedKunjungan->lastItem() }}</span>
                            dari <span>{{ $paginatedKunjungan->total() }}</span>
                        </p>
                        @endif
                        <div class="vrt-per-page">
                            <span>Per halaman:</span>
                            @foreach([3, 5, 10] as $size)
                                <a href="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" class="{{ $this->kunjunganPerPage === $size ? 'active' : '' }}">{{ $size }}</a>
                            @endforeach
                        </div>
                    </div>
                    @if($paginatedKunjungan->hasPages())
                    <div class="vrt-pagination-controls">
                        @if($paginatedKunjungan->onFirstPage())
                        <span class="vrt-page-btn disabled">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                        @else
                        <button wire:click="previousPage" class="vrt-page-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        @endif

                        @foreach($paginatedKunjungan->getUrlRange(1, $paginatedKunjungan->lastPage()) as $pageNum => $url)
                        <button wire:click="gotoPage({{ $pageNum }})" class="vrt-page-btn {{ $pageNum == $paginatedKunjungan->currentPage() ? 'active' : '' }}">
                            {{ $pageNum }}
                        </button>
                        @endforeach

                        @if($paginatedKunjungan->hasMorePages())
                        <button wire:click="nextPage" class="vrt-page-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        @else
                        <span class="vrt-page-btn disabled">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>


