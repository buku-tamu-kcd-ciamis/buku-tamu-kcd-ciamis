@extends('layouts.main')

@section('title', 'Tentang Developer — Cadisdik XIII')

@push('styles')
    <style>
        :root {
            --brand-900: #0b7a46;
            --brand-800: #0f8a50;
            --brand-700: #0f9455;
            --brand-600: #16a867;
            --brand-100: #dff7ea;
            --brand-050: #effcf5;
            --ink-900: #0f172a;
            --ink-700: #334155;
            --ink-500: #64748b;
            --line: #d9e5dd;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            color: var(--ink-900);
            background: linear-gradient(180deg, #eef4f0 0%, #e8efe9 100%);
            padding: 8px;
            overflow: hidden;
        }

        .developer-page {
            width: 100%;
            max-width: 1540px;
            min-height: calc(100vh - 16px);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #c8d7ce;
        }

        .hero {
            position: relative;
            background: linear-gradient(135deg, var(--brand-900) 0%, var(--brand-700) 52%, var(--brand-600) 100%);
            color: #f3fff8;
            padding: 24px 18px 30px;
            text-align: center;
            isolation: isolate;
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 36px 36px;
            opacity: 0.22;
            z-index: -1;
        }

        .hero-back {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 2;
        }

        .hero-back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(223, 247, 234, 0.6);
            color: #f3fff8;
            text-decoration: none;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            background: rgba(239, 252, 245, 0.16);
            backdrop-filter: blur(2px);
        }

        .hero-back-link:hover {
            background: rgba(239, 252, 245, 0.24);
            border-color: rgba(223, 247, 234, 0.85);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(223, 247, 234, 0.55);
            border-radius: 999px;
            background: rgba(239, 252, 245, 0.18);
            color: #e6fff2;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 6px 16px;
            backdrop-filter: blur(2px);
        }

        .hero-title {
            margin: 14px 0 6px;
            font-size: 42px;
            line-height: 1.05;
            font-weight: 700;
            color: #f4fff8;
        }

        .hero-subtitle {
            margin: 0 auto;
            max-width: 860px;
            font-size: 15px;
            line-height: 1.4;
            color: rgba(244, 255, 248, 0.86);
        }

        .content {
            background: #edf1ee;
            padding: 12px 14px 10px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .member-stack {
            max-width: 1480px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            position: relative;
            z-index: 1;
        }

        .member-card {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background:
                linear-gradient(140deg, rgba(255, 255, 255, 0.62) 0%, rgba(255, 255, 255, 0.4) 100%);
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 16px;
            padding: 16px 18px;
            box-shadow: 0 14px 28px rgba(9, 30, 66, 0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .member-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: -30%;
            width: 58%;
            height: 100%;
            background: linear-gradient(110deg, rgba(255, 255, 255, 0.42), rgba(255, 255, 255, 0));
            z-index: -1;
            pointer-events: none;
        }

        .member-card:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.76);
            box-shadow: 0 20px 36px rgba(9, 30, 66, 0.18);
        }

        .member-header {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            text-align: center;
        }

        .member-avatar-link {
            display: inline-flex;
            width: 84px;
            height: 84px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--brand-100);
            flex-shrink: 0;
        }

        .member-meta {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .member-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .member-name {
            margin: 0;
            font-size: 26px;
            line-height: 1.2;
            color: var(--ink-900);
            font-weight: 700;
        }

        .member-role {
            margin: 2px 0 0;
            font-size: 17px;
            color: #0e9458;
            font-weight: 600;
            line-height: 1.3;
        }

        .member-socials {
            margin: 12px 0 4px;
            display: flex;
            flex-wrap: nowrap;
            justify-content: center;
            gap: 6px;
            width: 100%;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .social-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            line-height: 1;
            white-space: nowrap;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
        }

        .social-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.12);
        }

        .social-chip:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
        }

        .social-chip svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
        }

        .social-github {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #111827;
        }

        .social-email {
            background: #e9f9f0;
            border-color: #c9f0d9;
            color: #0a7e49;
        }

        .social-linkedin,
        .social-instagram {
            background: #f2f6f3;
            border-color: #dce6df;
            color: #4b5563;
        }

        .social-linkedin:hover,
        .social-instagram:hover {
            background: #e9f2ec;
            border-color: #cdddd1;
        }

        .member-points {
            margin: 10px 0 0;
            padding-left: 16px;
            display: grid;
            gap: 6px;
            font-size: 14px;
            color: var(--ink-700);
            line-height: 1.45;
        }

        .page-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 4px;
            margin-top: auto;
            padding-top: 10px;
        }

        .copyright {
            margin: 0;
            font-size: 12px;
            color: #8090a4;
            text-align: center;
        }

        .no-data {
            grid-column: 1 / -1;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #ffffff;
            padding: 12px;
            font-size: 13px;
            color: var(--ink-500);
        }

        @media (max-width: 1260px) {
            body {
                overflow: auto;
            }

            .developer-page {
                min-height: unset;
            }

            .member-stack {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .hero {
                padding: 24px 14px 22px;
            }

            .hero-back {
                top: 10px;
                left: 10px;
            }

            .hero-back-link {
                font-size: 12px;
                padding: 6px 10px;
            }

            .hero-title {
                font-size: 34px;
            }

            .hero-subtitle {
                font-size: 13px;
            }

            .member-stack {
                margin-top: 0;
                gap: 12px;
                max-width: 100%;
                grid-template-columns: 1fr;
            }

            .member-avatar-link {
                width: 72px;
                height: 72px;
            }

            .member-name {
                font-size: 18px;
            }

            .member-role {
                font-size: 13px;
            }

            .member-github {
                font-size: 12px;
            }

            .member-socials,
            .member-points {
                margin-left: 0;
            }

            .social-chip {
                font-size: 12px;
                padding: 6px 9px;
            }

            .member-points {
                font-size: 12px;
            }

            .copyright {
                font-size: 13px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $teamMembers = $teamMembers ?? [];
        $projectEmail = 'cadisdik13@disdik.jabarprov.go.id';
    @endphp

    <main class="developer-page">
        <section class="hero">
            <div class="hero-back">
                <a href="{{ route('index') }}" class="hero-back-link">&larr; Kembali</a>
            </div>
            <span class="hero-badge">TIM PENGEMBANG</span>
            <h1 class="hero-title">Cadisdik XIII</h1>
            <p class="hero-subtitle">
                Halaman pengembang aplikasi Buku Tamu Digital Cadisdik Wilayah XIII,
                dirancang untuk layanan administrasi yang lebih cepat dan tertata.
            </p>
        </section>

        <section class="content">
            <div class="member-stack">
            @forelse($teamMembers as $member)
                @php
                    $quickPoints = array_values(array_filter([
                        $member['responsibilities'][0] ?? null,
                        $member['responsibilities'][1] ?? null,
                        $member['contributions'][0] ?? null,
                    ]));
                    $emailAddress = $member['email'] ?? $projectEmail;
                @endphp
                <article class="member-card">
                    <div class="member-header">
                        <div class="member-avatar-link">
                            <img src="{{ $member['avatar_url'] }}" alt="Avatar {{ $member['name'] }}" class="member-avatar"
                                loading="lazy" referrerpolicy="no-referrer">
                        </div>
                        <div class="member-meta">
                            <h2 class="member-name">{{ $member['name'] }}</h2>
                            <p class="member-role">{{ $member['role'] }}</p>
                        </div>
                    </div>

                        <div class="member-socials">
                            @if(!empty($member['linkedin_url']))
                                <a href="{{ $member['linkedin_url'] }}" class="social-chip social-linkedin" target="_blank" rel="noopener noreferrer">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.94 8.5A1.56 1.56 0 1 1 6.94 5.4a1.56 1.56 0 0 1 0 3.1ZM8.4 9.8H5.5v8.8h2.9V9.8Zm4.6 0h-2.8v8.8h2.8v-4.6c0-2.6 3.4-2.8 3.4 0v4.6h2.9v-5.6c0-4.4-5-4.2-6.3-2.1V9.8Z"/></svg>
                                    LinkedIn
                                </a>
                            @endif
                            <a href="{{ $member['github_url'] }}" class="social-chip social-github" target="_blank" rel="noopener noreferrer">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .5A12 12 0 0 0 8.2 23.9c.6.1.8-.2.8-.6v-2.2c-3.4.7-4.1-1.5-4.1-1.5-.6-1.4-1.3-1.8-1.3-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.6-1.3-5.6-6a4.6 4.6 0 0 1 1.2-3.2c-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.3 1.2a11.4 11.4 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.6 1.6.2 2.8.1 3.1a4.6 4.6 0 0 1 1.2 3.2c0 4.7-2.9 5.7-5.6 6 .4.3.8 1 .8 2.1v3.1c0 .4.2.7.8.6A12 12 0 0 0 12 .5Z"/></svg>
                                GitHub
                            </a>
                            @if(!empty($member['instagram_url']))
                                <a href="{{ $member['instagram_url'] }}" class="social-chip social-instagram" target="_blank" rel="noopener noreferrer">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2Zm0 1.9A3.9 3.9 0 0 0 3.9 7.8v8.4a3.9 3.9 0 0 0 3.9 3.9h8.4a3.9 3.9 0 0 0 3.9-3.9V7.8a3.9 3.9 0 0 0-3.9-3.9H7.8Zm8.8 1.4a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.9a3.1 3.1 0 1 0 0 6.2 3.1 3.1 0 0 0 0-6.2Z"/></svg>
                                    Instagram
                                </a>
                            @endif
                            <a href="mailto:{{ $emailAddress }}" class="social-chip social-email">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 6.5A2.5 2.5 0 0 1 5.5 4h13A2.5 2.5 0 0 1 21 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5v-11Zm2 .3v.2l7 4.7 7-4.7v-.2a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5Zm14 2.5-6.4 4.3a1 1 0 0 1-1.2 0L5 9.3v8.2c0 .3.2.5.5.5h13a.5.5 0 0 0 .5-.5V9.3Z"/></svg>
                                Email
                            </a>
                        </div>

                    @if(!empty($quickPoints))
                        <ul class="member-points">
                            @foreach($quickPoints as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @empty
                <div class="no-data">Data kontributor belum ditemukan pada CONTRIBUTORS.md.</div>
            @endforelse
            </div>

            <div class="page-actions">
                <p class="copyright">&copy; {{ now()->year }} Cadisdik Wilayah XIII. Semua hak cipta dilindungi.</p>
            </div>
        </section>
    </main>
@endsection
