@extends('layouts.app')
@section('title', 'About | SIMGOS Dynamic Report')
@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><span>System</span><i class="fa-solid fa-chevron-right"></i><strong>About</strong></div>
            <h1>About SIMGOS Dynamic Report</h1>
            <p>Informasi aplikasi dan platform reporting.</p>
        </div>
    </div>
    <section class="about-hero card">
        <div class="about-mark"><i class="fa-solid fa-chart-line"></i></div>
        <div><span class="eyebrow">SIMGOS REPORTING PLATFORM</span>
            <h2>SIMGOS Dynamic Report</h2>
            <p>Sistem dashboard dan dynamic reporting untuk membantu visualisasi dan analisis data SIMRS SIMGOS.</p><span
                class="version-badge">Version 1.0.0</span>
        </div>
    </section>
    <div class="about-grid">
        <div class="card info-tile"><i class="fa-solid fa-layer-group"></i>
            <div>
                <h3>Laravel MVC</h3>
                <p>Antarmuka dibangun menggunakan Blade, Controller, dan Model Laravel.</p>
            </div>
        </div>
        <div class="card info-tile"><i class="fa-solid fa-shield-halved"></i>
            <div>
                <h3>Read-only by design</h3>
                <p>Tahap awal hanya membaca data dan tidak mengubah struktur database SIMGOS.</p>
            </div>
        </div>
        <div class="card info-tile"><i class="fa-solid fa-chart-simple"></i>
            <div>
                <h3>Dynamic reporting</h3>
                <p>Builder laporan disiapkan agar mudah dihubungkan ke query SIMGOS berikutnya.</p>
            </div>
        </div>
    </div>
@endsection
