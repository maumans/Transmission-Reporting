<?php

namespace Tests\Feature;

use App\Services\BanqueCentraleService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    protected $banqueCentraleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->banqueCentraleService = $this->createMock(BanqueCentraleService::class);
        $this->app->instance(BanqueCentraleService::class, $this->banqueCentraleService);
    }

    public function test_login_page_loads()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/Login')
        );
    }

    public function test_login_success()
    {
        $this->banqueCentraleService
            ->expects($this->once())
            ->method('signin')
            ->with('testuser', 'password')
            ->willReturn([
                'token' => 'test-token',
                'user' => [
                    'id' => 1,
                    'username' => 'testuser'
                ]
            ]);

        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'password' => 'password'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertTrue(session()->has('banque_centrale_token'));
        $this->assertEquals('test-token', session('banque_centrale_token'));
    }

    public function test_login_failure()
    {
        $this->banqueCentraleService
            ->expects($this->once())
            ->method('signin')
            ->with('testuser', 'wrongpassword')
            ->willReturn(null);

        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'password' => 'wrongpassword'
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('message');
    }

    public function test_logout()
    {
        $this->banqueCentraleService
            ->expects($this->once())
            ->method('logout')
            ->willReturn(true);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/login');
        $this->assertFalse(session()->has('banque_centrale_token'));
    }

    public function test_reset_password_page_loads()
    {
        $response = $this->get(route('password.reset'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/ResetPassword')
        );
    }

    public function test_reset_password_success()
    {
        $this->banqueCentraleService
            ->expects($this->once())
            ->method('resetPassword')
            ->with('currentpass', 'newpass')
            ->willReturn(true);

        $response = $this->post(route('password.update'), [
            'current_password' => 'currentpass',
            'new_password' => 'newpass',
            'confirm_password' => 'newpass'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');
    }

    public function test_forgot_password_page_loads()
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/ForgotPassword')
        );
    }

    public function test_forgot_password_success()
    {
        $this->banqueCentraleService
            ->expects($this->once())
            ->method('forgotPassword')
            ->with('test@example.com')
            ->willReturn(true);

        $response = $this->post(route('password.email'), [
            'email' => 'test@example.com'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
} 