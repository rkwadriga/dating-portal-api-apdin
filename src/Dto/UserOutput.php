<?php declare(strict_types=1);
/**
 * Created 2021-11-27
 * Author Dmitry Kushneriov
 */

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class UserOutput extends AbstractDto
{
    public const GROUP_READ = 'user:read';
    public const GROUP_WRITE = 'user:write';

    public function __construct(
        #[Groups([self::GROUP_READ, self::GROUP_WRITE])]
        public int $id,

        #[Groups([self::GROUP_READ, self::GROUP_WRITE])]
        public string $uuid,

        #[Groups([self::GROUP_READ, self::GROUP_WRITE])]
        public string $email,

        #[Groups([self::GROUP_READ, self::GROUP_WRITE])]
        public string $firstName,

        #[Groups([self::GROUP_READ, self::GROUP_WRITE])]
        public string $lastName,
    ) {}
}