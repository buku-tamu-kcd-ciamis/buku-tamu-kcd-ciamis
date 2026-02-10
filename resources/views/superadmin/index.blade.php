{{-- Halaman Super Admin — Cadisdik XIII --}}
@extends('layouts.main')

@section('title', 'Super Admin — Cadisdik XIII')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/superadmin/superadmin.css') }}">
@endpush

@section('content')

    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo Cadisdik">
            <div class="header-text">
                <h1>Super Admin — Cadisdik XIII</h1>
                <p>Manajemen Sistem dan Pengguna</p>
            </div>
        </div>

        <!-- Content -->
        <div class="content-container">
            <div class="placeholder-content">
                <i class="fa-solid fa-shield-halved placeholder-icon"></i>
                <h2>Dashboard Super Admin</h2>
                <p>Halaman ini akan digunakan untuk manajemen pengguna, pengaturan sistem, dan kontrol akses penuh.</p>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- Script super admin akan ditambahkan di sini --}}
@endpush
