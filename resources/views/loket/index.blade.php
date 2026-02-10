{{-- Halaman Loket — Cadisdik XIII --}}
@extends('layouts.main')

@section('title', 'Loket — Cadisdik XIII')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/loket/loket.css') }}">
@endpush

@section('content')

    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo Cadisdik">
            <div class="header-text">
                <h1>Loket Cadisdik XIII</h1>
                <p>Sistem Manajemen Loket Pelayanan</p>
            </div>
        </div>

        <!-- Content -->
        <div class="content-container">
            <div class="placeholder-content">
                <i class="fa-solid fa-window-maximize placeholder-icon"></i>
                <h2>Halaman Loket</h2>
                <p>Halaman ini akan digunakan untuk mengelola antrian dan pelayanan loket.</p>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- Script loket akan ditambahkan di sini --}}
@endpush
