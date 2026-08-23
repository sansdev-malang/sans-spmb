<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users can register via frictionless quick register', function () {
    // Clear out potential conflicting settings
    \App\Models\SpmbPeriod::query()->delete();
    \App\Models\SpmbWave::query()->delete();
    \App\Models\SpmbType::query()->delete();
    \App\Models\SpmbUnit::query()->delete();

    // Create active configurations
    $period = \App\Models\SpmbPeriod::create(['year' => '2026/2027', 'is_active' => true]);
    $wave = \App\Models\SpmbWave::create(['name' => 'Gelombang 1', 'is_active' => true]);
    $type = \App\Models\SpmbType::create(['name' => 'Reguler', 'is_active' => true]);
    $unit = \App\Models\SpmbUnit::create(['name' => 'SD Anak Saleh', 'is_active' => true]);

    $response = $this->post('/quick-register', [
        'candidate_name' => 'Siswa Baru',
        'email' => 'siswabaru@example.com',
        'parent_phone' => '081234567890',
        'admission_level' => 'SD',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $user = \App\Models\User::where('email', 'siswabaru@example.com')->first();
    $this->assertNotNull($user);

    $registration = \App\Models\Registration::where('user_id', $user->id)->first();
    $this->assertNotNull($registration);
    $this->assertEquals('Siswa Baru', $registration->candidate_name);
    $this->assertEquals('SD', $registration->admission_level);
    $this->assertEquals($unit->id, $registration->spmb_unit_id);
    $this->assertEquals($period->id, $registration->spmb_period_id);
    $this->assertEquals($wave->id, $registration->spmb_wave_id);
    $this->assertEquals($type->id, $registration->spmb_type_id);

    $response->assertRedirect(route('dashboard.detail', $registration->id));
});
