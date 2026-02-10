<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buku Tamu SMKN 1 Ciamis</title>
    <link rel="icon" href="{{ asset('img/logo-cadisdik.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f0f0;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 30px 50px 20px;
        }

        .wrapper {
            width: 100%;
            max-width: 1400px;
        }

        .header {
            background: #0F9455;
            padding: 35px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            border-radius: 12px 12px 0 0;
        }

        .header img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            background: white;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .header-text {
            text-align: center;
        }

        .header-text h1 {
            color: white;
            font-size: 30px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .header-text p {
            color: white;
            font-size: 14px;
            font-weight: 300;
            opacity: 0.9;
        }

        .form-container {
            background: white;
            padding: 25px 40px 20px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .form-main {
            display: flex;
            gap: 30px;
        }

        .form-left {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .form-left-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 25px;
            align-items: start;
        }

        .form-right {
            display: flex;
            gap: 20px;
            width: 42%;
            flex-shrink: 0;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group label .required {
            color: #ff3b3b;
            margin-left: 2px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper input,
        .input-wrapper select {
            width: 100%;
            padding: 10px 42px 10px 15px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #333;
            background: white;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            outline: none;
            appearance: none;
            cursor: pointer;
        }

        .input-wrapper select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23999' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }

        .input-wrapper input:focus,
        .input-wrapper select:focus {
            border-color: #0F9455;
            box-shadow: 0 0 0 3px rgba(15, 148, 85, 0.1);
        }

        .input-wrapper .input-icon {
            position: absolute;
            right: 14px;
            color: #b0b0b0;
            font-size: 16px;
            pointer-events: none;
        }

        /* Autocomplete Jenis ID */
        .autocomplete-wrapper {
            position: relative;
            width: 100%;
        }
        .autocomplete-wrapper input {
            width: 100%;
            padding: 10px 42px 10px 15px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #333;
            background: white;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            outline: none;
            cursor: text;
        }
        .autocomplete-wrapper input:focus {
            border-color: #0F9455;
            box-shadow: 0 0 0 3px rgba(15, 148, 85, 0.1);
        }
        .autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1.5px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 999;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .autocomplete-list.show {
            display: block;
        }
        .autocomplete-item {
            padding: 10px 15px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #333;
            cursor: pointer;
            transition: background 0.15s;
        }
        .autocomplete-item:hover,
        .autocomplete-item.active {
            background: #0F9455;
            color: white;
        }
        .autocomplete-item:last-child {
            border-radius: 0 0 6px 6px;
        }

        /* Phone Input */
        .phone-wrapper {
            display: flex;
            align-items: center;
            gap: 0;
            width: 100%;
        }
        .phone-prefix {
            flex-shrink: 0;
            padding: 10px 10px 10px 15px;
            background: #f0f0f0;
            border: 1.5px solid #e0e0e0;
            border-right: none;
            border-radius: 8px 0 0 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #333;
            user-select: none;
            line-height: 1.5;
        }
        .phone-wrapper input {
            flex: 1;
            min-width: 0;
            padding: 10px 42px 10px 10px;
            border: 1.5px solid #e0e0e0;
            border-left: none;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #333;
            background: white;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            outline: none;
        }
        .phone-wrapper:focus-within .phone-prefix {
            border-color: #0F9455;
            box-shadow: 0 0 0 3px rgba(15, 148, 85, 0.1);
        }
        .phone-wrapper:focus-within input {
            border-color: #0F9455;
            box-shadow: 0 0 0 3px rgba(15, 148, 85, 0.1);
        }
        .phone-hint {
            font-size: 11px;
            margin-top: 4px;
            font-family: 'Poppins', sans-serif;
            color: #999;
            transition: color 0.2s;
        }
        .phone-hint.valid {
            color: #0F9455;
        }
        .phone-hint.invalid {
            color: #e53935;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: #e53935;
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.4s;
            opacity: 0;
            pointer-events: none;
            text-align: center;
            max-width: 90vw;
        }
        .toast-notification.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        .toast-notification i {
            margin-right: 8px;
        }
        .form-group.shake input,
        .form-group.shake select,
        .form-group.shake .autocomplete-wrapper input,
        .form-group.shake .phone-wrapper input {
            border-color: #e53935 !important;
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-4px); }
            40% { transform: translateX(4px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        /* Foto Diri Section */
        .foto-section {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .foto-box {
            border: 2px dashed #b8dcc8;
            border-radius: 12px;
            background: #f0faf4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 15px;
            flex: 1;
            text-align: center;
            aspect-ratio: 1 / 1;
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
        }

        .foto-box .camera-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #a8dbb8, #7cc99a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .foto-box .camera-icon i {
            font-size: 24px;
            color: white;
        }

        .foto-box p {
            color: #0F9455;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.5;
        }

        .foto-box video,
        .foto-box canvas,
        .foto-box img {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .btn-camera {
            width: 100%;
            padding: 12px;
            background: #0F9455;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s ease;
        }

        .btn-camera:hover {
            background: #0b7a46;
        }

        /* Tanda Tangan Section */
        .ttd-section {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .ttd-box {
            border: 2px dashed #b8dcc8;
            border-radius: 12px;
            background: #f0faf4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 15px;
            flex: 1;
            text-align: center;
            aspect-ratio: 1 / 1;
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
        }

        .ttd-box .pen-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #a8dbb8, #7cc99a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .ttd-box .pen-icon i {
            font-size: 24px;
            color: white;
        }

        .ttd-box p {
            color: #0F9455;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.5;
        }

        .ttd-box canvas {
            border: 1px solid #b8dcc8;
            border-radius: 8px;
            background: white;
            cursor: crosshair;
            max-width: 100%;
        }

        .btn-ttd {
            width: 100%;
            padding: 12px;
            background: #0F9455;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s ease;
        }

        .btn-ttd:hover {
            background: #0b7a46;
        }

        /* Catatan */
        .catatan {
            margin-top: 10px;
            padding-top: 3px;
        }

        .catatan p:first-child {
            font-size: 13px;
            font-weight: 700;
            color: #333;
            font-style: italic;
        }

        .catatan p:last-child {
            font-size: 13px;
            color: #666;
            font-style: italic;
        }

        .catatan .required {
            color: #ff3b3b;
        }

        /* Submit Button */
        .btn-simpan {
            width: 100%;
            padding: 14px;
            background: #0F9455;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s ease;
        }

        .btn-simpan:hover {
            background: #0b7a46;
        }

        /* Floating Barcode Button */
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #0F9455;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(15, 148, 85, 0.4);
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            z-index: 100;
        }

        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(15, 148, 85, 0.5);
        }

        .floating-btn i {
            color: white;
            font-size: 26px;
        }

        /* Barcode Survey Modal */
        .barcode-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 10001;
            align-items: center;
            justify-content: center;
        }
        .barcode-modal-overlay.active {
            display: flex;
        }
        .barcode-modal {
            background: white;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .barcode-modal .close-btn {
            position: absolute;
            top: 12px;
            right: 16px;
            background: none;
            border: none;
            font-size: 22px;
            color: #999;
            cursor: pointer;
            transition: color 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .barcode-modal .close-btn:hover {
            color: #e53935;
        }
        .barcode-modal h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: #0F9455;
            margin-bottom: 6px;
        }
        .barcode-modal p {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #666;
            margin-bottom: 16px;
        }
        .barcode-modal img {
            width: 100%;
            max-width: 280px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        /* Signature Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            text-align: center;
        }

        .modal-content h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
        }

        .modal-content canvas {
            border: 2px solid #b8dcc8;
            border-radius: 8px;
            cursor: crosshair;
            background: white;
            width: 100%;
            height: 250px;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
        }

        .modal-buttons button {
            padding: 10px 30px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-clear {
            background: #e5e7eb;
            color: #333;
        }

        .btn-clear:hover {
            background: #d1d5db;
        }

        .btn-save-ttd {
            background: #0F9455;
            color: white;
        }

        .btn-save-ttd:hover {
            background: #0b7a46;
        }

        .btn-cancel {
            background: #f87171;
            color: white;
        }

        .btn-cancel:hover {
            background: #ef4444;
        }

        /* ===== RESPONSIVE ===== */

        /* ---- LARGE TABLET / SMALL DESKTOP (max 1366px) ---- */
        @media (max-width: 1366px) {
            body {
                padding: 20px 30px 15px;
            }
            .header {
                padding: 22px 30px;
                gap: 18px;
            }
            .header img {
                width: 72px;
                height: 72px;
            }
            .header-text h1 {
                font-size: 24px;
            }
            .form-container {
                padding: 18px 25px 15px;
            }
            .form-main {
                gap: 20px;
            }
            .form-left-grid {
                gap: 10px 18px;
            }
            .form-right {
                width: 38%;
            }
        }

        /* ---- TABLET LANDSCAPE (landscape + 768-1200px) ---- */
        /* Ini mode utama untuk loket: landscape di tablet */
        @media (max-width: 1200px) and (orientation: landscape) {
            body {
                padding: 12px 20px 10px;
                height: 100vh;
                overflow: hidden;
            }
            .header {
                padding: 14px 25px;
                gap: 14px;
                border-radius: 10px 10px 0 0;
            }
            .header img {
                width: 55px;
                height: 55px;
                padding: 7px;
            }
            .header-text h1 {
                font-size: 20px;
            }
            .header-text p {
                font-size: 12px;
            }
            .form-container {
                padding: 14px 20px 12px;
            }
            .form-main {
                gap: 16px;
                flex-direction: row;
            }
            .form-left {
                flex: 1;
            }
            .form-left-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px 14px;
            }
            .form-right {
                width: 36%;
                flex-direction: column;
                gap: 12px;
            }
            .form-group label {
                font-size: 10.5px;
                margin-bottom: 4px;
            }
            .input-wrapper input,
            .input-wrapper select,
            .autocomplete-wrapper input {
                font-size: 12.5px;
                padding: 7px 36px 7px 12px;
            }
            .phone-prefix {
                font-size: 12.5px;
                padding: 7px 6px 7px 10px;
            }
            .phone-wrapper input {
                font-size: 12.5px;
                padding: 7px 36px 7px 6px;
            }
            .phone-hint {
                font-size: 10px;
                margin-top: 2px;
            }
            .input-wrapper .input-icon {
                font-size: 14px;
                right: 10px;
            }
            .foto-box,
            .ttd-box {
                aspect-ratio: 1 / 1;
                padding: 12px 10px;
            }
            .foto-box .camera-icon,
            .ttd-box .pen-icon {
                width: 40px;
                height: 40px;
                margin-bottom: 8px;
            }
            .foto-box .camera-icon i,
            .ttd-box .pen-icon i {
                font-size: 18px;
            }
            .foto-box p,
            .ttd-box p {
                font-size: 10px;
            }
            .btn-camera,
            .btn-ttd {
                padding: 8px;
                font-size: 12px;
                margin-top: 6px;
            }
            .btn-simpan {
                padding: 10px;
                font-size: 13px;
                margin-top: 6px;
            }
            .catatan {
                margin-top: 4px;
                padding-top: 2px;
            }
            .catatan p:first-child,
            .catatan p:last-child {
                font-size: 10.5px;
            }
            .floating-btn {
                width: 45px;
                height: 45px;
                bottom: 15px;
                right: 15px;
            }
            .floating-btn i {
                font-size: 20px;
            }
        }

        /* ---- TABLET PORTRAIT (portrait + 600-1200px) ---- */
        /* Tablet ditegakkan: scroll aktif, form di atas, foto/ttd di bawah */
        @media (max-width: 1200px) and (orientation: portrait) {
            body {
                height: auto;
                min-height: 100vh;
                overflow: auto;
                padding: 15px 20px;
            }
            .wrapper {
                max-width: 100%;
            }
            .header {
                padding: 20px 25px;
                gap: 15px;
            }
            .header img {
                width: 65px;
                height: 65px;
                padding: 8px;
            }
            .header-text h1 {
                font-size: 22px;
            }
            .header-text p {
                font-size: 13px;
            }
            .form-container {
                padding: 18px 22px 15px;
            }
            .form-main {
                flex-direction: column;
                gap: 20px;
            }
            .form-left-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px 20px;
            }
            .form-right {
                width: 100%;
                flex-direction: row;
                gap: 20px;
            }
            .foto-section,
            .ttd-section {
                flex: 1;
            }
            .foto-box,
            .ttd-box {
                aspect-ratio: 4 / 3;
                max-height: 300px;
            }
            .btn-camera,
            .btn-ttd {
                padding: 10px;
                font-size: 13px;
            }
            .btn-simpan {
                padding: 12px;
                font-size: 14px;
            }
            .catatan {
                margin-top: 8px;
            }
        }

        /* ---- MOBILE LANDSCAPE (landscape + max 768px height) ---- */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                padding: 8px 15px;
                height: auto;
                min-height: 100vh;
                overflow: auto;
            }
            .header {
                padding: 10px 20px;
                gap: 12px;
            }
            .header img {
                width: 40px;
                height: 40px;
                padding: 5px;
            }
            .header-text h1 {
                font-size: 16px;
            }
            .header-text p {
                font-size: 10px;
            }
            .form-container {
                padding: 10px 15px;
            }
            .form-left-grid {
                gap: 6px 12px;
            }
            .form-group label {
                font-size: 10px;
                margin-bottom: 3px;
            }
            .input-wrapper input,
            .input-wrapper select,
            .autocomplete-wrapper input {
                font-size: 12px;
                padding: 6px 32px 6px 10px;
            }
            .phone-prefix {
                font-size: 12px;
                padding: 6px 5px 6px 8px;
            }
            .phone-wrapper input {
                font-size: 12px;
                padding: 6px 32px 6px 5px;
            }
        }

        /* ---- MOBILE / SMALL TABLET (max 768px) ---- */
        @media (max-width: 768px) {
            body {
                height: auto;
                min-height: 100vh;
                overflow: auto;
                padding: 10px 12px;
                align-items: flex-start;
            }
            .header {
                padding: 18px 15px;
                flex-direction: column;
                text-align: center;
                gap: 10px;
                border-radius: 10px 10px 0 0;
            }
            .header img {
                width: 58px;
                height: 58px;
                padding: 7px;
            }
            .header-text h1 {
                font-size: 18px;
            }
            .header-text p {
                font-size: 12px;
            }
            .form-container {
                padding: 15px 14px 12px;
                border-radius: 0 0 10px 10px;
            }
            .form-main {
                flex-direction: column;
                gap: 15px;
            }
            .form-left-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .form-right {
                width: 100%;
                flex-direction: column;
                gap: 15px;
            }
            .foto-box,
            .ttd-box {
                aspect-ratio: 4 / 3;
                max-height: 260px;
            }
            .form-group label {
                font-size: 11px;
            }
            .input-wrapper input,
            .input-wrapper select,
            .autocomplete-wrapper input {
                font-size: 13px;
                padding: 9px 38px 9px 12px;
            }
            .phone-prefix {
                font-size: 13px;
                padding: 9px 8px 9px 12px;
            }
            .phone-wrapper input {
                font-size: 13px;
                padding: 9px 38px 9px 8px;
            }
            .btn-camera,
            .btn-ttd {
                padding: 10px;
                font-size: 13px;
            }
            .btn-simpan {
                padding: 12px;
                font-size: 14px;
            }
            .floating-btn {
                width: 50px;
                height: 50px;
                bottom: 20px;
                right: 20px;
            }
            .floating-btn i {
                font-size: 22px;
            }
            .catatan p:first-child,
            .catatan p:last-child {
                font-size: 11px;
            }
            .modal-content {
                padding: 20px 15px;
                width: 95%;
            }
            .barcode-modal {
                padding: 20px 15px;
                width: 95%;
            }
            .barcode-modal img {
                max-width: 220px;
            }
        }

        /* ---- MOBILE PORTRAIT (max 480px) ---- */
        @media (max-width: 480px) {
            body {
                padding: 6px;
            }
            .header {
                padding: 14px 10px;
                gap: 8px;
            }
            .header img {
                width: 48px;
                height: 48px;
                padding: 5px;
            }
            .header-text h1 {
                font-size: 15px;
            }
            .header-text p {
                font-size: 10.5px;
            }
            .form-container {
                padding: 10px 10px;
            }
            .form-left-grid {
                gap: 8px;
            }
            .input-wrapper input,
            .input-wrapper select,
            .autocomplete-wrapper input {
                font-size: 12px;
                padding: 8px 34px 8px 10px;
            }
            .input-wrapper .input-icon {
                font-size: 14px;
                right: 10px;
            }
            .phone-prefix {
                font-size: 12px;
                padding: 8px 5px 8px 8px;
            }
            .phone-wrapper input {
                font-size: 12px;
                padding: 8px 34px 8px 5px;
            }
            .phone-hint {
                font-size: 10px;
            }
            .foto-box,
            .ttd-box {
                aspect-ratio: auto;
                min-height: 180px;
                max-height: 220px;
            }
            .foto-box .camera-icon,
            .ttd-box .pen-icon {
                width: 42px;
                height: 42px;
                margin-bottom: 10px;
            }
            .foto-box .camera-icon i,
            .ttd-box .pen-icon i {
                font-size: 17px;
            }
            .toast-notification {
                font-size: 11px;
                padding: 10px 14px;
                max-width: 96vw;
            }
            .floating-btn {
                width: 44px;
                height: 44px;
                bottom: 12px;
                right: 12px;
            }
            .floating-btn i {
                font-size: 19px;
            }
        }
    </style>
</head>
<body>

    <div class="wrapper">
    <!-- Header -->
    <div class="header">
        <img src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo Cadisdik">
        <div class="header-text">
            <h1>Selamat Datang di Cadisdik XIII</h1>
            <p>Silahkan untuk mengisi buku tamu terlebih dahulu</p>
        </div>
    </div>

    <!-- Form -->
    <div class="form-container">
        <form id="bukuTamuForm" method="POST" action="#">
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
                                    <input type="text" id="jenis_id_input" placeholder="Ketik atau pilih jenis ID..." autocomplete="off" required>
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
                                <input type="text" name="nik" id="nik" placeholder="Pilih jenis ID terlebih dahulu" required>
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
                                    <input type="tel" name="nomor_hp" id="nomor_hp" placeholder="8xx-xxxx-xxxx" required maxlength="15">
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
                                    <input type="text" name="kabupaten_kota" id="kabupaten_kota" placeholder="Ketik nama kabupaten/kota..." autocomplete="off" required>
                                    <div class="autocomplete-list" id="kabkota_list"></div>
                                </div>
                                <i class="fa-solid fa-city input-icon"></i>
                            </div>
                        </div>

                        <!-- Bagian Yang Dituju -->
                        <div class="form-group">
                            <label>Bagian Yang Dituju <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="bagian_dituju" id="bagian_dituju" placeholder="" required>
                                <i class="fa-solid fa-door-open input-icon"></i>
                            </div>
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
                                <input type="text" name="keperluan" id="keperluan" placeholder="" required>
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

                <!-- RIGHT: Foto Diri + Tanda Tangan -->
                <div class="form-right">
                    <!-- Foto Diri -->
                    <div class="form-group foto-section">
                        <label>Foto Diri <span class="required">*</span></label>
                        <div class="foto-box" id="fotoBox">
                            <div class="camera-icon">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <p>Tekan tombol dibawah untuk<br>mengambil foto.</p>
                        </div>
                        <button type="button" class="btn-camera" id="btnCamera">Mulai Kamera</button>
                        <input type="hidden" name="foto" id="fotoInput">
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
            <button type="submit" class="btn-simpan">Simpan</button>
        </form>
    </div>

    </div><!-- /.wrapper -->

    <!-- Floating Barcode Button -->
    <a href="javascript:void(0);" class="floating-btn" id="btnBarcode" title="Survey Kepuasan Masyarakat">
        <i class="fa-solid fa-qrcode"></i>
    </a>

    <!-- Barcode Survey Modal -->
    <div class="barcode-modal-overlay" id="barcodeModal">
        <div class="barcode-modal">
            <button type="button" class="close-btn" id="btnCloseBarcode">&times;</button>
            <h3><i class="fa-solid"></i> Survey Kepuasan Masyarakat</h3>
            <p>Scan barcode di bawah ini untuk mengisi survey</p>
            <img src="{{ asset('img/barcode-skm.png') }}" alt="Barcode Survey Kepuasan Masyarakat">
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

    <script>
        // ===== DYNAMIC JENIS ID (AUTOCOMPLETE) =====
        const jenisIdInput = document.getElementById('jenis_id_input');
        const jenisIdHidden = document.getElementById('jenis_id');
        const jenisIdList = document.getElementById('jenis_id_list');
        const nikLabel = document.getElementById('nik_label');
        const nikInput = document.getElementById('nik');
        const nikIcon = document.getElementById('nik_icon');

        const jenisIdOptions = [
            { value: 'KTP', label: 'KTP' },
            { value: 'SIM', label: 'SIM' },
            { value: 'Passport', label: 'Passport' },
            { value: 'Kartu Pelajar', label: 'Kartu Pelajar' },
            { value: 'Kartu Pers', label: 'Kartu Pers' },
            { value: 'Kartu Pegawai', label: 'Kartu Pegawai / ASN' },
            { value: 'NIP', label: 'NIP' },
            { value: 'KITAS', label: 'KITAS / KITAP' },
            { value: 'Kartu Anggota', label: 'Kartu Anggota' },
            { value: 'Lainnya', label: 'Lainnya' }
        ];

        const idConfig = {
            '':              { label: 'Nomor ID',           placeholder: 'Pilih jenis ID terlebih dahulu', icon: 'fa-address-card', digits: null },
            'KTP':           { label: 'NIK',                placeholder: 'Masukkan 16 digit NIK',          icon: 'fa-id-card',       digits: 16 },
            'SIM':           { label: 'No. SIM',            placeholder: 'Masukkan 12 digit No. SIM',      icon: 'fa-car',           digits: 12 },
            'Passport':      { label: 'No. Passport',       placeholder: 'Masukkan nomor passport',        icon: 'fa-passport',      digits: null },
            'Kartu Pelajar': { label: 'No. Induk Siswa',    placeholder: 'Masukkan NIS / NISN',            icon: 'fa-graduation-cap', digits: null },
            'Kartu Pers':    { label: 'No. Kartu Pers',     placeholder: 'Masukkan nomor kartu pers',      icon: 'fa-newspaper',     digits: null },
            'Kartu Pegawai': { label: 'NIP / No. Pegawai',  placeholder: 'Masukkan NIP atau no. pegawai',  icon: 'fa-user-tie',      digits: null },
            'NIP':           { label: 'NIP',                placeholder: 'Masukkan NIP (18 digit)',        icon: 'fa-user-tie',      digits: 18 },
            'KITAS':         { label: 'No. KITAS/KITAP',    placeholder: 'Masukkan nomor KITAS/KITAP',     icon: 'fa-globe',         digits: null },
            'Kartu Anggota': { label: 'No. Anggota',        placeholder: 'Masukkan nomor kartu anggota',   icon: 'fa-id-badge',      digits: null },
            'Lainnya':       { label: 'Nomor Identitas',    placeholder: 'Masukkan nomor identitas Anda',  icon: 'fa-fingerprint',   digits: null }
        };

        let activeIndex = -1;

        function renderList(filter) {
            const query = (filter || '').toLowerCase();
            const filtered = query
                ? jenisIdOptions.filter(o => o.label.toLowerCase().includes(query) || o.value.toLowerCase().includes(query))
                : jenisIdOptions;

            jenisIdList.innerHTML = '';
            activeIndex = -1;

            if (filtered.length === 0) {
                jenisIdList.classList.remove('show');
                return;
            }

            filtered.forEach((opt, i) => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.textContent = opt.label;
                div.dataset.value = opt.value;
                div.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    selectOption(opt);
                });
                jenisIdList.appendChild(div);
            });
            jenisIdList.classList.add('show');
        }

        function selectOption(opt) {
            jenisIdInput.value = opt.label;
            jenisIdHidden.value = opt.value;
            jenisIdList.classList.remove('show');

            const config = idConfig[opt.value] || idConfig[''];
            nikLabel.innerHTML = config.label + ' <span class="required">*</span>';
            nikInput.placeholder = config.placeholder;
            nikIcon.className = 'fa-solid ' + config.icon + ' input-icon';
            nikInput.value = '';

            // Update hint & maxlength
            const nikHint = document.getElementById('nik_hint');
            if (config.digits) {
                nikInput.maxLength = config.digits;
                nikHint.textContent = 'Wajib ' + config.digits + ' digit';
                nikHint.className = 'phone-hint';
            } else {
                nikInput.removeAttribute('maxlength');
                nikHint.textContent = '';
                nikHint.className = 'phone-hint';
            }

            nikInput.focus();
        }

        jenisIdInput.addEventListener('focus', function() {
            renderList(this.value);
        });

        jenisIdInput.addEventListener('input', function() {
            jenisIdHidden.value = '';
            renderList(this.value);
        });

        jenisIdInput.addEventListener('blur', function() {
            setTimeout(() => {
                jenisIdList.classList.remove('show');
                // If typed text doesn't match any option, clear
                if (!jenisIdHidden.value) {
                    const match = jenisIdOptions.find(o => o.label.toLowerCase() === this.value.toLowerCase());
                    if (match) {
                        selectOption(match);
                    } else {
                        this.value = '';
                        jenisIdHidden.value = '';
                        const config = idConfig[''];
                        nikLabel.innerHTML = config.label + ' <span class="required">*</span>';
                        nikInput.placeholder = config.placeholder;
                        nikIcon.className = 'fa-solid ' + config.icon + ' input-icon';
                    }
                }
            }, 150);
        });

        jenisIdInput.addEventListener('keydown', function(e) {
            const items = jenisIdList.querySelectorAll('.autocomplete-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
                if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
                if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && items[activeIndex]) {
                    const val = items[activeIndex].dataset.value;
                    const opt = jenisIdOptions.find(o => o.value === val);
                    if (opt) selectOption(opt);
                }
            } else if (e.key === 'Escape') {
                jenisIdList.classList.remove('show');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!jenisIdInput.contains(e.target) && !jenisIdList.contains(e.target)) {
                jenisIdList.classList.remove('show');
            }
        });

        // ===== BARCODE SURVEY MODAL =====
        const btnBarcode = document.getElementById('btnBarcode');
        const barcodeModal = document.getElementById('barcodeModal');
        const btnCloseBarcode = document.getElementById('btnCloseBarcode');

        btnBarcode.addEventListener('click', function(e) {
            e.preventDefault();
            barcodeModal.classList.add('active');
        });

        btnCloseBarcode.addEventListener('click', function() {
            barcodeModal.classList.remove('active');
        });

        barcodeModal.addEventListener('click', function(e) {
            if (e.target === barcodeModal) {
                barcodeModal.classList.remove('active');
            }
        });

        // ===== NIK REAL-TIME VALIDATION =====
        nikInput.addEventListener('input', function() {
            const selectedId = jenisIdHidden.value;
            const config = idConfig[selectedId] || idConfig[''];
            const nikHint = document.getElementById('nik_hint');

            if (config.digits) {
                // Only allow digits for types with digit requirement
                this.value = this.value.replace(/[^0-9]/g, '');
                const count = this.value.length;

                if (count === 0) {
                    nikHint.textContent = 'Wajib ' + config.digits + ' digit';
                    nikHint.className = 'phone-hint';
                } else if (count < config.digits) {
                    nikHint.textContent = count + ' digit \u2014 kurang ' + (config.digits - count) + ' digit lagi';
                    nikHint.className = 'phone-hint invalid';
                } else {
                    nikHint.textContent = '\u2713 ' + config.digits + ' digit \u2014 valid';
                    nikHint.className = 'phone-hint valid';
                }
            } else {
                nikHint.textContent = '';
                nikHint.className = 'phone-hint';
            }
        });

        // ===== NOMOR HANDPHONE (+62 AUTO FORMAT) =====
        const phoneInput = document.getElementById('nomor_hp');
        const phoneHint = document.getElementById('phone_hint');
        const MIN_DIGITS = 9;   // min digits after +62 (e.g. 812345678)
        const MAX_DIGITS = 13;  // max digits after +62

        phoneInput.addEventListener('input', function(e) {
            // Strip leading 0 or +62 if user types it
            let raw = this.value.replace(/[^0-9]/g, '');

            // If user typed leading 0, remove it (already have +62)
            if (raw.startsWith('62')) {
                raw = raw.substring(2);
            }
            if (raw.startsWith('0')) {
                raw = raw.substring(1);
            }

            // Limit to max digits
            if (raw.length > MAX_DIGITS) {
                raw = raw.substring(0, MAX_DIGITS);
            }

            // Format: 8xx-xxxx-xxxx
            let formatted = '';
            for (let i = 0; i < raw.length; i++) {
                if (i === 3 || i === 7) formatted += '-';
                formatted += raw[i];
            }
            this.value = formatted;

            // Validation hint
            const digitCount = raw.length;
            if (digitCount === 0) {
                phoneHint.textContent = 'Min. 9 digit, Maks. 13 digit (setelah +62)';
                phoneHint.className = 'phone-hint';
            } else if (digitCount < MIN_DIGITS) {
                phoneHint.textContent = `${digitCount} digit — kurang ${MIN_DIGITS - digitCount} digit lagi`;
                phoneHint.className = 'phone-hint invalid';
            } else if (digitCount >= MIN_DIGITS && digitCount <= MAX_DIGITS) {
                phoneHint.textContent = `✓ ${digitCount} digit — nomor valid`;
                phoneHint.className = 'phone-hint valid';
            }
        });

        // Prevent non-numeric input
        phoneInput.addEventListener('keydown', function(e) {
            // Allow: backspace, delete, tab, escape, enter, arrows
            if ([8, 9, 13, 27, 46, 37, 38, 39, 40].includes(e.keyCode)) return;
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].includes(e.keyCode)) return;
            // Block non-numbers
            if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        // Handle paste
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            let clean = pasted.replace(/[^0-9]/g, '');
            if (clean.startsWith('62')) clean = clean.substring(2);
            if (clean.startsWith('0')) clean = clean.substring(1);
            this.value = clean;
            this.dispatchEvent(new Event('input'));
        });

        // ===== KABUPATEN/KOTA AUTOCOMPLETE (JAWA BARAT) =====
        const kabKotaData = [
            'Kab. Bandung', 'Kab. Bandung Barat', 'Kab. Bekasi', 'Kab. Bogor',
            'Kab. Ciamis', 'Kab. Cianjur', 'Kab. Cirebon', 'Kab. Garut',
            'Kab. Indramayu', 'Kab. Karawang', 'Kab. Kuningan', 'Kab. Majalengka',
            'Kab. Pangandaran', 'Kab. Purwakarta', 'Kab. Subang', 'Kab. Sukabumi',
            'Kab. Sumedang', 'Kab. Tasikmalaya',
            'Kota Bandung', 'Kota Banjar', 'Kota Bekasi', 'Kota Bogor',
            'Kota Cimahi', 'Kota Cirebon', 'Kota Depok', 'Kota Sukabumi',
            'Kota Tasikmalaya'
        ];

        const kabKotaInput = document.getElementById('kabupaten_kota');
        const kabKotaList = document.getElementById('kabkota_list');
        let kabKotaActiveIdx = -1;

        function renderKabKota(filter) {
            const query = (filter || '').toLowerCase();
            const filtered = query
                ? kabKotaData.filter(k => k.toLowerCase().includes(query))
                : kabKotaData;

            kabKotaList.innerHTML = '';
            kabKotaActiveIdx = -1;

            if (filtered.length === 0) {
                kabKotaList.classList.remove('show');
                return;
            }

            filtered.forEach(name => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                // Bold the matching part
                if (query) {
                    const idx = name.toLowerCase().indexOf(query);
                    div.innerHTML = name.substring(0, idx) + '<strong>' + name.substring(idx, idx + query.length) + '</strong>' + name.substring(idx + query.length);
                } else {
                    div.textContent = name;
                }
                div.dataset.value = name;
                div.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    kabKotaInput.value = name;
                    kabKotaList.classList.remove('show');
                });
                kabKotaList.appendChild(div);
            });
            kabKotaList.classList.add('show');
        }

        kabKotaInput.addEventListener('focus', function() {
            renderKabKota(this.value);
        });

        kabKotaInput.addEventListener('input', function() {
            renderKabKota(this.value);
        });

        kabKotaInput.addEventListener('blur', function() {
            setTimeout(() => kabKotaList.classList.remove('show'), 150);
        });

        kabKotaInput.addEventListener('keydown', function(e) {
            const items = kabKotaList.querySelectorAll('.autocomplete-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                kabKotaActiveIdx = Math.min(kabKotaActiveIdx + 1, items.length - 1);
                items.forEach((el, i) => el.classList.toggle('active', i === kabKotaActiveIdx));
                if (items[kabKotaActiveIdx]) items[kabKotaActiveIdx].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                kabKotaActiveIdx = Math.max(kabKotaActiveIdx - 1, 0);
                items.forEach((el, i) => el.classList.toggle('active', i === kabKotaActiveIdx));
                if (items[kabKotaActiveIdx]) items[kabKotaActiveIdx].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (kabKotaActiveIdx >= 0 && items[kabKotaActiveIdx]) {
                    kabKotaInput.value = items[kabKotaActiveIdx].dataset.value;
                    kabKotaList.classList.remove('show');
                }
            } else if (e.key === 'Escape') {
                kabKotaList.classList.remove('show');
            }
        });

        // Close kabkota dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!kabKotaInput.contains(e.target) && !kabKotaList.contains(e.target)) {
                kabKotaList.classList.remove('show');
            }
        });

        // ===== KAMERA / FOTO DIRI =====
        const btnCamera = document.getElementById('btnCamera');
        const fotoBox = document.getElementById('fotoBox');
        const fotoInput = document.getElementById('fotoInput');
        let stream = null;
        let cameraActive = false;

        // ===== FORM VALIDATION HELPER =====
        function validateFormBeforeAction(actionName) {
            const requiredFields = [
                { id: 'jenis_id', label: 'Jenis ID', checkHidden: true, inputId: 'jenis_id_input' },
                { id: 'nik', label: document.getElementById('nik_label').textContent.replace('*','').trim(), validateDigits: true },
                { id: 'nama_lengkap', label: 'Nama Lengkap' },
                { id: 'nomor_hp', label: 'Nomor Handphone', minDigits: 9 },
                { id: 'kabupaten_kota', label: 'Kabupaten/Kota Instansi' },
                { id: 'bagian_dituju', label: 'Bagian Yang Dituju' },
                { id: 'keperluan', label: 'Keperluan' }
            ];

            const emptyFields = [];
            let firstEmptyEl = null;

            requiredFields.forEach(function(f) {
                const el = document.getElementById(f.id);
                let isEmpty = false;

                if (f.checkHidden) {
                    isEmpty = !el.value.trim();
                } else if (f.minDigits) {
                    const digits = el.value.replace(/[^0-9]/g, '');
                    isEmpty = digits.length < f.minDigits;
                } else if (f.validateDigits) {
                    const selectedId = jenisIdHidden.value;
                    const cfg = idConfig[selectedId] || idConfig[''];
                    if (cfg.digits) {
                        const d = el.value.replace(/[^0-9]/g, '');
                        if (d.length !== cfg.digits) {
                            isEmpty = true;
                            f.label = f.label + ' (harus ' + cfg.digits + ' digit)';
                        }
                    } else {
                        isEmpty = !el.value.trim();
                    }
                } else {
                    isEmpty = !el.value.trim();
                }

                if (isEmpty) {
                    emptyFields.push(f.label);
                    const formGroup = (f.inputId ? document.getElementById(f.inputId) : el).closest('.form-group');
                    if (formGroup) {
                        formGroup.classList.add('shake');
                        setTimeout(() => formGroup.classList.remove('shake'), 600);
                    }
                    if (!firstEmptyEl) {
                        firstEmptyEl = f.inputId ? document.getElementById(f.inputId) : el;
                    }
                }
            });

            if (emptyFields.length > 0) {
                showToast('<i class="fa-solid fa-circle-exclamation"></i> Lengkapi form terlebih dahulu sebelum ' + actionName + ': ' + emptyFields.join(', '));
                if (firstEmptyEl) firstEmptyEl.focus();
                return false;
            }
            return true;
        }

        function showToast(message) {
            let toast = document.getElementById('formToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'formToast';
                toast.className = 'toast-notification';
                document.body.appendChild(toast);
            }
            toast.innerHTML = message;
            toast.classList.add('show');
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => toast.classList.remove('show'), 4000);
        }

        btnCamera.addEventListener('click', function() {
            if (!cameraActive && !validateFormBeforeAction('mengambil foto')) return;
            if (!cameraActive) {
                // Start camera
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
                    .then(function(mediaStream) {
                        stream = mediaStream;
                        cameraActive = true;

                        fotoBox.innerHTML = '';
                        const video = document.createElement('video');
                        video.srcObject = mediaStream;
                        video.autoplay = true;
                        video.playsInline = true;
                        video.style.width = '100%';
                        video.style.borderRadius = '8px';
                        fotoBox.appendChild(video);

                        btnCamera.textContent = 'Ambil Foto';
                    })
                    .catch(function(err) {
                        alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
                        console.error(err);
                    });
            } else {
                // Capture photo
                const video = fotoBox.querySelector('video');
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);

                const imageData = canvas.toDataURL('image/png');
                fotoInput.value = imageData;

                // Stop camera
                stream.getTracks().forEach(track => track.stop());
                cameraActive = false;

                // Show captured image
                fotoBox.innerHTML = '';
                const img = document.createElement('img');
                img.src = imageData;
                img.style.width = '100%';
                img.style.borderRadius = '8px';
                fotoBox.appendChild(img);

                btnCamera.textContent = 'Ulangi Foto';
            }
        });

        // ===== TANDA TANGAN =====
        const btnTtd = document.getElementById('btnTtd');
        const ttdModal = document.getElementById('ttdModal');
        const signatureCanvas = document.getElementById('signatureCanvas');
        const ctx = signatureCanvas.getContext('2d');
        const btnClearTtd = document.getElementById('btnClearTtd');
        const btnSaveTtd = document.getElementById('btnSaveTtd');
        const btnCancelTtd = document.getElementById('btnCancelTtd');
        const ttdBox = document.getElementById('ttdBox');
        const ttdInput = document.getElementById('ttdInput');
        let isDrawing = false;

        function resizeCanvas() {
            const rect = signatureCanvas.getBoundingClientRect();
            signatureCanvas.width = rect.width;
            signatureCanvas.height = rect.height;
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        btnTtd.addEventListener('click', function() {
            if (!validateFormBeforeAction('tanda tangan')) return;
            ttdModal.classList.add('active');
            setTimeout(resizeCanvas, 100);
        });

        btnCancelTtd.addEventListener('click', function() {
            ttdModal.classList.remove('active');
        });

        btnClearTtd.addEventListener('click', function() {
            ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        });

        btnSaveTtd.addEventListener('click', function() {
            const imageData = signatureCanvas.toDataURL('image/png');
            ttdInput.value = imageData;

            ttdBox.innerHTML = '';
            const img = document.createElement('img');
            img.src = imageData;
            img.style.width = '100%';
            img.style.borderRadius = '8px';
            ttdBox.appendChild(img);

            ttdModal.classList.remove('active');
            btnTtd.textContent = 'Ulangi Tanda Tangan';
        });

        // Drawing on canvas
        function getPos(e) {
            const rect = signatureCanvas.getBoundingClientRect();
            if (e.touches) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top
                };
            }
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        signatureCanvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        });

        signatureCanvas.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        });

        signatureCanvas.addEventListener('mouseup', () => { isDrawing = false; });
        signatureCanvas.addEventListener('mouseleave', () => { isDrawing = false; });

        // Touch support
        signatureCanvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        });

        signatureCanvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        });

        signatureCanvas.addEventListener('touchend', () => { isDrawing = false; });

        // Close modal on overlay click
        ttdModal.addEventListener('click', function(e) {
            if (e.target === ttdModal) {
                ttdModal.classList.remove('active');
            }
        });
    </script>
</body>
</html>
