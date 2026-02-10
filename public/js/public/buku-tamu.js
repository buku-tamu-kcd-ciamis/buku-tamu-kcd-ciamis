/**
 * ===== BUKU TAMU - CADISDIK XIII =====
 * JavaScript untuk halaman buku tamu
 */

document.addEventListener("DOMContentLoaded", function () {
    // ===== DYNAMIC JENIS ID (AUTOCOMPLETE) =====
    const jenisIdInput = document.getElementById("jenis_id_input");
    const jenisIdHidden = document.getElementById("jenis_id");
    const jenisIdList = document.getElementById("jenis_id_list");
    const nikLabel = document.getElementById("nik_label");
    const nikInput = document.getElementById("nik");
    const nikIcon = document.getElementById("nik_icon");

    const jenisIdOptions = [
        { value: "KTP", label: "KTP" },
        { value: "SIM", label: "SIM" },
        { value: "Passport", label: "Passport" },
        { value: "Kartu Pelajar", label: "Kartu Pelajar" },
        { value: "Kartu Pers", label: "Kartu Pers" },
        { value: "Kartu Pegawai", label: "Kartu Pegawai / ASN" },
        { value: "NIP", label: "NIP" },
        { value: "KITAS", label: "KITAS / KITAP" },
        { value: "Kartu Anggota", label: "Kartu Anggota" },
        { value: "Lainnya", label: "Lainnya" },
    ];

    const idConfig = {
        "": {
            label: "Nomor ID",
            placeholder: "Pilih jenis ID terlebih dahulu",
            icon: "fa-address-card",
            digits: null,
        },
        KTP: {
            label: "NIK",
            placeholder: "Masukkan 16 digit NIK",
            icon: "fa-id-card",
            digits: 16,
        },
        SIM: {
            label: "No. SIM",
            placeholder: "Masukkan 12 digit No. SIM",
            icon: "fa-car",
            digits: 12,
        },
        Passport: {
            label: "No. Passport",
            placeholder: "Masukkan nomor passport",
            icon: "fa-passport",
            digits: null,
        },
        "Kartu Pelajar": {
            label: "No. Induk Siswa",
            placeholder: "Masukkan NIS / NISN",
            icon: "fa-graduation-cap",
            digits: null,
        },
        "Kartu Pers": {
            label: "No. Kartu Pers",
            placeholder: "Masukkan nomor kartu pers",
            icon: "fa-newspaper",
            digits: null,
        },
        "Kartu Pegawai": {
            label: "NIP / No. Pegawai",
            placeholder: "Masukkan NIP atau no. pegawai",
            icon: "fa-user-tie",
            digits: null,
        },
        NIP: {
            label: "NIP",
            placeholder: "Masukkan NIP (18 digit)",
            icon: "fa-user-tie",
            digits: 18,
        },
        KITAS: {
            label: "No. KITAS/KITAP",
            placeholder: "Masukkan nomor KITAS/KITAP",
            icon: "fa-globe",
            digits: null,
        },
        "Kartu Anggota": {
            label: "No. Anggota",
            placeholder: "Masukkan nomor kartu anggota",
            icon: "fa-id-badge",
            digits: null,
        },
        Lainnya: {
            label: "Nomor Identitas",
            placeholder: "Masukkan nomor identitas Anda",
            icon: "fa-fingerprint",
            digits: null,
        },
    };

    let activeIndex = -1;

    function renderList(filter) {
        const query = (filter || "").toLowerCase();
        const filtered = query
            ? jenisIdOptions.filter(
                  (o) =>
                      o.label.toLowerCase().includes(query) ||
                      o.value.toLowerCase().includes(query),
              )
            : jenisIdOptions;

        jenisIdList.innerHTML = "";
        activeIndex = -1;

        if (filtered.length === 0) {
            jenisIdList.classList.remove("show");
            return;
        }

        filtered.forEach((opt, i) => {
            const div = document.createElement("div");
            div.className = "autocomplete-item";
            div.textContent = opt.label;
            div.dataset.value = opt.value;
            div.addEventListener("mousedown", function (e) {
                e.preventDefault();
                selectOption(opt);
            });
            jenisIdList.appendChild(div);
        });
        jenisIdList.classList.add("show");
    }

    function selectOption(opt) {
        jenisIdInput.value = opt.label;
        jenisIdHidden.value = opt.value;
        jenisIdList.classList.remove("show");

        const config = idConfig[opt.value] || idConfig[""];
        nikLabel.innerHTML = config.label + ' <span class="required">*</span>';
        nikInput.placeholder = config.placeholder;
        nikIcon.className = "fa-solid " + config.icon + " input-icon";
        nikInput.value = "";

        // Update hint & maxlength
        const nikHint = document.getElementById("nik_hint");
        if (config.digits) {
            nikInput.maxLength = config.digits;
            nikHint.textContent = "Wajib " + config.digits + " digit";
            nikHint.className = "phone-hint";
        } else {
            nikInput.removeAttribute("maxlength");
            nikHint.textContent = "";
            nikHint.className = "phone-hint";
        }

        nikInput.focus();
    }

    jenisIdInput.addEventListener("focus", function () {
        renderList(this.value);
    });

    jenisIdInput.addEventListener("input", function () {
        jenisIdHidden.value = "";
        renderList(this.value);
    });

    jenisIdInput.addEventListener("blur", function () {
        setTimeout(() => {
            jenisIdList.classList.remove("show");
            // If typed text doesn't match any option, clear
            if (!jenisIdHidden.value) {
                const match = jenisIdOptions.find(
                    (o) => o.label.toLowerCase() === this.value.toLowerCase(),
                );
                if (match) {
                    selectOption(match);
                } else {
                    this.value = "";
                    jenisIdHidden.value = "";
                    const config = idConfig[""];
                    nikLabel.innerHTML =
                        config.label + ' <span class="required">*</span>';
                    nikInput.placeholder = config.placeholder;
                    nikIcon.className =
                        "fa-solid " + config.icon + " input-icon";
                }
            }
        }, 150);
    });

    jenisIdInput.addEventListener("keydown", function (e) {
        const items = jenisIdList.querySelectorAll(".autocomplete-item");
        if (e.key === "ArrowDown") {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
            items.forEach((el, i) =>
                el.classList.toggle("active", i === activeIndex),
            );
            if (items[activeIndex])
                items[activeIndex].scrollIntoView({ block: "nearest" });
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            items.forEach((el, i) =>
                el.classList.toggle("active", i === activeIndex),
            );
            if (items[activeIndex])
                items[activeIndex].scrollIntoView({ block: "nearest" });
        } else if (e.key === "Enter") {
            e.preventDefault();
            if (activeIndex >= 0 && items[activeIndex]) {
                const val = items[activeIndex].dataset.value;
                const opt = jenisIdOptions.find((o) => o.value === val);
                if (opt) selectOption(opt);
            }
        } else if (e.key === "Escape") {
            jenisIdList.classList.remove("show");
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (e) {
        if (
            !jenisIdInput.contains(e.target) &&
            !jenisIdList.contains(e.target)
        ) {
            jenisIdList.classList.remove("show");
        }
    });

    // ===== BARCODE SURVEY MODAL =====
    const btnBarcode = document.getElementById("btnBarcode");
    const barcodeModal = document.getElementById("barcodeModal");
    const btnCloseBarcode = document.getElementById("btnCloseBarcode");

    // --- Draggable floating button ---
    let isDragging = false;
    let dragStartX, dragStartY, btnStartX, btnStartY;
    let hasDragged = false;

    function onDragStart(e) {
        isDragging = true;
        hasDragged = false;
        const touch = e.touches ? e.touches[0] : e;
        const rect = btnBarcode.getBoundingClientRect();
        dragStartX = touch.clientX;
        dragStartY = touch.clientY;
        btnStartX = rect.left;
        btnStartY = rect.top;
        btnBarcode.classList.add("dragging");
        e.preventDefault();
    }

    function onDragMove(e) {
        if (!isDragging) return;
        const touch = e.touches ? e.touches[0] : e;
        const dx = touch.clientX - dragStartX;
        const dy = touch.clientY - dragStartY;
        if (Math.abs(dx) > 3 || Math.abs(dy) > 3) hasDragged = true;

        let newX = btnStartX + dx;
        let newY = btnStartY + dy;

        // Keep within viewport
        const w = btnBarcode.offsetWidth;
        const h = btnBarcode.offsetHeight;
        newX = Math.max(0, Math.min(window.innerWidth - w, newX));
        newY = Math.max(0, Math.min(window.innerHeight - h, newY));

        btnBarcode.style.left = newX + "px";
        btnBarcode.style.top = newY + "px";
        btnBarcode.style.right = "auto";
        btnBarcode.style.bottom = "auto";
        e.preventDefault();
    }

    function onDragEnd() {
        if (!isDragging) return;
        isDragging = false;
        btnBarcode.classList.remove("dragging");
    }

    btnBarcode.addEventListener("mousedown", onDragStart);
    document.addEventListener("mousemove", onDragMove);
    document.addEventListener("mouseup", onDragEnd);
    btnBarcode.addEventListener("touchstart", onDragStart, { passive: false });
    document.addEventListener("touchmove", onDragMove, { passive: false });
    document.addEventListener("touchend", onDragEnd);

    // Only open modal if not dragged
    btnBarcode.addEventListener("click", function (e) {
        e.preventDefault();
        if (!hasDragged) {
            barcodeModal.classList.add("active");
        }
    });

    btnCloseBarcode.addEventListener("click", function () {
        barcodeModal.classList.remove("active");
    });

    barcodeModal.addEventListener("click", function (e) {
        if (e.target === barcodeModal) {
            barcodeModal.classList.remove("active");
        }
    });

    // ===== NIK REAL-TIME VALIDATION =====
    nikInput.addEventListener("input", function () {
        const selectedId = jenisIdHidden.value;
        const config = idConfig[selectedId] || idConfig[""];
        const nikHint = document.getElementById("nik_hint");

        if (config.digits) {
            // Only allow digits for types with digit requirement
            this.value = this.value.replace(/[^0-9]/g, "");
            const count = this.value.length;

            if (count === 0) {
                nikHint.textContent = "Wajib " + config.digits + " digit";
                nikHint.className = "phone-hint";
            } else if (count < config.digits) {
                nikHint.textContent =
                    count +
                    " digit \u2014 kurang " +
                    (config.digits - count) +
                    " digit lagi";
                nikHint.className = "phone-hint invalid";
            } else {
                nikHint.textContent =
                    "\u2713 " + config.digits + " digit \u2014 valid";
                nikHint.className = "phone-hint valid";
            }
        } else {
            nikHint.textContent = "";
            nikHint.className = "phone-hint";
        }
    });

    // ===== NOMOR HANDPHONE (+62 AUTO FORMAT) =====
    const phoneInput = document.getElementById("nomor_hp");
    const phoneHint = document.getElementById("phone_hint");
    const MIN_DIGITS = 9; // min digits after +62 (e.g. 812345678)
    const MAX_DIGITS = 13; // max digits after +62

    phoneInput.addEventListener("input", function (e) {
        // Strip leading 0 or +62 if user types it
        let raw = this.value.replace(/[^0-9]/g, "");

        // If user typed leading 0, remove it (already have +62)
        if (raw.startsWith("62")) {
            raw = raw.substring(2);
        }
        if (raw.startsWith("0")) {
            raw = raw.substring(1);
        }

        // Limit to max digits
        if (raw.length > MAX_DIGITS) {
            raw = raw.substring(0, MAX_DIGITS);
        }

        // Format: 8xx-xxxx-xxxx
        let formatted = "";
        for (let i = 0; i < raw.length; i++) {
            if (i === 3 || i === 7) formatted += "-";
            formatted += raw[i];
        }
        this.value = formatted;

        // Validation hint
        const digitCount = raw.length;
        if (digitCount === 0) {
            phoneHint.textContent =
                "Min. 9 digit, Maks. 13 digit (setelah +62)";
            phoneHint.className = "phone-hint";
        } else if (digitCount < MIN_DIGITS) {
            phoneHint.textContent =
                digitCount +
                " digit \u2014 kurang " +
                (MIN_DIGITS - digitCount) +
                " digit lagi";
            phoneHint.className = "phone-hint invalid";
        } else if (digitCount >= MIN_DIGITS && digitCount <= MAX_DIGITS) {
            phoneHint.textContent =
                "\u2713 " + digitCount + " digit \u2014 nomor valid";
            phoneHint.className = "phone-hint valid";
        }
    });

    // Prevent non-numeric input
    phoneInput.addEventListener("keydown", function (e) {
        // Allow: backspace, delete, tab, escape, enter, arrows
        if ([8, 9, 13, 27, 46, 37, 38, 39, 40].includes(e.keyCode)) return;
        // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
        if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].includes(e.keyCode))
            return;
        // Block non-numbers
        if (
            (e.keyCode < 48 || e.keyCode > 57) &&
            (e.keyCode < 96 || e.keyCode > 105)
        ) {
            e.preventDefault();
        }
    });

    // Handle paste
    phoneInput.addEventListener("paste", function (e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData(
            "text",
        );
        let clean = pasted.replace(/[^0-9]/g, "");
        if (clean.startsWith("62")) clean = clean.substring(2);
        if (clean.startsWith("0")) clean = clean.substring(1);
        this.value = clean;
        this.dispatchEvent(new Event("input"));
    });

    // ===== KABUPATEN/KOTA AUTOCOMPLETE (JAWA BARAT) =====
    const kabKotaData = [
        "Kab. Bandung",
        "Kab. Bandung Barat",
        "Kab. Bekasi",
        "Kab. Bogor",
        "Kab. Ciamis",
        "Kab. Cianjur",
        "Kab. Cirebon",
        "Kab. Garut",
        "Kab. Indramayu",
        "Kab. Karawang",
        "Kab. Kuningan",
        "Kab. Majalengka",
        "Kab. Pangandaran",
        "Kab. Purwakarta",
        "Kab. Subang",
        "Kab. Sukabumi",
        "Kab. Sumedang",
        "Kab. Tasikmalaya",
        "Kota Bandung",
        "Kota Banjar",
        "Kota Bekasi",
        "Kota Bogor",
        "Kota Cimahi",
        "Kota Cirebon",
        "Kota Depok",
        "Kota Sukabumi",
        "Kota Tasikmalaya",
    ];

    const kabKotaInput = document.getElementById("kabupaten_kota");
    const kabKotaList = document.getElementById("kabkota_list");
    let kabKotaActiveIdx = -1;

    function renderKabKota(filter) {
        const query = (filter || "").toLowerCase();
        const filtered = query
            ? kabKotaData.filter((k) => k.toLowerCase().includes(query))
            : kabKotaData;

        kabKotaList.innerHTML = "";
        kabKotaActiveIdx = -1;

        if (filtered.length === 0) {
            kabKotaList.classList.remove("show");
            return;
        }

        filtered.forEach((name) => {
            const div = document.createElement("div");
            div.className = "autocomplete-item";
            // Bold the matching part
            if (query) {
                const idx = name.toLowerCase().indexOf(query);
                div.innerHTML =
                    name.substring(0, idx) +
                    "<strong>" +
                    name.substring(idx, idx + query.length) +
                    "</strong>" +
                    name.substring(idx + query.length);
            } else {
                div.textContent = name;
            }
            div.dataset.value = name;
            div.addEventListener("mousedown", function (e) {
                e.preventDefault();
                kabKotaInput.value = name;
                kabKotaList.classList.remove("show");
            });
            kabKotaList.appendChild(div);
        });
        kabKotaList.classList.add("show");
    }

    kabKotaInput.addEventListener("focus", function () {
        renderKabKota(this.value);
    });

    kabKotaInput.addEventListener("input", function () {
        renderKabKota(this.value);
    });

    kabKotaInput.addEventListener("blur", function () {
        setTimeout(() => kabKotaList.classList.remove("show"), 150);
    });

    kabKotaInput.addEventListener("keydown", function (e) {
        const items = kabKotaList.querySelectorAll(".autocomplete-item");
        if (e.key === "ArrowDown") {
            e.preventDefault();
            kabKotaActiveIdx = Math.min(kabKotaActiveIdx + 1, items.length - 1);
            items.forEach((el, i) =>
                el.classList.toggle("active", i === kabKotaActiveIdx),
            );
            if (items[kabKotaActiveIdx])
                items[kabKotaActiveIdx].scrollIntoView({ block: "nearest" });
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            kabKotaActiveIdx = Math.max(kabKotaActiveIdx - 1, 0);
            items.forEach((el, i) =>
                el.classList.toggle("active", i === kabKotaActiveIdx),
            );
            if (items[kabKotaActiveIdx])
                items[kabKotaActiveIdx].scrollIntoView({ block: "nearest" });
        } else if (e.key === "Enter") {
            e.preventDefault();
            if (kabKotaActiveIdx >= 0 && items[kabKotaActiveIdx]) {
                kabKotaInput.value = items[kabKotaActiveIdx].dataset.value;
                kabKotaList.classList.remove("show");
            }
        } else if (e.key === "Escape") {
            kabKotaList.classList.remove("show");
        }
    });

    // Close kabkota dropdown when clicking outside
    document.addEventListener("click", function (e) {
        if (
            !kabKotaInput.contains(e.target) &&
            !kabKotaList.contains(e.target)
        ) {
            kabKotaList.classList.remove("show");
        }
    });

    // ===== KAMERA / FOTO SELFIE =====
    const btnCameraSelfie = document.getElementById("btnCameraSelfie");
    const fotoSelfieBox = document.getElementById("fotoSelfieBox");
    const fotoSelfieInput = document.getElementById("fotoSelfieInput");

    // ===== KAMERA / FOTO PENERIMAAN BERKAS =====
    const btnCameraPenerimaan = document.getElementById("btnCameraPenerimaan");
    const fotoPenerimaanBox = document.getElementById("fotoPenerimaanBox");
    const fotoPenerimaanInput = document.getElementById("fotoPenerimaanInput");

    // ===== FORM VALIDATION HELPER =====
    function validateFormBeforeAction(actionName) {
        const requiredFields = [
            {
                id: "jenis_id",
                label: "Jenis ID",
                checkHidden: true,
                inputId: "jenis_id_input",
            },
            {
                id: "nik",
                label: document
                    .getElementById("nik_label")
                    .textContent.replace("*", "")
                    .trim(),
                validateDigits: true,
            },
            { id: "nama_lengkap", label: "Nama Lengkap" },
            { id: "nomor_hp", label: "Nomor Handphone", minDigits: 9 },
            { id: "kabupaten_kota", label: "Kabupaten/Kota Instansi" },
            { id: "bagian_dituju", label: "Bagian Yang Dituju" },
            { id: "keperluan", label: "Keperluan" },
        ];

        const emptyFields = [];
        let firstEmptyEl = null;

        requiredFields.forEach(function (f) {
            const el = document.getElementById(f.id);
            let isEmpty = false;

            if (f.checkHidden) {
                isEmpty = !el.value.trim();
            } else if (f.minDigits) {
                const digits = el.value.replace(/[^0-9]/g, "");
                isEmpty = digits.length < f.minDigits;
            } else if (f.validateDigits) {
                const selectedId = jenisIdHidden.value;
                const cfg = idConfig[selectedId] || idConfig[""];
                if (cfg.digits) {
                    const d = el.value.replace(/[^0-9]/g, "");
                    if (d.length !== cfg.digits) {
                        isEmpty = true;
                        f.label = f.label + " (harus " + cfg.digits + " digit)";
                    }
                } else {
                    isEmpty = !el.value.trim();
                }
            } else {
                isEmpty = !el.value.trim();
            }

            if (isEmpty) {
                emptyFields.push(f.label);
                const formGroup = (
                    f.inputId ? document.getElementById(f.inputId) : el
                ).closest(".form-group");
                if (formGroup) {
                    formGroup.classList.add("shake");
                    setTimeout(() => formGroup.classList.remove("shake"), 600);
                }
                if (!firstEmptyEl) {
                    firstEmptyEl = f.inputId
                        ? document.getElementById(f.inputId)
                        : el;
                }
            }
        });

        if (emptyFields.length > 0) {
            showToast(
                '<i class="fa-solid fa-circle-exclamation"></i> Lengkapi form terlebih dahulu sebelum ' +
                    actionName +
                    ": " +
                    emptyFields.join(", "),
            );
            if (firstEmptyEl) firstEmptyEl.focus();
            return false;
        }
        return true;
    }

    function showToast(message) {
        let toast = document.getElementById("formToast");
        if (!toast) {
            toast = document.createElement("div");
            toast.id = "formToast";
            toast.className = "toast-notification";
            document.body.appendChild(toast);
        }
        toast.innerHTML = message;
        toast.classList.add("show");
        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(() => toast.classList.remove("show"), 4000);
    }

    // ===== SEQUENTIAL CAMERA & TTD LOGIC =====
    // Track state: which camera is active, which photos are done
    let activeCameraId = null; // null | 'selfie' | 'penerimaan'
    let selfieDone = false;
    let penerimaanDone = false;

    function stopActiveCamera() {
        if (activeCameraId === "selfie" && selfieLocalStream) {
            selfieLocalStream.getTracks().forEach((t) => t.stop());
            selfieLocalStream = null;
            activeCameraId = null;
            // Reset selfie box
            fotoSelfieBox.innerHTML =
                '<div class="camera-icon"><i class="fa-solid fa-camera"></i></div><p>Tekan tombol dibawah untuk<br>mengambil foto selfie.</p>';
            btnCameraSelfie.textContent = "Mulai Kamera";
        }
        if (activeCameraId === "penerimaan" && penerimaanLocalStream) {
            penerimaanLocalStream.getTracks().forEach((t) => t.stop());
            penerimaanLocalStream = null;
            activeCameraId = null;
            fotoPenerimaanBox.innerHTML =
                '<div class="camera-icon"><i class="fa-solid fa-handshake"></i></div><p>Foto bersama resepsionis<br>saat penerimaan berkas.</p>';
            btnCameraPenerimaan.textContent = "Mulai Kamera";
        }
    }

    // --- FOTO SELFIE ---
    let selfieLocalStream = null;
    let selfieCameraActive = false;

    btnCameraSelfie.addEventListener("click", function () {
        if (
            !selfieCameraActive &&
            !validateFormBeforeAction("mengambil foto selfie")
        )
            return;

        // If another camera is active, block
        if (
            !selfieCameraActive &&
            activeCameraId &&
            activeCameraId !== "selfie"
        ) {
            showToast(
                '<i class="fa-solid fa-circle-exclamation"></i> Matikan kamera ' +
                    (activeCameraId === "penerimaan"
                        ? "penerimaan berkas"
                        : "selfie") +
                    " terlebih dahulu!",
            );
            return;
        }

        if (!selfieCameraActive) {
            navigator.mediaDevices
                .getUserMedia({ video: { facingMode: "user" } })
                .then(function (mediaStream) {
                    selfieLocalStream = mediaStream;
                    selfieCameraActive = true;
                    activeCameraId = "selfie";

                    fotoSelfieBox.innerHTML = "";
                    const video = document.createElement("video");
                    video.srcObject = mediaStream;
                    video.autoplay = true;
                    video.playsInline = true;
                    video.style.width = "100%";
                    video.style.borderRadius = "8px";
                    fotoSelfieBox.appendChild(video);

                    btnCameraSelfie.textContent = "Ambil Foto";
                })
                .catch(function (err) {
                    alert(
                        "Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.",
                    );
                    console.error(err);
                });
        } else {
            // Capture
            const video = fotoSelfieBox.querySelector("video");
            const canvas = document.createElement("canvas");
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext("2d").drawImage(video, 0, 0);

            const imageData = canvas.toDataURL("image/png");
            fotoSelfieInput.value = imageData;

            selfieLocalStream.getTracks().forEach((t) => t.stop());
            selfieLocalStream = null;
            selfieCameraActive = false;
            activeCameraId = null;
            selfieDone = true;

            fotoSelfieBox.innerHTML = "";
            const img = document.createElement("img");
            img.src = imageData;
            img.style.width = "100%";
            img.style.borderRadius = "8px";
            fotoSelfieBox.appendChild(img);

            btnCameraSelfie.textContent = "Ulangi Foto";
        }
    });

    // --- FOTO PENERIMAAN BERKAS ---
    let penerimaanLocalStream = null;
    let penerimaanCameraActive = false;

    btnCameraPenerimaan.addEventListener("click", function () {
        // Must take selfie first
        if (!penerimaanCameraActive && !selfieDone) {
            showToast(
                '<i class="fa-solid fa-circle-exclamation"></i> Ambil foto selfie terlebih dahulu!',
            );
            fotoSelfieBox.closest(".form-group").classList.add("shake");
            setTimeout(
                () =>
                    fotoSelfieBox
                        .closest(".form-group")
                        .classList.remove("shake"),
                600,
            );
            return;
        }

        if (
            !penerimaanCameraActive &&
            !validateFormBeforeAction("mengambil foto penerimaan berkas")
        )
            return;

        // If another camera is active, block
        if (
            !penerimaanCameraActive &&
            activeCameraId &&
            activeCameraId !== "penerimaan"
        ) {
            showToast(
                '<i class="fa-solid fa-circle-exclamation"></i> Matikan kamera selfie terlebih dahulu!',
            );
            return;
        }

        if (!penerimaanCameraActive) {
            navigator.mediaDevices
                .getUserMedia({ video: { facingMode: "environment" } })
                .then(function (mediaStream) {
                    penerimaanLocalStream = mediaStream;
                    penerimaanCameraActive = true;
                    activeCameraId = "penerimaan";

                    fotoPenerimaanBox.innerHTML = "";
                    const video = document.createElement("video");
                    video.srcObject = mediaStream;
                    video.autoplay = true;
                    video.playsInline = true;
                    video.style.width = "100%";
                    video.style.borderRadius = "8px";
                    fotoPenerimaanBox.appendChild(video);

                    btnCameraPenerimaan.textContent = "Ambil Foto";
                })
                .catch(function (err) {
                    alert(
                        "Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.",
                    );
                    console.error(err);
                });
        } else {
            // Capture
            const video = fotoPenerimaanBox.querySelector("video");
            const canvas = document.createElement("canvas");
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext("2d").drawImage(video, 0, 0);

            const imageData = canvas.toDataURL("image/png");
            fotoPenerimaanInput.value = imageData;

            penerimaanLocalStream.getTracks().forEach((t) => t.stop());
            penerimaanLocalStream = null;
            penerimaanCameraActive = false;
            activeCameraId = null;
            penerimaanDone = true;

            fotoPenerimaanBox.innerHTML = "";
            const img = document.createElement("img");
            img.src = imageData;
            img.style.width = "100%";
            img.style.borderRadius = "8px";
            fotoPenerimaanBox.appendChild(img);

            btnCameraPenerimaan.textContent = "Ulangi Foto";
        }
    });

    // ===== TANDA TANGAN =====
    const btnTtd = document.getElementById("btnTtd");
    const ttdModal = document.getElementById("ttdModal");
    const signatureCanvas = document.getElementById("signatureCanvas");
    const ctx = signatureCanvas.getContext("2d");
    const btnClearTtd = document.getElementById("btnClearTtd");
    const btnSaveTtd = document.getElementById("btnSaveTtd");
    const btnCancelTtd = document.getElementById("btnCancelTtd");
    const ttdBox = document.getElementById("ttdBox");
    const ttdInput = document.getElementById("ttdInput");
    let isDrawing = false;

    function resizeCanvas() {
        const rect = signatureCanvas.getBoundingClientRect();
        signatureCanvas.width = rect.width;
        signatureCanvas.height = rect.height;
        ctx.strokeStyle = "#333";
        ctx.lineWidth = 2;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
    }

    btnTtd.addEventListener("click", function () {
        if (!validateFormBeforeAction("tanda tangan")) return;

        // Must take both photos first
        if (!selfieDone) {
            showToast(
                '<i class="fa-solid fa-circle-exclamation"></i> Ambil foto selfie terlebih dahulu!',
            );
            fotoSelfieBox.closest(".form-group").classList.add("shake");
            setTimeout(
                () =>
                    fotoSelfieBox
                        .closest(".form-group")
                        .classList.remove("shake"),
                600,
            );
            return;
        }

        // Block if camera still active
        if (activeCameraId) {
            showToast(
                '<i class="fa-solid fa-circle-exclamation"></i> Matikan kamera terlebih dahulu!',
            );
            return;
        }

        ttdModal.classList.add("active");
        setTimeout(resizeCanvas, 100);
    });

    btnCancelTtd.addEventListener("click", function () {
        ttdModal.classList.remove("active");
    });

    btnClearTtd.addEventListener("click", function () {
        ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
    });

    btnSaveTtd.addEventListener("click", function () {
        const imageData = signatureCanvas.toDataURL("image/png");
        ttdInput.value = imageData;

        ttdBox.innerHTML = "";
        const img = document.createElement("img");
        img.src = imageData;
        img.style.width = "100%";
        img.style.borderRadius = "8px";
        ttdBox.appendChild(img);

        ttdModal.classList.remove("active");
        btnTtd.textContent = "Ulangi Tanda Tangan";
    });

    // Drawing on canvas
    function getPos(e) {
        const rect = signatureCanvas.getBoundingClientRect();
        if (e.touches) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top,
            };
        }
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top,
        };
    }

    signatureCanvas.addEventListener("mousedown", (e) => {
        isDrawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    });

    signatureCanvas.addEventListener("mousemove", (e) => {
        if (!isDrawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    });

    signatureCanvas.addEventListener("mouseup", () => {
        isDrawing = false;
    });
    signatureCanvas.addEventListener("mouseleave", () => {
        isDrawing = false;
    });

    // Touch support
    signatureCanvas.addEventListener("touchstart", (e) => {
        e.preventDefault();
        isDrawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    });

    signatureCanvas.addEventListener("touchmove", (e) => {
        e.preventDefault();
        if (!isDrawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    });

    signatureCanvas.addEventListener("touchend", () => {
        isDrawing = false;
    });

    // Close modal on overlay click
    ttdModal.addEventListener("click", function (e) {
        if (e.target === ttdModal) {
            ttdModal.classList.remove("active");
        }
    });
});
