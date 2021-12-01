<?php declare(strict_types=1);
/**
 * Created 2021-11-28
 * Author Dmitry Kushneriov
 */

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\UserInput;
use App\Entity\User;

class UserInputDataTransformerInitializer implements DataTransformerInitializerInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private PasswordHasherFactoryInterface $passwordHasherFactory
    ) {}

    public function initialize(string $inputClass, array $context = [])
    {
        return UserInput::createFromEntity($context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null);
    }

    /**
     * @param UserInput $input
     * @param string $to
     * @param array $context
     * @return User
     */
    public function transform($input, string $to, array $context = [])
    {
        // Get updated entity or create new
        /** @var User $entity */
        $entity = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? new User();

        // Check if password changed
        $passwordChanged = $entity->getId() === null || $input->password !== $entity->getPassword();

        // Fill validation groups to context
        $validationGroups = $context['groups'] ?? [UserInput::GROUP_DEFAULT];
        $validationGroups[] = $entity->getId() === null ? UserInput::GROUP_CREATE : UserInput::GROUP_UPDATE;
        if ($passwordChanged) {
            $validationGroups[] = UserInput::GROUP_PASSWORD_CHANGE;
        }
        $context['groups'] = $validationGroups;

        // Validate input data
        $this->validator->validate($input, $context);

        // Hash password if it's changed
        if ($passwordChanged) {
            $input->password = $this->passwordHasherFactory->getPasswordHasher($entity)->hash($input->password);
        }

        // Set entity values from input data
        return $input->createOrUpdateEntity($entity);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof User) {
            return false;
        }

        return $to === User::class && ($context['input']['class'] ?? null) === UserInput::class;
    }

}