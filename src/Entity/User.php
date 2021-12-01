<?php

namespace App\Entity;

use App\Dto\UserInput;
use App\Dto\UserOutput;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ApiResource(
    collectionOperations: [
        "get" => [
            "normalization_context" => [
                "groups" => [UserOutput::GROUP_READ]
            ]
        ],
        "post" => [
            "normalization_context" => [
                "groups" => [UserOutput::GROUP_WRITE]
            ]
        ],
    ],
    itemOperations: [
        "get" => [
            "normalization_context" => [
                "groups" => [UserOutput::GROUP_READ]
            ]
        ],
        "put" => [
            "normalization_context" => [
                "groups" => [UserOutput::GROUP_WRITE]
            ]
        ],
        "delete"
    ],
    attributes: [
        "formats" => ["json", "jsonld"]
    ],
    input: UserInput::class,
    output: UserOutput::class,
)]
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("uuid")
 * @UniqueEntity("email")
 */
class User implements PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * //ApiProperty(identifier=false)
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=36, nullable=false, unique=true)
     * //SerializedName("id")
     * //ApiProperty(identifier=true, iri="id")
     */
    private string $uuid;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $password;

    /**
     * @ORM\Column(name="firstName", type="string", length=255, nullable=false)
     */
    private ?string $firstName;

    /**
     * @ORM\Column(name="lastName", type="string", length=255, nullable=false)
     */
    private ?string $lastName;

    public function __construct()
    {
        $this->uuid = (string) Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }
}
