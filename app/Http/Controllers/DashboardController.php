<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Pantalla de inicio de nómina. Los indicadores se agregan con los módulos
     * (empleados, novedades, liquidación).
     */
    public function index(Request $request): View
    {
        return view('dashboard.index', [
            'usuario' => $request->user(),
        ]);
    }
}
