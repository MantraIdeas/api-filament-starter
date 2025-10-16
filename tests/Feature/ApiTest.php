<?php

beforeEach(function () {
    $this->email = 'test@example.com';
});

test('test logout endpoint without authentication', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
});

test('test logout endpoint with authentication', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/logout');

    $response->assertStatus(200);

});

test('validate user registration api', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);
    $response->assertStatus(422);

    $response->assertJsonValidationErrors(['name', 'email', 'password']);

});

test('register user successfully', function () {

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Test User',
        'email' => $this->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'email',
            'token',
        ]
    ]);

    $this->assertDatabaseHas('users',
        [
            'name' => 'Test User',
            'email' => $this->email
        ]
    );

});

test('validate user login api', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'invalid-email',
        'password' => '',
    ]);
    $response->assertStatus(422);

    $response->assertJsonValidationErrors(['email', 'password']);

});


test('login user successfully', function () {
    \App\Models\User::factory()->create([
        'email' => $this->email,
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => $this->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);

    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'email',
            'token',
        ]
    ]);
});


test('login with invalid credentials', function () {
    \App\Models\User::factory()->create([
        'email' => $this->email,
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => $this->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(403);

    $response->assertJson([
        'message' => 'Email or password is incorrect',
        'data' => []
    ]);
});

test('forgot password successfully generate otp and send email', function () {
    \App\Models\User::factory()->create([
        'email' => $this->email,
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/v1/forgot-password', [
        'email' => $this->email,
    ]);

    $response->assertStatus(200);

    $response->assertJson([
        'message' => 'OTP sent to your email successfully',
    ]);

    $this->assertDatabaseHas('otps', [
        'email' => $this->email,
    ]);
});

test('forgot password with invalid email', function () {
    $response = $this->postJson('/api/v1/forgot-password', [
        'email' => 'invalid-email',
    ]);
    $response->assertStatus(422);

    $response->assertJsonValidationErrors(['email']);

});

test('test reset password endpoint', function () {
    $user = \App\Models\User::factory()->create([
        'email' => $this->email,
        'password' => bcrypt('old-password'),
    ]);

    // Simulate OTP generation and saving to database
    $otp = random_int(1000, 9999);
    \App\Models\Otp::factory()->create([
        'email' => $this->email,
        'otp' => $otp,
    ]);

    $response = $this->postJson('/api/v1/reset-password', [
        'otp' => $otp,
        'email' => $this->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertStatus(200);

    $response->assertJson([
        'message' => 'Password reset successfully',
    ]);

    // Verify that the password was updated
    $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password', $user->fresh()->password));
});

test('test resend otp', function () {
    $user = \App\Models\User::factory()->create([
        'email' => $this->email,
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/v1/resend-otp', [
        'email' => $this->email,
    ]);

    $response->assertStatus(200);

    $response->assertJson([
        'message' => 'OTP resent successfully',
    ]);

    $this->assertDatabaseHas('otps', [
        'email' => $this->email,
    ]);

});

test('test verify otp api for registration', function () {
    $user = \App\Models\User::factory()->create([
        'email' => $this->email,
        'password' => bcrypt('password'),
    ]);

    // Simulate OTP generation and saving to database
    $otp = random_int(1000, 9999);
    \App\Models\Otp::factory()->create([
        'email' => $this->email,
        'otp' => $otp,
    ]);

    $response = $this->postJson('/api/v1/verify-otp', [
        'email' => $this->email,
        'otp' => $otp,
        'verify_for' => 'registration',
    ]);

    $response->assertStatus(200);

    $response->assertJson([
        'message' => 'OTP verified successfully',
    ]);
});

test('test verify otp api for reset password', function () {
    $user = \App\Models\User::factory()->create([
        'email' => $this->email,
        'password' => bcrypt('password'),
    ]);

    // Simulate OTP generation and saving to database
    $otp = random_int(1000, 9999);
    \App\Models\Otp::factory()->create([
        'email' => $this->email,
        'otp' => $otp,
    ]);

    $response = $this->postJson('/api/v1/verify-otp', [
        'email' => $this->email,
        'otp' => $otp,
        'verify_for' => 'reset_password',
    ]);

    $this->assertDatabaseHas('otps', [
        'email' => $this->email,
        'otp' => $otp,
    ]);

    $response->assertStatus(200);

    $response->assertJson([
        'message' => 'OTP verified successfully',
    ]);

});

test('test change password api', function () {
    $user = \App\Models\User::factory()->create([
        'email' => $this->email,
        'password' => bcrypt('old-password'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/change-password', [
            'old_password' => 'old-password',
            'new_password' => 'new-password',
            'confirm_password' => 'new-password',
        ]);

    $response->assertStatus(200);

    $response->assertJson([
        'message' => 'Password changed successfully',
    ]);

    // Verify that the password was updated
    $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password', $user->fresh()->password));

});
