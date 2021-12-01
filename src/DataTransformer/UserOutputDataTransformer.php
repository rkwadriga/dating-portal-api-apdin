<?php declare(strict_types=1);
/**
 * Created 2021-11-27
 * Author Dmitry Kushneriov
 */

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\UserOutput;
use App\Entity\User;

class UserOutputDataTransformer implements DataTransformerInterface
{
    public function transform($object, string $to, array $context = [])
    {
        return UserOutput::createFromEntity($object);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $data instanceof User && $to === UserOutput::class;
    }
}