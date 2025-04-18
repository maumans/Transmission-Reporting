<?php

namespace Tests\Unit\Services;

use App\Services\BanqueCentraleService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BanqueCentraleServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BanqueCentraleService();
    }

    public function test_signin_success()
    {
        // Configuration du mock HTTP
        Http::fake([
            '*/auth/signin' => Http::response([
                'token' => 'test-token',
                'user' => [
                    'id' => 1,
                    'username' => 'testuser'
                ]
            ], 200)
        ]);

        // Appel de la méthode signin
        $response = $this->service->signin('testuser', 'password');

        // Vérifications
        $this->assertNotNull($response);
        $this->assertEquals('test-token', $response['token']);
        $this->assertEquals(1, $response['user']['id']);
        $this->assertEquals('testuser', $response['user']['username']);
    }

    public function test_signin_failure()
    {
        // Configuration du mock HTTP pour un échec
        Http::fake([
            '*/auth/signin' => Http::response([
                'message' => 'Identifiants invalides'
            ], 401)
        ]);

        // Appel de la méthode signin
        $response = $this->service->signin('testuser', 'wrongpassword');

        // Vérification
        $this->assertNull($response);
    }

    public function test_logout_success()
    {
        // Configuration du mock HTTP
        Http::fake([
            '*/auth/logout' => Http::response([], 200)
        ]);

        // Appel de la méthode logout
        $result = $this->service->logout();

        // Vérification
        $this->assertTrue($result);
    }

    public function test_reset_password_success()
    {
        // Configuration du mock HTTP
        Http::fake([
            '*/util/rpwd' => Http::response([], 200)
        ]);

        // Appel de la méthode resetPassword
        $result = $this->service->resetPassword('currentpass', 'newpass');

        // Vérification
        $this->assertTrue($result);
    }

    public function test_forgot_password_success()
    {
        // Configuration du mock HTTP
        Http::fake([
            '*/util/fpwd' => Http::response([], 200)
        ]);

        // Appel de la méthode forgotPassword
        $result = $this->service->forgotPassword('test@example.com');

        // Vérification
        $this->assertTrue($result);
    }
} 