@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Dashboard" icon="speedometer2"
        subtitle="Bienvenido(a), {{ $usuario->name }}. Sistema de nómina de EDS El Portal de Tunja.">
        @if ($usuario->isAdministrador())
            <a href="{{ route('users.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-people" aria-hidden="true"></i> Gestionar usuarios
            </a>
        @endif
    </x-page-header>

    {{-- Los indicadores (empleados activos, novedades del periodo, nómina liquidada)
         se agregan con sus módulos. --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="dashboard-kpi">
                <span class="dashboard-kpi-titulo">Su rol</span>
                <span class="dashboard-kpi-valor">{{ \App\Models\User::etiquetaRol($usuario->role) }}</span>
                <span class="dashboard-kpi-detalle">{{ $usuario->email }}</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="dashboard-kpi">
                <span class="dashboard-kpi-titulo">Módulos de nómina</span>
                <span class="dashboard-kpi-valor">En construcción</span>
                <span class="dashboard-kpi-detalle">Empleados, novedades, liquidación, prestaciones e informes.</span>
            </div>
        </div>
    </div>
@endsection
