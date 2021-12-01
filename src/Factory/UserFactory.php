<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static User|Proxy findOrCreate(array $attributes)
 * @method static User|Proxy random()
 * @method static User[]|Proxy[] randomSet(int $number)
 * @method static User[]|Proxy[] randomRange(int $min, int $max)
 * @method static UserRepository|RepositoryProxy repository()
 * @method User|Proxy create($attributes = [])
 * @method User[]|Proxy[] createMany(int $number, $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    const DEFAULT_PASSWORD = 'test';
    const DEFAULT_FIRSTNAME = 'User';
    const DEFAULT_LASTNAME = 'Test';

    public function withEmail(string $email): self
    {
        return $this->addState(['firstName' => $email]);
    }

    public function withFirstName(string $firstName): self
    {
        return $this->addState(['firstName' => $firstName]);
    }

    public function withLastName(string $lastName): self
    {
        return $this->addState(['lastName' => $lastName]);
    }

    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->email(),
            // hashed version of "test"
            // php bin/console security:hash-password --env=test
            'password' => '$2y$13$QrPMYN/4iSkbA8WGvle.CuH.S43SDbbFUQbVOn4M0W8UKiRmVB5tO',
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
        ];
    }

    /*protected function initialize(): self
    {
        parent::initialize();

        $this->beforeInstantiate(function(User $user) {});

        return $this;
    }*/

    protected static function getClass(): string
    {
        return User::class;
    }
}
