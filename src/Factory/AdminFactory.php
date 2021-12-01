<?php declare(strict_types=1);
/**
 * Created 2021-11-27
 * Author Dmitry Kushneriov
 */

namespace App\Factory;

use DateTimeImmutable;
use App\Entity\Admin;
use App\Repository\AdminRepository;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static Admin|Proxy findOrCreate(array $attributes)
 * @method static Admin|Proxy random()
 * @method static Admin[]|Proxy[] randomSet(int $number)
 * @method static Admin[]|Proxy[] randomRange(int $min, int $max)
 * @method static AdminRepository|RepositoryProxy repository()
 * @method Admin|Proxy create($attributes = [])
 * @method Admin[]|Proxy[] createMany(int $number, $attributes = [])
 */

class AdminFactory extends ModelFactory
{
    public function __construct(
        private PasswordHasherFactoryInterface $encoder
    ) {
        parent::__construct();

    }

    const DEFAULT_PASSWORD = 'test';
    const DEFAULT_FIRSTNAME = 'User';
    const DEFAULT_LASTNAME = 'Test';

    public function withFirstName(string $number = self::DEFAULT_FIRSTNAME): self
    {
        return $this->addState(['firstName' => $number]);
    }

    public function withLastName(string $number = self::DEFAULT_LASTNAME): self
    {
        return $this->addState(['lastName' => $number]);
    }

    public function withCreatedAt(?DateTimeImmutable $createdAt = null): self
    {
        if ($createdAt === null) {
            $createdAt = new DateTimeImmutable();
        }
        return $this->addState(['createdAt' => $createdAt]);
    }

    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->email(),
            // hashed version of "test"
            // php bin/console security:hash-password --env=test
            'plainPassword' => self::DEFAULT_PASSWORD,
        ];
    }

    protected static function getClass(): string
    {
        return Admin::class;
    }
}