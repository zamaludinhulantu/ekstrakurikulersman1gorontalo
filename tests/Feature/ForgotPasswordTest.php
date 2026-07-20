<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_forgot_password_page_is_accessible(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Kirim Tautan Reset')
            ->assertSee('Reset password akun melalui email.');
    }

    public function test_user_can_request_reset_password_link(): void
    {
        Notification::fake();

        $user = User::query()->where('email', 'siswa1@gmail.com')->firstOrFail();

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect();

        $this->assertGuest();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_user_can_reset_password_from_valid_token(): void
    {
        $user = User::query()->where('email', 'siswa1@gmail.com')->firstOrFail();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'passwordBaru123',
            'password_confirmation' => 'passwordBaru123',
        ])->assertRedirect(route('login'));

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'passwordBaru123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_invalid_email_shows_error_on_reset_request(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => 'tidak.ada@example.com',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
    }
}
