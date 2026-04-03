{{-- Halaman Public / Index — Buku Tamu Cadisdik XIII --}}
@extends('layouts.main')

@section('title', 'Buku Tamu — Cadisdik XIII')
@section('seo_title', 'Buku Tamu Digital KCD Ciamis | Registrasi Tamu Online')
@section('seo_description', 'Platform Buku Tamu Digital KCD Ciamis untuk registrasi tamu online, pengelolaan kunjungan, dan layanan administrasi pendidikan secara cepat dan tertib.')
@section('seo_keywords', 'buku tamu digital ciamis, etamu kcd, registrasi tamu online, buku tamu cabang dinas pendidikan, aplikasi buku tamu sekolah, buku tamu smkn 1 ciamis')
@section('seo_canonical', url('/'))
@section('seo_image', '/img/og-etamu-kcd.png')
@section('seo_image_alt', 'Buku Tamu Digital KCD Ciamis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public/buku-tamu.css') }}">
@endpush

@section('content')

    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo Cadisdik">
            <div class="header-text">
                <h1>Selamat Datang di Cabang Dinas Pendidikan Wilayah XIII</h1>
                <p>Silahkan untuk mengisi buku tamu terlebih dahulu</p>
            </div>
        </div>

        @if(session('success'))
            <script>window.__flashSuccess = @json(session('success'));</script>
        @endif

        @if(session('error'))
            <script>window.__flashError = @json(session('error'));</script>
        @endif

        <!-- Form -->
        <div class="form-container">
            <form id="bukuTamuForm" method="POST" action="{{ route('buku-tamu.store') }}">
                @csrf
                <div class="form-main">

                    <!-- LEFT: Input Fields -->
                    <div class="form-left">
                        <div class="form-left-grid">

                            <!-- Jenis ID -->
                            <div class="form-group">
                                <label>Jenis ID <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <div class="autocomplete-wrapper">
                                        <input type="text" id="jenis_id_input" placeholder="Ketik atau pilih jenis ID..."
                                            autocomplete="off" required>
                                        <input type="hidden" name="jenis_id" id="jenis_id" required>
                                        <div class="autocomplete-list" id="jenis_id_list"></div>
                                    </div>
                                    <i class="fa-solid fa-id-card input-icon"></i>
                                </div>
                            </div>

                            <!-- NIK / Nomor ID -->
                            <div class="form-group">
                                <label id="nik_label">Nomor ID <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <input type="text" name="nik" id="nik" placeholder="Pilih jenis ID terlebih dahulu"
                                        required>
                                    <i class="fa-solid fa-address-card input-icon" id="nik_icon"></i>
                                </div>
                                <div class="phone-hint" id="nik_hint"></div>
                            </div>

                            <!-- Nama Lengkap -->
                            <div class="form-group">
                                <label>Nama Lengkap <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="" required>
                                    <i class="fa-solid fa-user input-icon"></i>
                                </div>
                            </div>

                            <!-- Instansi -->
                            <div class="form-group">
                                <label>Instansi</label>
                                <div class="input-wrapper">
                                    <input type="text" name="instansi" id="instansi" placeholder="">
                                    <i class="fa-solid fa-building-user input-icon"></i>
                                </div>
                            </div>

                            <!-- Nomor Handphone -->
                            <div class="form-group">
                                <label>Nomor Handphone <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <div class="phone-wrapper">
                                        <span class="phone-prefix">+62</span>
                                        <input type="tel" name="nomor_hp" id="nomor_hp" placeholder="8xx-xxxx-xxxx" required
                                            maxlength="15">
                                    </div>
                                    <i class="fa-solid fa-phone input-icon"></i>
                                </div>
                                <div class="phone-hint" id="phone_hint">Min. 9 digit, Maks. 13 digit (setelah +62)</div>
                            </div>

                            <!-- Jabatan -->
                            <div class="form-group">
                                <label>Jabatan</label>
                                <div class="input-wrapper">
                                    <input type="text" name="jabatan" id="jabatan" placeholder="">
                                    <i class="fa-solid fa-user-tie input-icon"></i>
                                </div>
                            </div>

                            <!-- Kabupaten/Kota Instansi -->
                            <div class="form-group">
                                <label>Kabupaten/Kota Instansi <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <div class="autocomplete-wrapper">
                                        <input type="text" name="kabupaten_kota" id="kabupaten_kota"
                                            placeholder="Ketik nama kabupaten/kota..." autocomplete="off" required>
                                        <div class="autocomplete-list" id="kabkota_list"></div>
                                    </div>
                                    <i class="fa-solid fa-city input-icon"></i>
                                </div>
                            </div>

                            <!-- Staff Yang Dituju -->
                            <div class="form-group">
                                <label>Staff Yang Dituju <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <div class="autocomplete-wrapper">
                                        <input type="text" id="staff_dituju_input" placeholder="Ketik nama staff..."
                                            autocomplete="off" required>
                                        <input type="hidden" name="staff_dituju" id="staff_dituju" required>
                                        <div class="autocomplete-list" id="staff_dituju_list"></div>
                                    </div>
                                    <i class="fa-solid fa-user-tie input-icon"></i>
                                </div>
                                <div class="phone-hint">Wajib dipilih — tentukan staff yang ingin ditemui</div>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label>Email</label>
                                <div class="input-wrapper">
                                    <input type="email" name="email" id="email" placeholder="">
                                    <i class="fa-solid fa-envelope input-icon"></i>
                                </div>
                            </div>

                            <!-- Keperluan -->
                            <div class="form-group">
                                <label>Keperluan <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <div class="autocomplete-wrapper">
                                        <input type="text" name="keperluan" id="keperluan"
                                            placeholder="Ketik atau pilih keperluan..." autocomplete="off" required>
                                        <div class="autocomplete-list" id="keperluan_list"></div>
                                    </div>
                                    <i class="fa-solid fa-clipboard-list input-icon"></i>
                                </div>
                            </div>

                        </div>

                        <!-- Catatan -->
                        <div class="catatan">
                            <p><strong>CATATAN :</strong></p>
                            <p>Semua kolom yang bertanda <span class="required">*</span> wajib diisi.</p>
                        </div>
                    </div>

                    <!-- RIGHT: Foto Selfie + Foto Penerimaan + Tanda Tangan -->
                    <div class="form-right">
                        <!-- Foto Selfie -->
                        <div class="form-group foto-section">
                            <label>Foto Selfie <span class="required">*</span></label>
                            <div class="foto-box" id="fotoSelfieBox">
                                <div class="camera-icon">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                                <p>Tekan tombol dibawah untuk<br>mengambil foto selfie.</p>
                            </div>
                            <div class="camera-controls">
                                <button type="button" class="btn-camera" id="btnCameraSelfie">Mulai Kamera</button>
                                <button type="button" class="btn-flip-camera" id="btnFlipSelfie" title="Ganti Kamera"
                                    style="display:none;"><i class="fa-solid fa-camera-rotate"></i></button>
                            </div>
                            <input type="hidden" name="foto_selfie" id="fotoSelfieInput">
                        </div>

                        <!-- Foto Penerimaan Berkas -->
                        <div class="form-group foto-section foto-penerimaan-section">
                            <label>Foto Penerimaan Berkas</label>
                            <div class="foto-box" id="fotoPenerimaanBox">
                                <div class="camera-icon">
                                    <i class="fa-solid fa-handshake"></i>
                                </div>
                                <p>Foto bersama resepsionis<br>saat penerimaan berkas.</p>
                            </div>
                            <div class="camera-controls">
                                <button type="button" class="btn-camera" id="btnCameraPenerimaan">Mulai Kamera</button>
                                <button type="button" class="btn-close-camera" id="btnClosePenerimaan" title="Tutup Kamera"
                                    style="display:none;"><i class="fa-solid fa-xmark"></i></button>
                                <button type="button" class="btn-flip-camera" id="btnFlipPenerimaan" title="Ganti Kamera"
                                    style="display:none;"><i class="fa-solid fa-camera-rotate"></i></button>
                            </div>
                            <input type="hidden" name="foto_penerimaan" id="fotoPenerimaanInput">
                        </div>

                        <!-- Tanda Tangan -->
                        <div class="form-group ttd-section">
                            <label>Tanda Tangan <span class="required">*</span></label>
                            <div class="ttd-box" id="ttdBox">
                                <div class="pen-icon">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                                <p>Tekan tombol dibawah untuk<br>menandatangani.</p>
                            </div>
                            <button type="button" class="btn-ttd" id="btnTtd">Gambar Tanda Tangan</button>
                            <input type="hidden" name="tanda_tangan" id="ttdInput">
                        </div>
                    </div>

                </div>

                <!-- Tombol Simpan -->
                <button type="submit" class="btn-simpan">Kirim</button>
            </form>
        </div>

        <footer class="public-footer" role="contentinfo" aria-label="Footer halaman buku tamu">
            <a href="{{ route('developer.about') }}" class="public-footer__link" title="Tentang Developer">
                Cadisdik Wilayah XIII • © <span id="realtimeYear">{{ now()->year }}</span>
            </a>
        </footer>

    </div>{{-- /.wrapper --}}

    <div class="apk-reminder" id="apkReminder" role="status" aria-live="polite">
        <button type="button" class="apk-reminder__close" id="apkReminderClose" aria-label="Tutup pengingat download APK">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="apk-reminder__content">
            <img class="apk-reminder__logo" src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo KCD">
            <p class="apk-reminder__title">Pakai Android?</p>
            <p class="apk-reminder__desc">Gunakan aplikasi Buku Tamu KCD agar akses lebih cepat.</p>
        </div>
        @if($apkDownloadAvailable ?? false)
            <a class="apk-reminder__action" href="{{ route('apk.download') }}">
                <i class="fa-solid fa-download"></i>
                Download APK
            </a>
        @else
            <span class="apk-reminder__action is-disabled">
                <i class="fa-solid fa-circle-info"></i>
                APK belum tersedia
            </span>
        @endif
    </div>

    <!-- Floating Barcode Button -->
    <button type="button" class="floating-btn" id="btnBarcode" title="Survey Kepuasan Masyarakat">
        <i class="fa-solid fa-qrcode"></i>
    </button>

    <!-- Barcode Survey Modal -->
    <div class="barcode-modal-overlay" id="barcodeModal">
        <div class="barcode-modal">
            <button type="button" class="close-btn" id="btnCloseBarcode">&times;</button>
            <h3><i class="fa-solid"></i> Survey Kepuasan Masyarakat</h3>
            <p id="surveyGateNote">Scan barcode di bawah ini untuk mengisi survey</p>
            @php
                $pengaturan = \App\Models\PengaturanKcd::getSettings();
                $barcodeUrl = $pengaturan->barcode_skm ? '/storage/' . ltrim($pengaturan->barcode_skm, '/') : asset('img/barcode-skm.png');
            @endphp
            <img src="{{ $barcodeUrl }}" alt="Barcode Survey Kepuasan Masyarakat">
            <div class="survey-submit-gate" id="surveySubmitGate">
                <p class="survey-timer-text" id="surveyTimerText"></p>
                <button type="button" class="btn-lanjut-submit" id="btnLanjutSubmit" disabled>Lanjutkan Kirim</button>
            </div>
        </div>
    </div>

    <!-- Modal Tanda Tangan -->
    <div class="modal-overlay" id="ttdModal">
        <div class="modal-content">
            <h3>Tanda Tangan</h3>
            <canvas id="signatureCanvas"></canvas>
            <div class="modal-buttons">
                <button type="button" class="btn-clear" id="btnClearTtd">Hapus</button>
                <button type="button" class="btn-save-ttd" id="btnSaveTtd">Simpan</button>
                <button type="button" class="btn-cancel" id="btnCancelTtd">Batal</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Dynamic dropdown data from database
        window.__dropdownData = {
            jenisId: @json($jenisIdOptions ?? []),
            keperluan: @json($keperluanOptions ?? []),
            kabupatenKota: @json($kabupatenKotaOptions ?? []),
            bagianDituju: @json($bagianDitujuOptions ?? []),
            staffList: @json($staffList ?? []),
        };

        // Keep footer year always in sync with current year.
        (function syncRealtimeYear() {
            const yearNode = document.getElementById('realtimeYear');
            if (!yearNode) {
                return;
            }

            const updateYear = () => {
                yearNode.textContent = String(new Date().getFullYear());
            };

            updateYear();
            setInterval(updateYear, 60 * 1000);
        })();

        (function setupApkReminder() {
            const reminder = document.getElementById('apkReminder');
            const closeButton = document.getElementById('apkReminderClose');

            if (!reminder || !closeButton) {
                return;
            }

            const userAgent = navigator.userAgent || '';
            const isAndroidWebView = /Android/i.test(userAgent) && /;\s*wv\)|\bwv\b/i.test(userAgent);
            const isCapacitorNative = Boolean(
                window.Capacitor &&
                (
                    (typeof window.Capacitor.isNativePlatform === 'function' && window.Capacitor.isNativePlatform()) ||
                    (typeof window.Capacitor.getPlatform === 'function' && window.Capacitor.getPlatform() !== 'web')
                )
            );
            const isTrustedWebActivity = document.referrer.startsWith('android-app://');
            const isApkRuntime = isCapacitorNative || isAndroidWebView || isTrustedWebActivity;

            if (isApkRuntime) {
                reminder.classList.add('is-hidden');

                return;
            }

            window.setTimeout(() => {
                reminder.classList.add('is-visible');
            }, 700);

            closeButton.addEventListener('click', () => {
                reminder.classList.remove('is-visible');
                reminder.classList.add('is-hidden');
            });
        })();
    </script>
    <script src="{{ asset('js/public/buku-tamu.js') }}"></script>
@endpush