{{-- Halaman Admin — Cadisdik XIII --}}
@extends('layouts.main')

@section('title', 'Admin — Cadisdik XIII')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">
@endpush

@section('content')

    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo Cadisdik">
            <div class="header-text">
                <h1>Admin Panel — Cadisdik XIII</h1>
                <p>Kelola data buku tamu dan laporan</p>
            </div>
        </div>

        <!-- Content -->
        <div class="content-container">
            <div class="placeholder-content">
                <i class="fa-solid fa-chart-line placeholder-icon"></i>
                <h2>Dashboard Admin</h2>
                <p>Halaman ini akan digunakan untuk mengelola data buku tamu, melihat laporan, dan statistik pengunjung.</p>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- Script admin akan ditambahkan di sini --}}
@endpush
