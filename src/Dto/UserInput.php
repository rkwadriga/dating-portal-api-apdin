<?php declare(strict_types=1);
/**
 * Created 2021-11-28
 * Author Dmitry Kushneriov
 */

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class UserInput extends AbstractDto
{
    public const GROUP_DEFAULT = 'Default';
    public const GROUP_CREATE = 'create';
    public const GROUP_UPDATE = 'update';
    public const GROUP_PASSWORD_CHANGE = 'password_change';

    public function __construct(
        #[
            Assert\NotBlank(groups: [self::GROUP_UPDATE]),
            Assert\Length([
                'groups' => [self::GROUP_DEFAULT],
                'min' => 36,
                'max' => 36
            ])
        ]
        public ?string $uuid = null,

        #[
            Assert\NotBlank(groups: [self::GROUP_CREATE]),
            Assert\Email(groups: [self::GROUP_DEFAULT])
        ]
        public ?string $email = null,

        #[
            Assert\NotBlank(groups: [self::GROUP_PASSWORD_CHANGE]),
            Assert\Length([
                'groups' => [self::GROUP_PASSWORD_CHANGE],
                'min' => 4,
                'max' => 36
            ])
        ]
        public ?string $password = null,

        #[
            Assert\NotBlank(groups: [self::GROUP_CREATE]),
            Assert\Length([
                'groups' => [self::GROUP_DEFAULT],
                'min' => 2,
                'max' => 36
            ])
        ]
        public ?string $firstName = null,

        #[
            Assert\NotBlank(groups: [self::GROUP_CREATE]),
            Assert\Length([
                'groups' => [self::GROUP_DEFAULT],
                'min' => 2,
                'max' => 36
            ])
        ]
        public ?string $lastName = null,
    ) {}

    public function createOrUpdateEntity(?User $user): User
    {
        if ($user === null) {
            $user = new User();
        }

        return $this->setEntityAttributes($user, $user->getId() === null);
    }
}