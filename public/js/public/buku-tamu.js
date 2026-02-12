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

    // ===== NIK REAL-TIME VALIDATION & AUTO-FILL =====
    let autoFillTimeout = null;

    // Function to check if number has more than 3 consecutive repeated digits
    function hasRepeatedDigits(value) {
        const regex = /(\d)\1{3,}/; // Matches any digit repeated 4 or more times
        return regex.test(value);
    }

    // Function to check if number has more than 2 consecutive sequential digits
    function hasSequentialDigits(value) {
        for (let i = 0; i < value.length - 2; i++) {
            const digit1 = parseInt(value[i]);
            const digit2 = parseInt(value[i + 1]);
            const digit3 = parseInt(value[i + 2]);

            // Check ascending sequence (e.g., 123, 234, 345)
            if (digit2 === digit1 + 1 && digit3 === digit2 + 1) {
                return true;
            }

            // Check descending sequence (e.g., 321, 432, 543)
            if (digit2 === digit1 - 1 && digit3 === digit2 - 1) {
                return true;
            }
        }
        return false;
    }

    nikInput.addEventListener("input", function () {
        const selectedId = jenisIdHidden.value;
        const config = idConfig[selectedId] || idConfig[""];
        const nikHint = document.getElementById("nik_hint");

        if (config.digits) {
            // Only allow digits for types with digit requirement
            this.value = this.value.replace(/[^0-9]/g, "");
            const count = this.value.length;

            // Check for repeated digits
            if (count > 0 && hasRepeatedDigits(this.value)) {
                nikHint.textContent =
                    "\u2717 Angka tidak boleh sama lebih dari 3 digit berturut-turut";
                nikHint.className = "phone-hint invalid";
                return;
            }

            // Check for sequential digits
            if (count > 0 && hasSequentialDigits(this.value)) {
                nikHint.textContent =
                    "\u2717 Angka tidak boleh berurutan lebih dari 2 digit";
                nikHint.className = "phone-hint invalid";
                return;
            }

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

                // Auto-fill data jika NIK sudah pernah terdaftar
                clearTimeout(autoFillTimeout);
                autoFillTimeout = setTimeout(() => {
                    fetchGuestDataByNik(this.value);
                }, 500);
            }
        } else {
            nikHint.textContent = "";
            nikHint.className = "phone-hint";
        }
    });

    // Function to fetch guest data by NIK
    async function fetchGuestDataByNik(nik) {
        if (!nik) return;

        try {
            const response = await fetch(
                `/api/guest-by-nik?nik=${encodeURIComponent(nik)}`,
            );
            const result = await response.json();

            if (result.found && result.data) {
                const data = result.data;

                // Auto-fill form fields
                document.getElementById("nama_lengkap").value =
                    data.nama_lengkap || "";
                document.getElementById("instansi").value = data.instansi || "";
                document.getElementById("jabatan").value = data.jabatan || "";
                document.getElementById("kabupaten_kota").value =
                    data.kabupaten_kota || "";
                document.getElementById("email").value = data.email || "";

                // Handle phone number - remove +62 prefix if present
                if (data.nomor_hp) {
                    let phoneNumber = data.nomor_hp.replace(/[^0-9]/g, "");
                    if (phoneNumber.startsWith("62")) {
                        phoneNumber = phoneNumber.substring(2);
                    }
                    if (phoneNumber.startsWith("0")) {
                        phoneNumber = phoneNumber.substring(1);
                    }

                    // Format: 8xx-xxxx-xxxx
                    let formatted = "";
                    for (let i = 0; i < phoneNumber.length; i++) {
                        if (i === 3 || i === 7) formatted += "-";
                        formatted += phoneNumber[i];
                    }
                    document.getElementById("nomor_hp").value = formatted;

                    // Trigger validation hint
                    phoneInput.dispatchEvent(
                        new Event("input", { bubbles: true }),
                    );
                }

                // Show notification
                const nikHint = document.getElementById("nik_hint");
                const originalText = nikHint.textContent;
                nikHint.textContent =
                    "\u2713 Data otomatis terisi dari kunjungan sebelumnya";
                nikHint.className = "phone-hint valid";

                setTimeout(() => {
                    nikHint.textContent = originalText;
                }, 3000);
            }
        } catch (error) {
            console.error("Error fetching guest data:", error);
        }
    }

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
    const btnFlipSelfie = document.getElementById("btnFlipSelfie");

    // ===== KAMERA / FOTO PENERIMAAN BERKAS =====
    const btnCameraPenerimaan = document.getElementById("btnCameraPenerimaan");
    const fotoPenerimaanBox = document.getElementById("fotoPenerimaanBox");
    const fotoPenerimaanInput = document.getElementById("fotoPenerimaanInput");
    const btnFlipPenerimaan = document.getElementById("btnFlipPenerimaan");
    const btnClosePenerimaan = document.getElementById("btnClosePenerimaan");

    // ===== FACE DETECTION HELPER =====
    let faceDetectorSupported = false;
    let faceDetector = null;
    if (window.FaceDetector) {
        try {
            faceDetector = new FaceDetector({
                fastMode: false,
                maxDetectedFaces: 5,
            });
            faceDetectorSupported = true;
        } catch (e) {
            faceDetectorSupported = false;
        }
    }

    // Canvas-based skin-tone detection fallback (medium strictness)
    function detectFaceBySkinTone(canvas) {
        const ctx2 = canvas.getContext("2d");
        const w = canvas.width;
        const h = canvas.height;
        // Sample center 60% of the image (where faces usually are)
        const startX = Math.floor(w * 0.2);
        const startY = Math.floor(h * 0.1);
        const regionW = Math.floor(w * 0.6);
        const regionH = Math.floor(h * 0.6);
        const imageData = ctx2.getImageData(startX, startY, regionW, regionH);
        const data = imageData.data;
        let skinPixels = 0;
        const totalPixels = regionW * regionH;
        // Sample every 4th pixel for performance
        for (let i = 0; i < data.length; i += 16) {
            const r = data[i];
            const g = data[i + 1];
            const b = data[i + 2];
            // Skin detection using RGB rules (works for diverse skin tones)
            if (
                r > 60 &&
                g > 40 &&
                b > 20 &&
                r > g &&
                r > b &&
                r - g > 10 &&
                Math.max(r, g, b) - Math.min(r, g, b) > 15 &&
                Math.abs(r - g) > 10
            ) {
                skinPixels++;
            }
        }
        const skinRatio = skinPixels / (totalPixels / 4);
        // Medium strictness: 8% skin pixels in center region = face likely present
        return { detected: skinRatio > 0.08, confidence: skinRatio };
    }

    async function detectFaces(canvasOrBitmap, canvas) {
        // Try native FaceDetector first — this is the AUTHORITATIVE face check
        if (faceDetectorSupported && faceDetector) {
            try {
                const faces = await faceDetector.detect(canvasOrBitmap);
                if (faces.length > 0) {
                    return {
                        detected: true,
                        count: faces.length,
                        native: true,
                    };
                }
            } catch (e) {
                console.warn("FaceDetector failed, using fallback:", e);
            }
        }
        // Fallback: skin-tone — ONLY used if native API not available at all
        // Mark as non-native so selfie can reject it
        if (canvas) {
            var regions = detectFaceRegions(canvas);
            return {
                detected: regions.length > 0,
                count: regions.length,
                native: false,
            };
        }
        return { detected: false, count: 0, native: false };
    }

    // ===== CAMERA UTILITY =====
    // ===== LIVENESS DETECTION (anti-spoofing) =====
    // Stores recent frame data for motion analysis
    let livenessFrames = [];
    let livenessLastCapture = 0;
    const LIVENESS_FRAME_COUNT = 10;
    const LIVENESS_INTERVAL = 300; // ms between frame captures

    function captureLivenessFrame(video) {
        var now = Date.now();
        if (now - livenessLastCapture < LIVENESS_INTERVAL) return;
        livenessLastCapture = now;
        var c = document.createElement("canvas");
        var s = 100; // slightly larger for better analysis
        c.width = s;
        c.height = Math.round(s * (video.videoHeight / video.videoWidth));
        c.getContext("2d").drawImage(video, 0, 0, c.width, c.height);
        var data = c
            .getContext("2d")
            .getImageData(0, 0, c.width, c.height).data;
        // Store brightness values for 4 quadrants (to detect uniform vs localized motion)
        var cw = c.width,
            ch = c.height;
        var halfW = Math.floor(cw / 2),
            halfH = Math.floor(ch / 2);
        var quads = [[], [], [], []]; // TL, TR, BL, BR
        for (var y = 0; y < ch; y += 2) {
            for (var x = 0; x < cw; x += 2) {
                var i = (y * cw + x) * 4;
                var bri = Math.round((data[i] + data[i + 1] + data[i + 2]) / 3);
                var qi = (y < halfH ? 0 : 2) + (x < halfW ? 0 : 1);
                quads[qi].push(bri);
            }
        }
        livenessFrames.push(quads);
        if (livenessFrames.length > LIVENESS_FRAME_COUNT) {
            livenessFrames.shift();
        }
    }

    function resetLivenessFrames() {
        livenessFrames = [];
        livenessLastCapture = 0;
    }

    /**
     * Liveness check v2: strict anti-spoofing
     * Returns { live: bool, reason: string }
     * Checks:
     * 1. Micro-motion: frames must show movement (reject perfectly still photos)
     * 2. Regional motion variance: real faces move non-uniformly (eyes blink, head tilts)
     *    — a photo held by hand shakes UNIFORMLY across all quadrants
     * 3. Texture sharpness (Laplacian): real camera-to-face is sharp; photo-of-screen is soft/moiré
     * 4. Screen glare: look for specular highlight clusters (screens reflect light)
     * 5. Color depth: real faces have natural hue gradients
     */
    function checkLiveness(canvas) {
        var result = { live: true, reason: "" };

        // ===== Check 1: Micro-motion (must exist) =====
        if (livenessFrames.length >= 4) {
            var quadMotions = [0, 0, 0, 0];
            var comparisons = 0;
            for (var fi = 1; fi < livenessFrames.length; fi++) {
                var prev = livenessFrames[fi - 1];
                var curr = livenessFrames[fi];
                comparisons++;
                for (var qi = 0; qi < 4; qi++) {
                    var len = Math.min(prev[qi].length, curr[qi].length);
                    var diff = 0;
                    for (var pi = 0; pi < len; pi++) {
                        diff += Math.abs(curr[qi][pi] - prev[qi][pi]);
                    }
                    quadMotions[qi] += len > 0 ? diff / len : 0;
                }
            }
            // Average motion per quadrant
            for (var qi2 = 0; qi2 < 4; qi2++) {
                quadMotions[qi2] /= comparisons;
            }
            var totalMotion =
                (quadMotions[0] +
                    quadMotions[1] +
                    quadMotions[2] +
                    quadMotions[3]) /
                4;

            // Static photo: near-zero motion
            if (totalMotion < 0.8) {
                result.live = false;
                result.reason =
                    "Tidak terdeteksi gerakan — pastikan ini wajah asli langsung, bukan foto!";
                return result;
            }

            // ===== Check 2: Regional motion variance =====
            // A real face has NON-UNIFORM motion (e.g., eyes blink, mouth moves, head tilts)
            // A photo held by hand shakes ALL quadrants nearly equally
            var motionMin = Math.min.apply(null, quadMotions);
            var motionMax = Math.max.apply(null, quadMotions);
            var motionRange = motionMax - motionMin;
            var motionAvg = totalMotion;
            // Coefficient of variation of quadrant motions
            var motionVariance = 0;
            for (var qi3 = 0; qi3 < 4; qi3++) {
                motionVariance +=
                    (quadMotions[qi3] - motionAvg) *
                    (quadMotions[qi3] - motionAvg);
            }
            motionVariance = Math.sqrt(motionVariance / 4);
            var motionCV = motionAvg > 0 ? motionVariance / motionAvg : 0;

            // If all quadrants move almost identically (CV < 0.08), it's uniform shake = photo
            // Real faces have CV > 0.08 because different face parts move differently
            if (motionCV < 0.06 && totalMotion > 1.0 && totalMotion < 15) {
                result.live = false;
                result.reason =
                    "Gerakan terlalu seragam — seperti foto yang digoyang. Gunakan wajah asli!";
                return result;
            }
        } else {
            // Not enough frames yet — require waiting
            result.live = false;
            result.reason =
                "Tunggu sebentar... sedang memverifikasi wajah asli.";
            return result;
        }

        // ===== Check 3: Texture sharpness (Laplacian variance) =====
        var ctx2 = canvas.getContext("2d");
        var w = canvas.width,
            h = canvas.height;
        var rx = Math.floor(w * 0.2),
            ry = Math.floor(h * 0.1);
        var rw = Math.floor(w * 0.6),
            rh = Math.floor(h * 0.6);
        var imgData = ctx2.getImageData(rx, ry, rw, rh);
        var d = imgData.data;
        var step = 3;
        var sw = Math.floor(rw / step),
            sh = Math.floor(rh / step);
        var gray = [];
        for (var gy = 0; gy < sh; gy++) {
            gray[gy] = [];
            for (var gx = 0; gx < sw; gx++) {
                var gi = (gy * step * rw + gx * step) * 4;
                gray[gy][gx] = (d[gi] + d[gi + 1] + d[gi + 2]) / 3;
            }
        }
        var lapSum = 0,
            lapCount = 0;
        for (var ly = 1; ly < sh - 1; ly++) {
            for (var lx = 1; lx < sw - 1; lx++) {
                var lap =
                    -4 * gray[ly][lx] +
                    gray[ly - 1][lx] +
                    gray[ly + 1][lx] +
                    gray[ly][lx - 1] +
                    gray[ly][lx + 1];
                lapSum += lap * lap;
                lapCount++;
            }
        }
        var lapVariance = lapCount > 0 ? lapSum / lapCount : 0;
        // Photo-of-screen has moiré or extreme blurriness → low Laplacian
        // Real face has natural texture → higher Laplacian (>40)
        if (lapVariance < 40) {
            result.live = false;
            result.reason =
                "Gambar terlalu halus/buram — pastikan ini wajah asli langsung, bukan foto!";
            return result;
        }

        // ===== Check 4: Screen glare / specular highlight detection =====
        var veryBrightPx = 0,
            totalPx = 0;
        var clusterCount = 0;
        for (var si = 0; si < d.length; si += step * 4) {
            var sr = d[si],
                sg = d[si + 1],
                sb = d[si + 2];
            totalPx++;
            // Screen glare: very bright and near-white (low saturation)
            if (sr > 245 && sg > 245 && sb > 245) {
                veryBrightPx++;
            }
        }
        var brightRatio = totalPx > 0 ? veryBrightPx / totalPx : 0;
        // More than 8% pure-white pixels in the face region = screen reflection
        if (brightRatio > 0.08) {
            result.live = false;
            result.reason =
                "Terdeteksi pantulan layar — jangan gunakan foto dari HP/layar!";
            return result;
        }

        // ===== Check 5: Color naturalness (hue spread) =====
        var hueHist = new Array(36).fill(0);
        var skinPxCount = 0;
        for (var ci = 0; ci < d.length; ci += step * 4) {
            var cr2 = d[ci],
                cg2 = d[ci + 1],
                cb2 = d[ci + 2];
            var cmax2 = Math.max(cr2, cg2, cb2),
                cmin2 = Math.min(cr2, cg2, cb2);
            var cdiff2 = cmax2 - cmin2;
            if (cdiff2 > 15 && cr2 > 50 && cg2 > 30 && cr2 > cb2) {
                var hue2 = 0;
                if (cmax2 === cr2) hue2 = ((cg2 - cb2) / cdiff2) % 6;
                else if (cmax2 === cg2) hue2 = (cb2 - cr2) / cdiff2 + 2;
                else hue2 = (cr2 - cg2) / cdiff2 + 4;
                hue2 = Math.round(hue2 * 60);
                if (hue2 < 0) hue2 += 360;
                hueHist[Math.floor(hue2 / 10) % 36]++;
                skinPxCount++;
            }
        }
        if (skinPxCount > 50) {
            var nonZeroBuckets = hueHist.filter(function (v) {
                return v > skinPxCount * 0.02;
            }).length;
            // Real skin has 4+ distinct hue ranges; very flat = likely screen/print
            if (nonZeroBuckets <= 2) {
                result.live = false;
                result.reason =
                    "Warna terlalu seragam — pastikan ini wajah asli, bukan foto layar!";
                return result;
            }
        }

        return result;
    }

    function startCamera(facingMode) {
        return navigator.mediaDevices.getUserMedia({
            video: { facingMode: facingMode },
        });
    }

    function capturePhoto(video, isFrontCamera) {
        const canvas = document.createElement("canvas");
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx2 = canvas.getContext("2d");
        if (isFrontCamera) {
            // Flip horizontally to un-mirror front camera
            ctx2.translate(canvas.width, 0);
            ctx2.scale(-1, 1);
        }
        ctx2.drawImage(video, 0, 0);
        return { dataUrl: canvas.toDataURL("image/png"), canvas: canvas };
    }

    function createVideoElement(mediaStream, isFrontCamera) {
        const video = document.createElement("video");
        video.srcObject = mediaStream;
        video.autoplay = true;
        video.playsInline = true;
        video.style.width = "100%";
        video.style.borderRadius = "8px";
        if (isFrontCamera) {
            video.style.transform = "scaleX(-1)";
        }
        // Create wrapper with overlay canvas
        const wrapper = document.createElement("div");
        wrapper.className = "camera-preview-wrapper";
        wrapper.appendChild(video);
        const overlay = document.createElement("canvas");
        overlay.className = "detection-overlay";
        wrapper.appendChild(overlay);
        return { video: video, wrapper: wrapper, overlay: overlay };
    }

    // ===== ACTUAL CAMERA FACING DETECTION =====
    function isActuallyFrontCamera(mediaStream) {
        const track = mediaStream.getVideoTracks()[0];
        if (track) {
            const settings = track.getSettings();
            if (settings.facingMode) {
                return settings.facingMode === "user";
            }
        }
        // Desktop webcam: facingMode not reported → assumed front-facing
        return true;
    }

    // ===== LIVE DETECTION OVERLAY =====
    let detectionRAF = null;
    let lastDetectTime = 0;
    let currentFaceBoxes = [];
    let currentDocBoxes = [];
    let smoothFaceBoxes = [];
    let smoothDocBoxes = [];
    const DETECT_INTERVAL = 300; // ms between detections
    const SMOOTH_FACTOR = 0.4; // 0=no smoothing, 1=full previous

    // ===== AUTO-CAPTURE COUNTDOWN =====
    let autoCaptureActive = false;
    let autoCaptureCountdown = 0;
    let autoCaptureInterval = null;
    let autoCaptureMode = null;

    function startAutoCapture(mode) {
        if (autoCaptureActive) return;
        autoCaptureActive = true;
        autoCaptureCountdown = 3;
        autoCaptureMode = mode;
        autoCaptureInterval = setInterval(function () {
            autoCaptureCountdown--;
            if (autoCaptureCountdown <= 0) {
                cancelAutoCapture();
                // Trigger the capture button
                if (mode === "selfie") {
                    btnCameraSelfie.click();
                } else {
                    btnCameraPenerimaan.click();
                }
            }
        }, 1000);
    }

    function cancelAutoCapture() {
        if (autoCaptureInterval) {
            clearInterval(autoCaptureInterval);
            autoCaptureInterval = null;
        }
        autoCaptureActive = false;
        autoCaptureCountdown = 0;
        autoCaptureMode = null;
    }

    function smoothBox(prev, next) {
        if (!prev) return next;
        var f = SMOOTH_FACTOR;
        return {
            x: prev.x * f + next.x * (1 - f),
            y: prev.y * f + next.y * (1 - f),
            w: prev.w * f + next.w * (1 - f),
            h: prev.h * f + next.h * (1 - f),
        };
    }

    function matchAndSmooth(prevBoxes, newBoxes) {
        if (newBoxes.length === 0) return [];
        if (prevBoxes.length === 0) return newBoxes.slice();
        // Simple nearest-center matching
        var result = [];
        var used = [];
        for (var ni = 0; ni < newBoxes.length; ni++) {
            var nb = newBoxes[ni];
            var ncx = nb.x + nb.w / 2,
                ncy = nb.y + nb.h / 2;
            var bestDist = Infinity,
                bestIdx = -1;
            for (var pi = 0; pi < prevBoxes.length; pi++) {
                if (used.indexOf(pi) !== -1) continue;
                var pb = prevBoxes[pi];
                var pcx = pb.x + pb.w / 2,
                    pcy = pb.y + pb.h / 2;
                var dist = Math.sqrt(
                    (ncx - pcx) * (ncx - pcx) + (ncy - pcy) * (ncy - pcy),
                );
                if (dist < bestDist) {
                    bestDist = dist;
                    bestIdx = pi;
                }
            }
            if (bestIdx >= 0 && bestDist < 150) {
                used.push(bestIdx);
                result.push(smoothBox(prevBoxes[bestIdx], nb));
            } else {
                result.push(nb);
            }
        }
        return result;
    }

    // Find ALL face-like skin regions — focus on HEAD shapes only
    function detectFaceRegions(canvas) {
        var ctx2 = canvas.getContext("2d");
        var w = canvas.width,
            h = canvas.height;
        var cellSize = 8;
        var cols = Math.floor(w / cellSize);
        var rows = Math.floor(h / cellSize);
        var data = ctx2.getImageData(0, 0, w, h).data;
        // Only scan top 75% of frame (faces are in upper portion)
        var maxRow = Math.floor(rows * 0.75);
        var grid = [];
        for (var row = 0; row < rows; row++) {
            grid[row] = [];
            for (var col = 0; col < cols; col++) {
                if (row > maxRow) {
                    grid[row][col] = false;
                    continue;
                }
                var skinPx = 0,
                    total = 0;
                for (var dy = 0; dy < cellSize; dy += 2) {
                    for (var dx = 0; dx < cellSize; dx += 2) {
                        var px = col * cellSize + dx;
                        var py = row * cellSize + dy;
                        if (px < w && py < h) {
                            var i = (py * w + px) * 4;
                            var r = data[i],
                                g = data[i + 1],
                                b = data[i + 2];
                            var cmax = Math.max(r, g, b),
                                cmin = Math.min(r, g, b);
                            // Strict skin: must be warm tone, not white/gray
                            var isSkin =
                                r > 80 &&
                                g > 50 &&
                                b > 25 &&
                                r > g &&
                                r > b &&
                                r - g >= 15 &&
                                r - b >= 20 &&
                                cmax - cmin > 20 &&
                                r - g < 100 &&
                                // NOT paper-white: reject very bright pixels
                                r < 240 &&
                                g < 230 &&
                                b < 220 &&
                                // NOT gray/dark clothes
                                cmax > 90;
                            if (isSkin) skinPx++;
                            total++;
                        }
                    }
                }
                grid[row][col] = total > 0 && skinPx / total > 0.4;
            }
        }
        // Flood fill — 4-directional only (tighter clusters)
        var visited = [];
        for (var vr = 0; vr < rows; vr++) {
            visited[vr] = [];
            for (var vc = 0; vc < cols; vc++) visited[vr][vc] = false;
        }
        var regions = [];
        // Face size constraints relative to frame
        var frameArea = cols * rows;
        var minFaceCells = Math.max(8, Math.floor(frameArea * 0.005));
        var maxFaceCells = Math.floor(frameArea * 0.15); // face can't be >15% of frame
        for (var fr = 0; fr < maxRow; fr++) {
            for (var fc = 0; fc < cols; fc++) {
                if (grid[fr][fc] && !visited[fr][fc]) {
                    var queue = [{ r: fr, c: fc }];
                    visited[fr][fc] = true;
                    var minR = fr,
                        maxR = fr,
                        minC = fc,
                        maxC = fc,
                        size = 0;
                    while (queue.length > 0) {
                        var cur = queue.shift();
                        size++;
                        if (cur.r < minR) minR = cur.r;
                        if (cur.r > maxR) maxR = cur.r;
                        if (cur.c < minC) minC = cur.c;
                        if (cur.c > maxC) maxC = cur.c;
                        // 4-directional only — prevents merging adjacent faces
                        var dirs = [
                            [-1, 0],
                            [1, 0],
                            [0, -1],
                            [0, 1],
                        ];
                        for (var d = 0; d < dirs.length; d++) {
                            var nr = cur.r + dirs[d][0],
                                nc = cur.c + dirs[d][1];
                            if (
                                nr >= 0 &&
                                nr < rows &&
                                nc >= 0 &&
                                nc < cols &&
                                grid[nr][nc] &&
                                !visited[nr][nc]
                            ) {
                                visited[nr][nc] = true;
                                queue.push({ r: nr, c: nc });
                            }
                        }
                    }
                    if (size >= minFaceCells && size <= maxFaceCells) {
                        var bbW = maxC - minC + 1;
                        var bbH = maxR - minR + 1;
                        var rw = bbW * cellSize;
                        var rh = bbH * cellSize;
                        var aspect = rw / Math.max(rh, 1);
                        // Head shape: roughly 0.6 to 1.2 (portrait oval)
                        if (aspect >= 0.55 && aspect <= 1.3) {
                            var fillRatio = size / Math.max(bbW * bbH, 1);
                            // Must be >35% filled (compact blob, not scattered)
                            if (fillRatio > 0.35) {
                                var pad = cellSize;
                                regions.push({
                                    x: Math.max(0, minC * cellSize - pad),
                                    y: Math.max(0, minR * cellSize - pad),
                                    width: Math.min(
                                        w - Math.max(0, minC * cellSize - pad),
                                        rw + pad * 2,
                                    ),
                                    height: Math.min(
                                        h - Math.max(0, minR * cellSize - pad),
                                        rh + pad * 2,
                                    ),
                                    size: size,
                                    fill: fillRatio,
                                });
                            }
                        }
                    }
                }
            }
        }
        regions.sort(function (a, b) {
            return b.fill * b.size - a.fill * a.size;
        });
        return regions.slice(0, 5);
    }

    // Document detection: finds PAPER-WHITE rectangles, explicitly rejects skin
    function detectDocumentRegions(canvas, faceBoxes) {
        var ctx2 = canvas.getContext("2d");
        var w = canvas.width,
            h = canvas.height;
        var data = ctx2.getImageData(0, 0, w, h).data;
        var step = 2;
        var sw = Math.floor(w / step),
            sh = Math.floor(h / step);
        var gray = new Uint8Array(sw * sh);
        var isSkinMap = new Uint8Array(sw * sh); // track skin pixels to exclude
        for (var sy = 0; sy < sh; sy++) {
            for (var sx = 0; sx < sw; sx++) {
                var i = (sy * step * w + sx * step) * 4;
                var r = data[i],
                    g = data[i + 1],
                    b = data[i + 2];
                gray[sy * sw + sx] = Math.round((r + g + b) / 3);
                // Mark skin-colored pixels (to exclude from doc detection)
                var isSkin =
                    r > 80 &&
                    g > 50 &&
                    b > 25 &&
                    r > g &&
                    r > b &&
                    r - g >= 15 &&
                    r - b >= 15 &&
                    r < 240;
                isSkinMap[sy * sw + sx] = isSkin ? 1 : 0;
            }
        }
        // Sobel edges
        var edges = new Uint8Array(sw * sh);
        for (var ey = 1; ey < sh - 1; ey++) {
            for (var ex = 1; ex < sw - 1; ex++) {
                var idx = ey * sw + ex;
                var gx =
                    -gray[idx - sw - 1] +
                    gray[idx - sw + 1] -
                    2 * gray[idx - 1] +
                    2 * gray[idx + 1] -
                    gray[idx + sw - 1] +
                    gray[idx + sw + 1];
                var gy =
                    -gray[idx - sw - 1] -
                    2 * gray[idx - sw] -
                    gray[idx - sw + 1] +
                    gray[idx + sw - 1] +
                    2 * gray[idx + sw] +
                    gray[idx + sw + 1];
                edges[idx] = Math.min(255, Math.abs(gx) + Math.abs(gy));
            }
        }
        // Paper = very bright AND low color saturation AND NOT skin
        var cellSize = 6;
        var gcols = Math.floor(sw / cellSize);
        var grows = Math.floor(sh / cellSize);
        var paperGrid = [];
        for (var gr = 0; gr < grows; gr++) {
            paperGrid[gr] = [];
            for (var gc = 0; gc < gcols; gc++) {
                var bSum = 0,
                    bCnt = 0,
                    skinCnt = 0,
                    satSum = 0;
                for (var cdy = 0; cdy < cellSize; cdy++) {
                    for (var cdx = 0; cdx < cellSize; cdx++) {
                        var ci =
                            (gr * cellSize + cdy) * sw + (gc * cellSize + cdx);
                        if (ci < gray.length) {
                            bSum += gray[ci];
                            skinCnt += isSkinMap[ci];
                            // Check saturation: paper is low saturation
                            var pi =
                                ((gr * cellSize + cdy) * step * w +
                                    (gc * cellSize + cdx) * step) *
                                4;
                            if (pi + 2 < data.length) {
                                var pr = data[pi],
                                    pg = data[pi + 1],
                                    pb = data[pi + 2];
                                var pmax = Math.max(pr, pg, pb),
                                    pmin = Math.min(pr, pg, pb);
                                satSum += pmax > 0 ? (pmax - pmin) / pmax : 0;
                            }
                            bCnt++;
                        }
                    }
                }
                var cellBright = bCnt > 0 ? bSum / bCnt : 0;
                var cellSkinRatio = bCnt > 0 ? skinCnt / bCnt : 0;
                var cellSat = bCnt > 0 ? satSum / bCnt : 0;
                // Paper: bright (>190), low saturation (<0.2), NOT skin-colored (>80% not skin)
                paperGrid[gr][gc] =
                    cellBright > 190 && cellSat < 0.2 && cellSkinRatio < 0.2
                        ? 1
                        : 0;
            }
        }
        // Flood fill paper regions
        var bVisited = [];
        for (var bvr = 0; bvr < grows; bvr++) {
            bVisited[bvr] = [];
            for (var bvc = 0; bvc < gcols; bvc++) bVisited[bvr][bvc] = false;
        }
        var docs = [];
        for (var dr = 0; dr < grows; dr++) {
            for (var dc = 0; dc < gcols; dc++) {
                if (paperGrid[dr][dc] === 1 && !bVisited[dr][dc]) {
                    var dQ = [{ r: dr, c: dc }];
                    bVisited[dr][dc] = true;
                    var dMinR = dr,
                        dMaxR = dr,
                        dMinC = dc,
                        dMaxC = dc,
                        dSize = 0;
                    while (dQ.length > 0) {
                        var dCur = dQ.shift();
                        dSize++;
                        if (dCur.r < dMinR) dMinR = dCur.r;
                        if (dCur.r > dMaxR) dMaxR = dCur.r;
                        if (dCur.c < dMinC) dMinC = dCur.c;
                        if (dCur.c > dMaxC) dMaxC = dCur.c;
                        var dDirs = [
                            [-1, 0],
                            [1, 0],
                            [0, -1],
                            [0, 1],
                        ];
                        for (var dd = 0; dd < dDirs.length; dd++) {
                            var dnr = dCur.r + dDirs[dd][0],
                                dnc = dCur.c + dDirs[dd][1];
                            if (
                                dnr >= 0 &&
                                dnr < grows &&
                                dnc >= 0 &&
                                dnc < gcols &&
                                paperGrid[dnr][dnc] === 1 &&
                                !bVisited[dnr][dnc]
                            ) {
                                bVisited[dnr][dnc] = true;
                                dQ.push({ r: dnr, c: dnc });
                            }
                        }
                    }
                    var dRw = (dMaxC - dMinC + 1) * cellSize * step;
                    var dRh = (dMaxR - dMinR + 1) * cellSize * step;
                    var dAspect = dRw / Math.max(dRh, 1);
                    var dBBArea = (dMaxC - dMinC + 1) * (dMaxR - dMinR + 1);
                    var dFill = dSize / Math.max(dBBArea, 1);
                    // Document must be: reasonable size, rectangular, well-filled
                    if (
                        dSize >= 6 &&
                        dRw > w * 0.06 &&
                        dRh > h * 0.04 &&
                        dRw < w * 0.65 &&
                        dRh < h * 0.65 &&
                        dAspect > 0.4 &&
                        dAspect < 3.5 &&
                        dFill > 0.35
                    ) {
                        var pad2 = Math.floor(cellSize * step * 0.3);
                        var docBox = {
                            x: Math.max(0, dMinC * cellSize * step - pad2),
                            y: Math.max(0, dMinR * cellSize * step - pad2),
                            width: Math.min(w, dRw + pad2 * 2),
                            height: Math.min(h, dRh + pad2 * 2),
                            size: dSize,
                            fill: dFill,
                        };
                        // CHECK: must NOT overlap significantly with any face box
                        var overlapsWithFace = false;
                        if (faceBoxes && faceBoxes.length > 0) {
                            for (var ofi = 0; ofi < faceBoxes.length; ofi++) {
                                var fb = faceBoxes[ofi];
                                // Compute overlap area
                                var ox1 = Math.max(docBox.x, fb.x);
                                var oy1 = Math.max(docBox.y, fb.y);
                                var ox2 = Math.min(
                                    docBox.x + docBox.width,
                                    fb.x + fb.w,
                                );
                                var oy2 = Math.min(
                                    docBox.y + docBox.height,
                                    fb.y + fb.h,
                                );
                                if (ox1 < ox2 && oy1 < oy2) {
                                    var overlapArea = (ox2 - ox1) * (oy2 - oy1);
                                    var docArea = docBox.width * docBox.height;
                                    var faceArea = fb.w * fb.h;
                                    // If >20% of doc overlaps with face, reject
                                    if (
                                        overlapArea / docArea > 0.2 ||
                                        overlapArea / faceArea > 0.2
                                    ) {
                                        overlapsWithFace = true;
                                        break;
                                    }
                                }
                            }
                        }
                        if (!overlapsWithFace) {
                            docs.push(docBox);
                        }
                    }
                }
            }
        }
        docs.sort(function (a, b) {
            return b.fill * b.size - a.fill * a.size;
        });
        return docs.slice(0, 2);
    }

    function drawDetectionBox(ctx, box, color, label, isVerified) {
        var x = Math.round(box.x),
            y = Math.round(box.y);
        var bw = Math.round(box.w),
            bh = Math.round(box.h);
        if (bw < 8 || bh < 8) return; // Skip tiny boxes
        var cornerLen = Math.min(24, bw * 0.3, bh * 0.3);
        // Draw full rectangle border (thin)
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.5;
        ctx.globalAlpha = 0.4;
        ctx.strokeRect(x, y, bw, bh);
        ctx.globalAlpha = 1;
        // Draw bold corner brackets
        ctx.strokeStyle = color;
        ctx.lineWidth = 4;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
        // Top-left
        ctx.beginPath();
        ctx.moveTo(x, y + cornerLen);
        ctx.lineTo(x, y);
        ctx.lineTo(x + cornerLen, y);
        ctx.stroke();
        // Top-right
        ctx.beginPath();
        ctx.moveTo(x + bw - cornerLen, y);
        ctx.lineTo(x + bw, y);
        ctx.lineTo(x + bw, y + cornerLen);
        ctx.stroke();
        // Bottom-left
        ctx.beginPath();
        ctx.moveTo(x, y + bh - cornerLen);
        ctx.lineTo(x, y + bh);
        ctx.lineTo(x + cornerLen, y + bh);
        ctx.stroke();
        // Bottom-right
        ctx.beginPath();
        ctx.moveTo(x + bw - cornerLen, y + bh);
        ctx.lineTo(x + bw, y + bh);
        ctx.lineTo(x + bw, y + bh - cornerLen);
        ctx.stroke();
        // Label badge
        if (label) {
            var icon = isVerified ? "\u2713 " : "";
            var text = icon + label;
            ctx.font = "bold 11px 'Poppins', sans-serif";
            var textW = ctx.measureText(text).width;
            var badgeY = y > 22 ? y - 22 : y + bh + 2;
            ctx.fillStyle = color;
            ctx.globalAlpha = 0.9;
            var radius = 4;
            var bx = x,
                by = badgeY,
                bbw = textW + 12,
                bbh = 20;
            ctx.beginPath();
            ctx.moveTo(bx + radius, by);
            ctx.lineTo(bx + bbw - radius, by);
            ctx.quadraticCurveTo(bx + bbw, by, bx + bbw, by + radius);
            ctx.lineTo(bx + bbw, by + bbh - radius);
            ctx.quadraticCurveTo(
                bx + bbw,
                by + bbh,
                bx + bbw - radius,
                by + bbh,
            );
            ctx.lineTo(bx + radius, by + bbh);
            ctx.quadraticCurveTo(bx, by + bbh, bx, by + bbh - radius);
            ctx.lineTo(bx, by + radius);
            ctx.quadraticCurveTo(bx, by, bx + radius, by);
            ctx.fill();
            ctx.globalAlpha = 1;
            ctx.fillStyle = "#fff";
            ctx.fillText(text, x + 6, badgeY + 14);
        }
    }

    async function detectionLoop(video, overlay, isFront, mode) {
        if (!video.srcObject || video.paused || video.ended) return;
        var now = Date.now();
        var ctx = overlay.getContext("2d");
        // Sync overlay size to video display size
        if (video.videoWidth > 0 && video.videoHeight > 0) {
            var dw = video.clientWidth;
            var dh = video.clientHeight;
            if (overlay.width !== dw) overlay.width = dw;
            if (overlay.height !== dh) overlay.height = dh;
        }
        // Run detection at throttled interval
        if (now - lastDetectTime > DETECT_INTERVAL && video.videoWidth > 0) {
            lastDetectTime = now;
            // Capture liveness frame for selfie mode (anti-spoofing)
            if (mode === "selfie") {
                captureLivenessFrame(video);
            }
            var scaleX = overlay.width / video.videoWidth;
            var scaleY = overlay.height / video.videoHeight;
            // --- Face Detection (multi-face) ---
            var newFaces = [];
            var nativeFaceCount = 0;
            if (faceDetectorSupported && faceDetector) {
                try {
                    var faces = await faceDetector.detect(video);
                    faces.forEach(function (face) {
                        var bx = face.boundingBox.x * scaleX;
                        var by = face.boundingBox.y * scaleY;
                        var bw = face.boundingBox.width * scaleX;
                        var bh = face.boundingBox.height * scaleY;
                        if (isFront) bx = overlay.width - bx - bw;
                        newFaces.push({ x: bx, y: by, w: bw, h: bh });
                    });
                    nativeFaceCount = faces.length;
                } catch (e) {
                    /* ignore */
                }
            }
            // Skin fallback for overlay bounding boxes when native found nothing
            // NOTE: This is just a visual hint. Actual selfie CAPTURE still requires native API.
            var tmpC = document.createElement("canvas");
            tmpC.width = video.videoWidth;
            tmpC.height = video.videoHeight;
            tmpC.getContext("2d").drawImage(video, 0, 0);
            if (newFaces.length === 0) {
                var faceRegions = detectFaceRegions(tmpC);
                faceRegions.forEach(function (region) {
                    var bx = region.x * scaleX;
                    var by = region.y * scaleY;
                    var bw = region.width * scaleX;
                    var bh = region.height * scaleY;
                    if (isFront) bx = overlay.width - bx - bw;
                    newFaces.push({ x: bx, y: by, w: bw, h: bh });
                });
            }
            // Smooth face boxes (reduce jitter)
            smoothFaceBoxes = matchAndSmooth(smoothFaceBoxes, newFaces);
            currentFaceBoxes = smoothFaceBoxes;

            // --- Document Detection (penerimaan only) ---
            // Pass face boxes (in video coords) so doc detection can exclude face overlap
            var newDocs = [];
            if (mode === "penerimaan") {
                // Convert current face boxes to video-pixel coords for overlap check
                var faceBoxesInVideoCoords = newFaces.map(function (fb) {
                    var fbx = fb.x / scaleX;
                    if (isFront) fbx = video.videoWidth - fbx - fb.w / scaleX;
                    return {
                        x: fbx,
                        y: fb.y / scaleY,
                        w: fb.w / scaleX,
                        h: fb.h / scaleY,
                    };
                });
                var docRegions = detectDocumentRegions(
                    tmpC,
                    faceBoxesInVideoCoords,
                );
                docRegions.forEach(function (doc) {
                    var dx2 = doc.x * scaleX;
                    var dy2 = doc.y * scaleY;
                    var dw2 = doc.width * scaleX;
                    var dh2 = doc.height * scaleY;
                    if (isFront) dx2 = overlay.width - dx2 - dw2;
                    newDocs.push({ x: dx2, y: dy2, w: dw2, h: dh2 });
                });
            }
            smoothDocBoxes = matchAndSmooth(smoothDocBoxes, newDocs);
            currentDocBoxes = smoothDocBoxes;
        }
        // --- Draw overlay with verification status ---
        ctx.clearRect(0, 0, overlay.width, overlay.height);
        var expectedFaces = mode === "selfie" ? 1 : 2;
        var faceOk = currentFaceBoxes.length === expectedFaces;
        var docOk = mode === "penerimaan" ? currentDocBoxes.length >= 1 : true;
        for (var fi = 0; fi < currentFaceBoxes.length; fi++) {
            var fColor = faceOk
                ? "#0F9455"
                : currentFaceBoxes.length > expectedFaces
                  ? "#F59E0B"
                  : "#EF4444";
            drawDetectionBox(
                ctx,
                currentFaceBoxes[fi],
                fColor,
                "Wajah" + (currentFaceBoxes.length > 1 ? " " + (fi + 1) : ""),
                faceOk,
            );
        }
        for (var di = 0; di < currentDocBoxes.length; di++) {
            var dColor = docOk ? "#2196F3" : "#EF4444";
            drawDetectionBox(
                ctx,
                currentDocBoxes[di],
                dColor,
                "Berkas" + (currentDocBoxes.length > 1 ? " " + (di + 1) : ""),
                docOk,
            );
        }
        // Status indicator text at bottom
        if (overlay.width > 0) {
            // Dynamic font size based on overlay width
            var fontSize = Math.max(
                9,
                Math.min(12, Math.floor(overlay.width / 28)),
            );
            ctx.font = "bold " + fontSize + "px 'Poppins', sans-serif";
            var statusText = "";
            if (mode === "selfie") {
                if (currentFaceBoxes.length === 0)
                    statusText = "\u26A0 Arahkan wajah ke kamera";
                else if (currentFaceBoxes.length === 1)
                    statusText = "\u2713 1 wajah — siap foto";
                else
                    statusText =
                        "\u2717 " +
                        currentFaceBoxes.length +
                        " wajah — harus 1 saja";
            } else {
                var parts = [];
                if (currentFaceBoxes.length < 2)
                    parts.push("+" + (2 - currentFaceBoxes.length) + " wajah");
                if (currentDocBoxes.length < 1) parts.push("arahkan berkas");
                if (
                    currentFaceBoxes.length >= 2 &&
                    currentDocBoxes.length >= 1
                ) {
                    statusText =
                        "\u2713 " +
                        currentFaceBoxes.length +
                        " wajah + " +
                        currentDocBoxes.length +
                        " berkas — siap";
                } else {
                    statusText = "\u26A0 " + parts.join(", ");
                }
            }
            // Override status text with countdown
            if (autoCaptureActive && autoCaptureCountdown > 0) {
                statusText =
                    "\uD83D\uDCF8 Foto dalam " +
                    autoCaptureCountdown +
                    " dtk...";
            }
            var stW = ctx.measureText(statusText).width;
            // Clamp to overlay width with padding
            var maxW = overlay.width - 16;
            if (stW > maxW) stW = maxW;
            var stX = (overlay.width - stW - 16) / 2;
            var stY = overlay.height - 10;
            var allOk =
                mode === "selfie"
                    ? currentFaceBoxes.length === 1
                    : currentFaceBoxes.length >= 2 &&
                      currentDocBoxes.length >= 1;
            ctx.fillStyle = allOk ? "rgba(15,148,85,0.85)" : "rgba(0,0,0,0.6)";
            ctx.beginPath();
            var sr = 6;
            ctx.moveTo(stX + sr, stY - 20);
            ctx.lineTo(stX + stW + 16 - sr, stY - 20);
            ctx.quadraticCurveTo(
                stX + stW + 16,
                stY - 20,
                stX + stW + 16,
                stY - 20 + sr,
            );
            ctx.lineTo(stX + stW + 16, stY + 4 - sr);
            ctx.quadraticCurveTo(
                stX + stW + 16,
                stY + 4,
                stX + stW + 16 - sr,
                stY + 4,
            );
            ctx.lineTo(stX + sr, stY + 4);
            ctx.quadraticCurveTo(stX, stY + 4, stX, stY + 4 - sr);
            ctx.lineTo(stX, stY - 20 + sr);
            ctx.quadraticCurveTo(stX, stY - 20, stX + sr, stY - 20);
            ctx.fill();
            ctx.fillStyle = "#fff";
            ctx.fillText(statusText, stX + 8, stY - 3);
        }

        // ===== AUTO-CAPTURE LOGIC =====
        var isVerified = faceOk && docOk;
        if (isVerified && !autoCaptureActive) {
            startAutoCapture(mode);
        } else if (!isVerified && autoCaptureActive) {
            cancelAutoCapture();
        }

        // Draw countdown overlay when active
        if (autoCaptureActive && autoCaptureCountdown > 0) {
            // Dim overlay
            ctx.fillStyle = "rgba(0, 0, 0, 0.25)";
            ctx.fillRect(0, 0, overlay.width, overlay.height);
            // Center circle
            var cx = overlay.width / 2;
            var cy = overlay.height / 2 - 10;
            var cr = Math.min(50, overlay.width * 0.12);
            // Background circle
            ctx.beginPath();
            ctx.arc(cx, cy, cr, 0, Math.PI * 2);
            ctx.fillStyle = "rgba(15, 148, 85, 0.92)";
            ctx.fill();
            // Arc progress ring (clockwise drain)
            ctx.beginPath();
            var progress = autoCaptureCountdown / 3;
            ctx.arc(
                cx,
                cy,
                cr + 4,
                -Math.PI / 2,
                -Math.PI / 2 + Math.PI * 2 * progress,
            );
            ctx.strokeStyle = "#fff";
            ctx.lineWidth = 4;
            ctx.lineCap = "round";
            ctx.stroke();
            // Countdown number
            ctx.font =
                "bold " + Math.round(cr * 0.9) + "px 'Poppins', sans-serif";
            ctx.fillStyle = "#fff";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(autoCaptureCountdown.toString(), cx, cy);
            // Reset text alignment
            ctx.textAlign = "start";
            ctx.textBaseline = "alphabetic";
        }

        detectionRAF = requestAnimationFrame(function () {
            detectionLoop(video, overlay, isFront, mode);
        });
    }

    function stopDetectionLoop() {
        cancelAutoCapture();
        if (detectionRAF) {
            cancelAnimationFrame(detectionRAF);
            detectionRAF = null;
        }
        currentFaceBoxes = [];
        currentDocBoxes = [];
        smoothFaceBoxes = [];
        smoothDocBoxes = [];
    }

    function restartDetectionLoop(boxEl, stream, mode) {
        stopDetectionLoop();
        var video = boxEl.querySelector("video");
        var overlay = boxEl.querySelector(".detection-overlay");
        if (video && overlay && stream) {
            var isFront = isActuallyFrontCamera(stream);
            detectionLoop(video, overlay, isFront, mode);
        }
    }

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

    function showToast(message, type) {
        let toast = document.getElementById("formToast");
        if (!toast) {
            toast = document.createElement("div");
            toast.id = "formToast";
            toast.className = "toast-notification";
            document.body.appendChild(toast);
        }
        // Reset type classes
        toast.classList.remove("toast-success", "toast-warning", "toast-info");
        if (type === "success") toast.classList.add("toast-success");
        else if (type === "warning") toast.classList.add("toast-warning");
        else if (type === "info") toast.classList.add("toast-info");
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

    // ===== KEPERLUAN-BASED CONDITIONAL REQUIREMENT =====
    function isKeperluanBerkas() {
        var val = (
            document.getElementById("keperluan").value || ""
        ).toLowerCase();
        return (
            val.includes("berkas") ||
            val.includes("surat") ||
            val.includes("dokumen") ||
            val.includes("legalisir")
        );
    }

    function updatePenerimaanLabel() {
        var label = document.querySelector(".foto-penerimaan-section label");
        if (label) {
            if (isKeperluanBerkas()) {
                label.innerHTML =
                    'Foto Penerimaan Berkas <span class="required">*</span>';
            } else {
                label.innerHTML = "Foto Penerimaan Berkas";
            }
        }
    }

    // Camera facing mode per section
    let selfieFacing = "user"; // front by default for selfie
    let penerimaanFacing = "environment"; // back by default for penerimaan

    function stopActiveCamera() {
        stopDetectionLoop();
        if (activeCameraId === "selfie" && selfieLocalStream) {
            selfieLocalStream.getTracks().forEach((t) => t.stop());
            selfieLocalStream = null;
            activeCameraId = null;
            fotoSelfieBox.innerHTML =
                '<div class="camera-icon"><i class="fa-solid fa-camera"></i></div><p>Tekan tombol dibawah untuk<br>mengambil foto selfie.</p>';
            btnCameraSelfie.textContent = "Mulai Kamera";
            btnFlipSelfie.style.display = "none";
        }
        if (activeCameraId === "penerimaan" && penerimaanLocalStream) {
            penerimaanLocalStream.getTracks().forEach((t) => t.stop());
            penerimaanLocalStream = null;
            activeCameraId = null;
            fotoPenerimaanBox.innerHTML =
                '<div class="camera-icon"><i class="fa-solid fa-handshake"></i></div><p>Foto bersama resepsionis<br>saat penerimaan berkas.</p>';
            btnCameraPenerimaan.textContent = "Mulai Kamera";
            btnFlipPenerimaan.style.display = "none";
        }
    }

    // --- FOTO SELFIE ---
    let selfieLocalStream = null;
    let selfieCameraActive = false;

    function startSelfieCamera() {
        resetLivenessFrames();
        startCamera(selfieFacing)
            .then(function (mediaStream) {
                selfieLocalStream = mediaStream;
                selfieCameraActive = true;
                activeCameraId = "selfie";

                var actuallyFront = isActuallyFrontCamera(mediaStream);
                fotoSelfieBox.innerHTML = "";
                var cam = createVideoElement(mediaStream, actuallyFront);
                fotoSelfieBox.appendChild(cam.wrapper);

                btnCameraSelfie.textContent = "Ambil Foto";
                btnFlipSelfie.style.display = "flex";

                // Start live detection overlay
                cam.video.addEventListener("loadeddata", function () {
                    detectionLoop(
                        cam.video,
                        cam.overlay,
                        actuallyFront,
                        "selfie",
                    );
                });
            })
            .catch(function (err) {
                showToast(
                    '<i class="fa-solid fa-circle-exclamation"></i> Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.',
                );
                console.error(err);
            });
    }

    btnCameraSelfie.addEventListener("click", async function () {
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
            startSelfieCamera();
        } else {
            // Capture
            stopDetectionLoop();
            const video = fotoSelfieBox.querySelector("video");
            var actuallyFront = isActuallyFrontCamera(selfieLocalStream);
            const result = capturePhoto(video, actuallyFront);
            const imageData = result.dataUrl;

            // Face detection — STRICT: must be exactly 1 REAL face (native API)
            const faceResult = await detectFaces(result.canvas, result.canvas);

            // For selfie: REQUIRE native FaceDetector — skin-tone blobs are NOT enough
            if (faceDetectorSupported && !faceResult.native) {
                showToast(
                    '<i class="fa-solid fa-face-frown"></i> Wajah tidak terdeteksi! Pastikan wajah Anda terlihat jelas di kamera.',
                );
                restartDetectionLoop(
                    fotoSelfieBox,
                    selfieLocalStream,
                    "selfie",
                );
                return;
            }

            if (!faceResult.detected || faceResult.count === 0) {
                showToast(
                    '<i class="fa-solid fa-face-frown"></i> Wajah tidak terdeteksi! Pastikan wajah Anda terlihat jelas dan coba lagi.',
                );
                restartDetectionLoop(
                    fotoSelfieBox,
                    selfieLocalStream,
                    "selfie",
                );
                return;
            }
            if (faceResult.count > 1) {
                showToast(
                    '<i class="fa-solid fa-circle-exclamation"></i> Terdeteksi ' +
                        faceResult.count +
                        " wajah! Foto selfie harus 1 orang saja.",
                );
                restartDetectionLoop(
                    fotoSelfieBox,
                    selfieLocalStream,
                    "selfie",
                );
                return;
            }

            // Liveness check — prevent photo-of-photo spoofing
            var liveness = checkLiveness(result.canvas);
            if (!liveness.live) {
                showToast(
                    '<i class="fa-solid fa-user-shield"></i> ' +
                        liveness.reason,
                    "warning",
                );
                resetLivenessFrames();
                restartDetectionLoop(
                    fotoSelfieBox,
                    selfieLocalStream,
                    "selfie",
                );
                return;
            }

            fotoSelfieInput.value = imageData;
            selfieLocalStream.getTracks().forEach((t) => t.stop());
            selfieLocalStream = null;
            selfieCameraActive = false;
            activeCameraId = null;
            selfieDone = true;
            btnFlipSelfie.style.display = "none";

            fotoSelfieBox.innerHTML = "";
            const imgEl = document.createElement("img");
            imgEl.src = imageData;
            imgEl.style.width = "100%";
            imgEl.style.borderRadius = "8px";
            fotoSelfieBox.appendChild(imgEl);

            btnCameraSelfie.textContent = "Ulangi Foto";

            showToast(
                '<i class="fa-solid fa-circle-check"></i> Foto selfie tersimpan — wajah terverifikasi!',
                "success",
            );
        }
    });

    // Flip selfie camera
    btnFlipSelfie.addEventListener("click", function () {
        if (!selfieCameraActive) return;
        stopDetectionLoop();
        selfieFacing = selfieFacing === "user" ? "environment" : "user";
        // Stop current stream and restart
        if (selfieLocalStream) {
            selfieLocalStream.getTracks().forEach((t) => t.stop());
            selfieLocalStream = null;
        }
        selfieCameraActive = false;
        activeCameraId = null;
        startSelfieCamera();
    });

    // --- FOTO PENERIMAAN BERKAS ---
    let penerimaanLocalStream = null;
    let penerimaanCameraActive = false;

    function startPenerimaanCamera() {
        startCamera(penerimaanFacing)
            .then(function (mediaStream) {
                penerimaanLocalStream = mediaStream;
                penerimaanCameraActive = true;
                activeCameraId = "penerimaan";

                var actuallyFront = isActuallyFrontCamera(mediaStream);
                fotoPenerimaanBox.innerHTML = "";
                var cam = createVideoElement(mediaStream, actuallyFront);
                fotoPenerimaanBox.appendChild(cam.wrapper);

                btnCameraPenerimaan.textContent = "Ambil Foto";
                btnFlipPenerimaan.style.display = "flex";
                btnClosePenerimaan.style.display = "flex";

                // Start live detection overlay (face + document)
                cam.video.addEventListener("loadeddata", function () {
                    detectionLoop(
                        cam.video,
                        cam.overlay,
                        actuallyFront,
                        "penerimaan",
                    );
                });
            })
            .catch(function (err) {
                showToast(
                    '<i class="fa-solid fa-circle-exclamation"></i> Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.',
                );
                console.error(err);
            });
    }

    btnCameraPenerimaan.addEventListener("click", async function () {
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
            startPenerimaanCamera();
        } else {
            // Capture
            stopDetectionLoop();
            const video = fotoPenerimaanBox.querySelector("video");
            var actuallyFront = isActuallyFrontCamera(penerimaanLocalStream);
            const result = capturePhoto(video, actuallyFront);
            const imageData = result.dataUrl;

            // Face detection — STRICT: must be exactly 2 faces + verify document
            const faceResult = await detectFaces(result.canvas, result.canvas);

            if (!faceResult.detected || faceResult.count === 0) {
                showToast(
                    '<i class="fa-solid fa-triangle-exclamation"></i> Tidak ada wajah terdeteksi! Pastikan petugas piket & pengunjung terlihat.',
                );
                restartDetectionLoop(
                    fotoPenerimaanBox,
                    penerimaanLocalStream,
                    "penerimaan",
                );
                return;
            }
            if (faceResult.count < 2) {
                showToast(
                    '<i class="fa-solid fa-triangle-exclamation"></i> Hanya ' +
                        faceResult.count +
                        " wajah — harus ada 2 orang (petugas piket & pengunjung)!",
                );
                restartDetectionLoop(
                    fotoPenerimaanBox,
                    penerimaanLocalStream,
                    "penerimaan",
                );
                return;
            }
            if (faceResult.count > 2) {
                showToast(
                    '<i class="fa-solid fa-circle-exclamation"></i> Terdeteksi ' +
                        faceResult.count +
                        " wajah — harus tepat 2 orang saja!",
                );
                restartDetectionLoop(
                    fotoPenerimaanBox,
                    penerimaanLocalStream,
                    "penerimaan",
                );
                return;
            }
            // Check document presence
            var docCheck = detectDocumentRegions(result.canvas);
            if (docCheck.length === 0) {
                showToast(
                    '<i class="fa-solid fa-file-circle-exclamation"></i> Berkas tidak terdeteksi! Pastikan berkas/surat terlihat di foto.',
                );
                restartDetectionLoop(
                    fotoPenerimaanBox,
                    penerimaanLocalStream,
                    "penerimaan",
                );
                return;
            }
            showToast(
                '<i class="fa-solid fa-circle-check"></i> Foto penerimaan tersimpan — 2 wajah + ' +
                    docCheck.length +
                    " berkas terverifikasi!",
                "success",
            );

            fotoPenerimaanInput.value = imageData;
            penerimaanLocalStream.getTracks().forEach((t) => t.stop());
            penerimaanLocalStream = null;
            penerimaanCameraActive = false;
            activeCameraId = null;
            penerimaanDone = true;
            btnFlipPenerimaan.style.display = "none";
            btnClosePenerimaan.style.display = "none";

            fotoPenerimaanBox.innerHTML = "";
            const imgEl = document.createElement("img");
            imgEl.src = imageData;
            imgEl.style.width = "100%";
            imgEl.style.borderRadius = "8px";
            fotoPenerimaanBox.appendChild(imgEl);

            btnCameraPenerimaan.textContent = "Ulangi Foto";
        }
    });

    // Close penerimaan camera (optional — user can skip this photo)
    btnClosePenerimaan.addEventListener("click", function () {
        if (!penerimaanCameraActive) return;
        stopDetectionLoop();
        if (penerimaanLocalStream) {
            penerimaanLocalStream.getTracks().forEach((t) => t.stop());
            penerimaanLocalStream = null;
        }
        penerimaanCameraActive = false;
        activeCameraId = null;
        btnFlipPenerimaan.style.display = "none";
        btnClosePenerimaan.style.display = "none";
        btnCameraPenerimaan.textContent = "Mulai Kamera";
        fotoPenerimaanBox.innerHTML =
            '<div class="camera-icon"><i class="fa-solid fa-handshake"></i></div><p>Foto bersama resepsionis<br>saat penerimaan berkas.</p>';
        showToast(
            '<i class="fa-solid fa-circle-info"></i> Kamera penerimaan berkas ditutup.',
            "info",
        );
    });

    // Flip penerimaan camera
    btnFlipPenerimaan.addEventListener("click", function () {
        if (!penerimaanCameraActive) return;
        stopDetectionLoop();
        penerimaanFacing = penerimaanFacing === "user" ? "environment" : "user";
        if (penerimaanLocalStream) {
            penerimaanLocalStream.getTracks().forEach((t) => t.stop());
            penerimaanLocalStream = null;
        }
        penerimaanCameraActive = false;
        activeCameraId = null;
        startPenerimaanCamera();
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

        // If keperluan is berkas-related, penerimaan is required
        if (isKeperluanBerkas() && !penerimaanDone) {
            showToast(
                '<i class="fa-solid fa-circle-exclamation"></i> Keperluan Anda terkait berkas — ambil foto penerimaan berkas terlebih dahulu!',
                "warning",
            );
            fotoPenerimaanBox.closest(".form-group").classList.add("shake");
            setTimeout(
                () =>
                    fotoPenerimaanBox
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

    // ===== KEPERLUAN AUTOCOMPLETE =====
    const keperluanInput = document.getElementById("keperluan");
    const keperluanList = document.getElementById("keperluan_list");
    let keperluanActiveIdx = -1;

    const keperluanData = [
        "Koordinasi/Konsultasi",
        "Rapat",
        "Menyerahkan Surat/Berkas",
        "Legalisir",
        "Audiensi",
        "Pengambilan Dokumen",
        "Permohonan Izin",
        "Sosialisasi",
        "Pembinaan",
        "Monitoring/Evaluasi",
        "Pelaporan",
        "Pengaduan",
        "Kunjungan Kerja",
        "Tanda Tangan Dokumen",
        "Verifikasi Data",
        "Permohonan Rekomendasi",
        "Asistensi",
        "Pendataan",
        "Kerja Sama/MoU",
        "Undangan/Acara Resmi",
        "Lainnya",
    ];

    function renderKeperluan(filter) {
        const query = (filter || "").toLowerCase();
        const filtered = query
            ? keperluanData.filter(function (k) {
                  return k.toLowerCase().includes(query);
              })
            : keperluanData;

        keperluanList.innerHTML = "";
        keperluanActiveIdx = -1;

        if (filtered.length === 0) {
            keperluanList.classList.remove("show");
            return;
        }

        filtered.forEach(function (name) {
            const div = document.createElement("div");
            div.className = "autocomplete-item";
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
                keperluanInput.value = name;
                keperluanList.classList.remove("show");
                updatePenerimaanLabel();
            });
            keperluanList.appendChild(div);
        });
        keperluanList.classList.add("show");
    }

    keperluanInput.addEventListener("focus", function () {
        renderKeperluan(this.value);
    });

    keperluanInput.addEventListener("input", function () {
        renderKeperluan(this.value);
        updatePenerimaanLabel();
    });

    keperluanInput.addEventListener("blur", function () {
        setTimeout(function () {
            keperluanList.classList.remove("show");
        }, 150);
    });

    keperluanInput.addEventListener("keydown", function (e) {
        const items = keperluanList.querySelectorAll(".autocomplete-item");
        if (e.key === "ArrowDown") {
            e.preventDefault();
            keperluanActiveIdx = Math.min(
                keperluanActiveIdx + 1,
                items.length - 1,
            );
            items.forEach(function (el, i) {
                el.classList.toggle("active", i === keperluanActiveIdx);
            });
            if (items[keperluanActiveIdx])
                items[keperluanActiveIdx].scrollIntoView({ block: "nearest" });
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            keperluanActiveIdx = Math.max(keperluanActiveIdx - 1, 0);
            items.forEach(function (el, i) {
                el.classList.toggle("active", i === keperluanActiveIdx);
            });
            if (items[keperluanActiveIdx])
                items[keperluanActiveIdx].scrollIntoView({ block: "nearest" });
        } else if (e.key === "Enter") {
            e.preventDefault();
            if (keperluanActiveIdx >= 0 && items[keperluanActiveIdx]) {
                keperluanInput.value = items[keperluanActiveIdx].dataset.value;
                keperluanList.classList.remove("show");
                updatePenerimaanLabel();
            }
        } else if (e.key === "Escape") {
            keperluanList.classList.remove("show");
        }
    });

    // Close keperluan dropdown when clicking outside
    document.addEventListener("click", function (e) {
        if (
            !keperluanInput.contains(e.target) &&
            !keperluanList.contains(e.target)
        ) {
            keperluanList.classList.remove("show");
        }
    });

    // ===== FORM SUBMIT VALIDATION =====
    const bukuTamuForm = document.getElementById("bukuTamuForm");
    if (bukuTamuForm) {
        bukuTamuForm.addEventListener("submit", function (e) {
            // Check NIK validity (no repeated digits more than 3 times)
            const selectedId = jenisIdHidden.value;
            const config = idConfig[selectedId] || idConfig[""];
            if (
                config.digits &&
                nikInput.value &&
                hasRepeatedDigits(nikInput.value)
            ) {
                e.preventDefault();
                showToast(
                    '<i class="fa-solid fa-circle-exclamation"></i> NIK tidak valid! Angka tidak boleh sama lebih dari 3 digit berturut-turut.',
                    "error",
                );
                nikInput.closest(".form-group").classList.add("shake");
                setTimeout(
                    () =>
                        nikInput
                            .closest(".form-group")
                            .classList.remove("shake"),
                    600,
                );
                nikInput.focus();
                return;
            }

            // Check NIK validity (no sequential digits more than 2)
            if (
                config.digits &&
                nikInput.value &&
                hasSequentialDigits(nikInput.value)
            ) {
                e.preventDefault();
                showToast(
                    '<i class="fa-solid fa-circle-exclamation"></i> NIK tidak valid! Angka tidak boleh berurutan lebih dari 2 digit.',
                    "error",
                );
                nikInput.closest(".form-group").classList.add("shake");
                setTimeout(
                    () =>
                        nikInput
                            .closest(".form-group")
                            .classList.remove("shake"),
                    600,
                );
                nikInput.focus();
                return;
            }

            // Check selfie is taken
            if (!fotoSelfieInput.value) {
                e.preventDefault();
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
            // Check penerimaan berkas if keperluan is berkas-related
            if (isKeperluanBerkas() && !fotoPenerimaanInput.value) {
                e.preventDefault();
                showToast(
                    '<i class="fa-solid fa-circle-exclamation"></i> Keperluan Anda terkait berkas — foto penerimaan berkas wajib diambil!',
                    "warning",
                );
                fotoPenerimaanBox.closest(".form-group").classList.add("shake");
                setTimeout(
                    () =>
                        fotoPenerimaanBox
                            .closest(".form-group")
                            .classList.remove("shake"),
                    600,
                );
                return;
            }
            // Check tanda tangan
            if (!ttdInput.value) {
                e.preventDefault();
                showToast(
                    '<i class="fa-solid fa-circle-exclamation"></i> Tanda tangan belum diisi!',
                );
                ttdBox.closest(".form-group").classList.add("shake");
                setTimeout(
                    () =>
                        ttdBox.closest(".form-group").classList.remove("shake"),
                    600,
                );
                return;
            }
        });
    }

    // ===== FLASH MESSAGE AS TOAST =====
    if (window.__flashSuccess) {
        showToast(
            '<i class="fa-solid fa-circle-check"></i> ' + window.__flashSuccess,
            "success",
        );
        delete window.__flashSuccess;
    }
    if (window.__flashError) {
        showToast(
            '<i class="fa-solid fa-circle-exclamation"></i> ' +
                window.__flashError,
        );
        delete window.__flashError;
    }
});
