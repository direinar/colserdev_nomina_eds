<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\Hash;

test('nadie se elimina a sí mismo ni deja el sistema sin administradores', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
    $contador = User::factory()->create(['role' => User::ROLE_CONTADOR]);

    $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertSessionHas('error', 'No puede eliminar su propio usuario.');

    // Único administrador: no se le puede quitar el rol.
    $this->actingAs($admin)->put(route('users.update', $admin), [
        'name' => $admin->name, 'email' => $admin->email, 'role' => User::ROLE_CONTADOR,
    ])->assertSessionHasErrors('role');

    expect($admin->fresh()->role)->toBe(User::ROLE_ADMINISTRADOR);

    // Con otro administrador, sí se puede eliminar uno de los dos.
    $otroAdmin = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
    $this->actingAs($admin)->delete(route('users.destroy', $otroAdmin))->assertSessionHas('success');
    $this->actingAs($admin)->delete(route('users.destroy', $contador))->assertSessionHas('success');

    expect(User::query()->where('role', User::ROLE_ADMINISTRADOR)->count())->toBe(1);
});

test('el contador entra al dashboard pero no a la gestion de usuarios', function () {
    $contador = User::factory()->create(['role' => User::ROLE_CONTADOR]);

    $this->actingAs($contador)->get(route('dashboard'))->assertOk()->assertDontSee('Administración');
    $this->actingAs($contador)->get(route('users.index'))->assertForbidden();
});

test('el gerente y el super administrador tienen el acceso del administrador', function (string $role) {
    $usuario = User::factory()->create(['role' => $role]);

    $this->actingAs($usuario)->get(route('dashboard'))->assertOk()->assertSee('Administración');
    $this->actingAs($usuario)->get(route('users.index'))->assertOk();
})->with([User::ROLE_GERENTE, User::ROLE_SUPERADMIN]);

test('solo el gerente o el super administrador gestionan esas cuentas y asignan esos roles', function () {
    $gerente = User::factory()->create(['role' => User::ROLE_GERENTE]);
    $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
    $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
    $contador = User::factory()->create(['role' => User::ROLE_CONTADOR]);

    $this->actingAs($admin)->get(route('users.edit', $gerente))->assertForbidden();
    $this->actingAs($admin)->put(route('users.update', $gerente), [
        'name' => $gerente->name, 'email' => $gerente->email, 'role' => User::ROLE_CONTADOR,
    ])->assertForbidden();
    $this->actingAs($admin)->delete(route('users.destroy', $superadmin))->assertForbidden();
    $this->actingAs($admin)->put(route('users.update', $contador), [
        'name' => $contador->name, 'email' => $contador->email, 'role' => User::ROLE_GERENTE,
    ])->assertSessionHasErrors('role');

    expect($gerente->fresh()->role)->toBe(User::ROLE_GERENTE)
        ->and($contador->fresh()->role)->toBe(User::ROLE_CONTADOR);

    $this->actingAs($gerente)->put(route('users.update', $admin), [
        'name' => $admin->name, 'email' => $admin->email, 'role' => User::ROLE_SUPERADMIN,
    ])->assertSessionHasNoErrors();

    expect($admin->fresh()->role)->toBe(User::ROLE_SUPERADMIN);
});

test('no se le quita el rol al único gerente o super administrador', function () {
    $gerente = User::factory()->create(['role' => User::ROLE_GERENTE]);
    User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);

    $this->actingAs($gerente)->put(route('users.update', $gerente), [
        'name' => $gerente->name, 'email' => $gerente->email, 'role' => User::ROLE_ADMINISTRADOR,
    ])->assertSessionHasErrors('role');

    expect($gerente->fresh()->role)->toBe(User::ROLE_GERENTE);
});

test('las rutas por rol comparan el rol igual que el menú, sin importar mayúsculas', function () {
    $admin = User::factory()->create(['role' => 'Administrador']);

    $this->actingAs($admin)->get(route('users.index'))->assertOk();
});

test('el seeder de administradores no cambia la clave de un usuario existente', function () {
    $existente = User::factory()->create([
        'email' => 'diego.reina9@hotmail.com',
        'password' => 'MiClaveSegura#2026',
        'role' => User::ROLE_ADMINISTRADOR,
    ]);

    $this->seed(AdminUserSeeder::class);

    expect(Hash::check('MiClaveSegura#2026', $existente->fresh()->password))->toBeTrue()
        ->and(Hash::check('password', $existente->fresh()->password))->toBeFalse()
        // El otro administrador se crea con una clave aleatoria, no con "password".
        ->and(Hash::check('password', User::query()->where('email', 'carlosbeltranramos@gmail.com')->sole()->password))->toBeFalse();
});

test('el registro publico esta desactivado: las cuentas las crea administracion', function () {
    $this->get('/register')->assertNotFound();
});
