<?php declare(strict_types=1);
/**
 * Created 2021-11-26
 * Author Dmitry Kushneriov
 */

namespace App\Tests\Command;

use App\Factory\AdminFactory;
use DateTimeImmutable;
use App\Entity\Admin;
use App\Test\AbstractCommandTest;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class CreateAdminCommandTest extends AbstractCommandTest
{
    use ReloadDatabaseTrait;
    
    private const COMMAND_NAME = 'app:create-admin';

    private const ADMIN_DATA = [
        'email' => 'admin@mail.com',
        'password' => 'test',
        'firstName' => 'Admin',
        'lastName' => 'Admin',
    ];

    public function testSuccessfulFullDataCreating()
    {
        $adminData = self::ADMIN_DATA;
        // Call command with an all params
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);

        // Check is command output correct
        $this->assertStringContainsString("Admin {$adminData['email']} created", $tester->getDisplay());
        // Chek all admin data
        $this->checkCreatedAdminData($adminData);
    }

    public function testSuccessfulCreatingWithoutFirstName()
    {
        $adminData = self::ADMIN_DATA;
        $adminData['firstName'] = '';
        // Call command with emptyFirstName
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Admin {$adminData['email']} created", $tester->getDisplay());
        $this->checkCreatedAdminData($adminData);
    }

    public function testSuccessfulCreatingWithoutLastName()
    {
        $adminData = self::ADMIN_DATA;
        $adminData['lastName'] = '';
        // Call command with empty lastName
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Admin {$adminData['email']} created", $tester->getDisplay());
        $this->checkCreatedAdminData($adminData);
    }

    public function testSuccessfulCreatingWithoutAllOptionalParams()
    {
        $adminData = self::ADMIN_DATA;
        $adminData['firstName'] = '';
        $adminData['lastName'] = '';
        // Call command with empty optional params
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Admin {$adminData['email']} created", $tester->getDisplay());
        $this->checkCreatedAdminData($adminData);
    }

    public function testCreatingWithDuplicatedEmail()
    {
        // Create admin record
        $adminData = self::ADMIN_DATA;
        AdminFactory::createOne(array_merge($adminData, ['plainPassword' => $adminData['password']]));

        // Call command with existed email
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);

        // Check is command output correct
        $this->assertStringContainsString("This email is already used", $tester->getDisplay());
    }

    public function testCreatingWithIncorrectRequiredData()
    {
        $adminData = self::ADMIN_DATA;

        $adminData['email'] = '';
        // Call command without email
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid email", $tester->getDisplay());
        $this->assertStringContainsString("This param is required", $tester->getDisplay());

        $adminData['email'] = 'invalid_email';
        // Call command with invalid email
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid email", $tester->getDisplay());

        $adminData['email'] = self::ADMIN_DATA['email'];
        $adminData['password'] = '';
        // Call command without password
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid password", $tester->getDisplay());
        $this->assertStringContainsString("This param is required", $tester->getDisplay());

        $adminData['email'] = '';
        $adminData['password'] = '';
        // Call command without all required params
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid email", $tester->getDisplay());
        $this->assertStringContainsString("This param is required", $tester->getDisplay());

        $adminData['email'] = self::ADMIN_DATA['email'];
        $adminData['password'] = 'pas';
        // Call command with short password
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid password", $tester->getDisplay());

        $adminData['password'] = 'suuuuper_loooooooooong_passwooooooord';
        // Call command with long password
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid password", $tester->getDisplay());

        $adminData['email'] = '';
        $adminData['password'] = '';
        $adminData['firstName'] = '';
        $adminData['lastName'] = '';
        // Call command without all params
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid email", $tester->getDisplay());
        $this->assertStringContainsString("This param is required", $tester->getDisplay());
    }

    public function testCreatingWithIncorrectOptionalData()
    {
        $adminData = self::ADMIN_DATA;
        $adminData['firstName'] = 'A';
        // Call command with short firstName
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid firstName", $tester->getDisplay());

        // Call command with long firstName
        $adminData['firstName'] = 'suuuuper_loooooooooong_firstNaaaaaame';
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid firstName", $tester->getDisplay());

        $adminData['firstName'] = self::ADMIN_DATA['firstName'];
        $adminData['lastName'] = 'A';
        // Call command with short lastName
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid lastName", $tester->getDisplay());

        // Call command with long lastName
        $adminData['lastName'] = 'suuuuper_loooooooooong_lastNaaaaaaame';
        $tester = $this->getCommandTester(self::COMMAND_NAME);
        $tester->execute($adminData);
        $this->assertStringContainsString("Invalid lastName", $tester->getDisplay());
    }

    private function checkCreatedAdminData(array $adminData)
    {
        // Check is record in "admin" table created adn has correct data
        $admin = $this->em->getRepository(Admin::class)->findOneByEmail($adminData['email']);
        $this->assertNotNull($admin);
        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertIsInt($admin->getId());
        $this->assertEquals($adminData['email'], $admin->getEmail());
        $this->assertEquals($adminData['password'], $admin->getPlainPassword());
        $this->assertEquals($adminData['firstName'], $admin->getFirstName());
        $this->assertEquals($adminData['lastName'], $admin->getLastName());
        $this->assertInstanceOf(DateTimeImmutable::class, $admin->getCreatedAt());

        // Check password
        /** @var PasswordHasherFactoryInterface $passwordHasher */
        $passwordHasher = $this->loadComponent(PasswordHasherFactoryInterface::class);
        $this->assertTrue($passwordHasher->getPasswordHasher($admin)->verify($admin->getPassword(), $adminData['password']));
    }
}