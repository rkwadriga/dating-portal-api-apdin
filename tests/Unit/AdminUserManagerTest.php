<?php declare(strict_types=1);
/**
 * Created 2021-11-26
 * Author Dmitry Kushneriov
 */

namespace App\Tests\Unit;

use DateTimeImmutable;
use App\Doctrine\Listener\AdminCreatedListener;
use App\Entity\Admin;
use App\Repository\AdminRepository;
use App\Service\AdminUserManager;
use App\Test\AbstractUnitTest;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class AdminUserManagerTest extends AbstractUnitTest
{
    private const ADMIN_DATA = [
        'email' => 'admin@mail.com',
        'password' => 'test',
        'firstName' => 'Admin',
        'lastName' => 'Admin',
    ];

    public function testEmailIsUnique()
    {
        // <-- AdminUserManager.emailIsUnique should return true -->
        // Mock admin repository
        $adminRepositoryMock = $this->getMockBuilder(AdminRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findOneByEmail'])
            ->getMock();

        // Mock "AdminUserManager.findOneByEmail" method to always return null
        $adminRepositoryMock->method('findOneByEmail')->willReturn(null);

        // Mock entity manager with mocked method "getRepository" to always return mocked AdminRepository
        $emMock = $this->mockEntityManagerForRepository($adminRepositoryMock);

        // Create AdminUserManager instance with mocked "$em->getRepository()" method
        $userManager = new AdminUserManager($emMock);

        // AdminUserManager.emailIsUnique should return true
        $this->assertTrue($userManager->emailIsUnique(self::ADMIN_DATA['email']));


        // <-- AdminUserManager.emailIsUnique should return false -->
        // Mock "AdminUserManager.findOneByEmail" method to always return true
        $adminRepositoryMock = $this->getMockBuilder(AdminRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findOneByEmail'])
            ->getMock();

        // Mock "AdminUserManager.findOneByEmail" method to always return an admin object
        $adminRepositoryMock->method('findOneByEmail')->willReturn(new Admin());

        // Mock entity manager "getRepository" method to always return mocked AdminRepository
        $emMock = $this->mockEntityManagerForRepository($adminRepositoryMock);

        // Create AdminUserManager instance with mocked "$em->getRepository()" method
        $userManager = new AdminUserManager($emMock);

        // AdminUserManager.emailIsUnique should return false
        $this->assertFalse($userManager->emailIsUnique(self::ADMIN_DATA['email']));


        // <-- AdminUserManager.createAdmin should return e new Admin object -->
        // Mock EntityManager.flush method
        $emMock->method('flush')->willReturn(null);

        // Create AdminUserManager instance with mocked entity manager
        $userManager = new AdminUserManager($emMock);

        // Create admin
        $adminData = self::ADMIN_DATA;
        $admin = $userManager->createAdmin(...$adminData);
        // Check admin fields before entity listener processing
        $this->assertEquals($adminData['email'], $admin->getEmail());
        $this->assertEquals($adminData['password'], $admin->getPlainPassword());
        $this->assertEquals($adminData['firstName'], $admin->getFirstName());
        $this->assertEquals($adminData['lastName'], $admin->getLastName());
        $this->assertNull($admin->getPassword());
        $this->assertNull($admin->getCreatedAt());

        // Mock password hasher method "hash" to always return the "" value
        $mockedPasswordHasher = $this->getMockBuilder(PasswordHasherInterface::class)->getMock();
        $mockedPasswordHasher->method('hash')->willReturn('hashed_password_12345');

        // Mock password hasher factory to always return mocked password hasher
        $mockedPasswordHasherFactory = $this->getMockBuilder(PasswordHasherFactoryInterface::class)->getMock();
        $mockedPasswordHasherFactory->method('getPasswordHasher')->willReturn($mockedPasswordHasher);

        // Call the admin entity listener
        $entityListener = new AdminCreatedListener($mockedPasswordHasherFactory);
        $entityListener->preFlush($admin);

        // Check admin fields after entity listener processing
        $this->assertEquals($adminData['email'], $admin->getEmail());
        $this->assertEquals($adminData['password'], $admin->getPlainPassword());
        $this->assertEquals($adminData['firstName'], $admin->getFirstName());
        $this->assertEquals($adminData['lastName'], $admin->getLastName());
        $this->assertEquals('hashed_password_12345', $admin->getPassword());
        $this->assertInstanceOf(DateTimeImmutable::class, $admin->getCreatedAt());
    }
}