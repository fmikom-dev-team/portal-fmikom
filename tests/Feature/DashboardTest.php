<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the laravel dashboard page', function () {
    $user = createUserWithType('mahasiswa');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('mahasiswa.dashboard'));
});

test('admin users are redirected to the admin dashboard after login redirect route', function () {
    $user = createUserWithType('admin');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('mahasiswa users are redirected to the fast user dashboard after login redirect route', function () {
    $user = createUserWithType('mahasiswa');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('mahasiswa.dashboard'));
});

test('dosen users are redirected to the dosen dashboard after login redirect route', function () {
    $user = createUserWithType('dosen');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('dosen.dashboard'));
});

test('kaprodi users are redirected to the kaprodi dashboard after login redirect route', function () {
    $user = createUserWithType('kaprodi');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('kaprodi.dashboard'));
});

test('dekan users are redirected to the dekan dashboard after login redirect route', function () {
    $user = createUserWithType('dekan');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('dekan.dashboard'));
});

test('admin users cannot access the fast user dashboard directly', function () {
    $user = createUserWithType('admin');

    $this->actingAs($user)
        ->get(route('fast.user.dashboard'))
        ->assertForbidden();
});

test('mahasiswa users cannot access the admin dashboard directly', function () {
    $user = createUserWithType('mahasiswa');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('mahasiswa users cannot access the approval dashboard directly', function () {
    $user = createUserWithType('mahasiswa');

    $this->actingAs($user)
        ->get(route('approval.dashboard'))
        ->assertForbidden();
});
