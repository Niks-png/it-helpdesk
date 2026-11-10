<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_sent_to_latvian_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Pierakstīties');
    }

    public function test_wrong_password_is_rejected(): void
    {
        Livewire::test(Login::class)
            ->fillForm(['email' => 'darbinieks@test.local', 'password' => 'nepareiza'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_employee_and_admin_can_log_in_with_their_role(): void
    {
        foreach (['darbinieks@test.local' => 'darbinieks123', 'admin@test.local' => 'admin123'] as $email => $password) {
            Livewire::test(Login::class)
                ->fillForm(['email' => $email, 'password' => $password])
                ->call('authenticate')
                ->assertHasNoFormErrors();

            $this->assertAuthenticatedAs(User::firstWhere('email', $email));
            $this->get('/')->assertOk();
            auth()->logout();
        }

        $this->assertFalse(User::firstWhere('email', 'darbinieks@test.local')->isAdmin());
        $this->assertTrue(User::firstWhere('email', 'admin@test.local')->isAdmin());
    }
}
