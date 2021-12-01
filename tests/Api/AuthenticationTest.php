<?php declare(strict_types=1);
/**
 * Created 2021-11-29
 * Author Dmitry Kushneriov
 */

namespace App\Tests\Api;

use App\Api\Routes;
use App\Factory\AdminFactory;
use App\Test\AbstractApiTest;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationTest extends AbstractApiTest
{
    use ReloadDatabaseTrait;

    public function testLogin()
    {
        // Create admin user
        $admin = AdminFactory::new()->create(['email' => 'admin@test.local', 'plainPassword' => 'password'])->disableAutoRefresh();

        $this->send(Routes::LOGIN, [
            'email' => $admin->getEmail(),
            'password' => $admin->getPlainPassword()
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $token = $this->getResponseParams('token');
        $this->assertIsString($token);
        $this->assertGreaterThan(400, strlen($token));
    }
}