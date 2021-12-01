<?php declare(strict_types=1);
/**
 * Created 2021-11-29
 * Author Dmitry Kushneriov
 */

namespace App\Test;

use App\Api\Routes;
use App\Factory\AdminFactory;

trait AuthenticationTrait
{
    private ?string $token = null;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->logout();
    }

    protected function login(string $email = 'test_admin@test.local', string $password = 'test_admin_passwd', bool $autoCreate = true): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        // Create admin user
        if ($autoCreate) {
            AdminFactory::createOne(['email' => $email, 'plainPassword' => $password]);
        }

        // Login admin user
        $this->send(Routes::LOGIN, [
            'email' => $email,
            'password' => $password
        ]);

        return $this->token = $this->getResponseParams('token');
    }

    protected function logout(): void
    {
        $this->token = null;
    }

    protected function setToken(string $token): void
    {
        $this->token = $token;
    }

    protected function getToken(): ?string
    {
        return $this->token;
    }
}