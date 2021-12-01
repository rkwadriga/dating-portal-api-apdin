<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Service;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;

class AdminUserManager
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function emailIsUnique(string $email): bool
    {
        $exitedAdminRecord = $this->em->getRepository(Admin::class)->findOneByEmail($email);

        return $exitedAdminRecord === null;
    }

    public function createAdmin(string $email, string $password, ?string $firstName, ?string $lastName): Admin
    {
        $admin = new Admin();
        $admin
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setFirstName($firstName)
            ->setLastName($lastName);

        $this->em->persist($admin);
        $this->em->flush();

        return $admin;
    }
}