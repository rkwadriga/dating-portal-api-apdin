<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Doctrine\Listener;

use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use App\Entity\Admin;

class AdminCreatedListener
{
    public function __construct(
        private PasswordHasherFactoryInterface $encoder
    ) {}

    public function preFlush(Admin $admin): void
    {
        if ($admin->getPlainPassword() !== null) {
            $admin->setPassword($this->encoder->getPasswordHasher($admin)->hash($admin->getPlainPassword()));
        }
        if ($admin->getCreatedAt() === null) {
            $admin->setCreatedAt(new DateTimeImmutable());
        }
    }
}