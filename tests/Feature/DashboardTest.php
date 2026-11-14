<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('todos los roles de nomina ven el dashboard', function (string $role) {
    $usuario = User::factory()->create(['role' => $role, 'name' => 'Ana Pérez']);

    $this->actingAs($usuario)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Bienvenido(a), Ana Pérez')
        ->assertSee(User::etiquetaRol($role));
})->with(User::roles());
