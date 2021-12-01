<?php declare(strict_types=1);
/**
 * Created 2021-11-27
 * Author Dmitry Kushneriov
 */

namespace App\Dto;

use App\Helpers\ObjectAttributesTrait;

abstract class AbstractDto
{
    use ObjectAttributesTrait;

    public static function createFromEntity(?object $entity): static
    {
        if ($entity === null) {
            return new static();
        }

        $constructorAttributes = [];
        [$publicProperties, $configurableAttributes] = static::getConfigMetadata();
        foreach (array_keys($publicProperties) as $property) {
            $getter = 'get' . ucfirst($property);
            if (method_exists($entity, $getter)) {
                $constructorAttributes[$property] = $entity->$getter();
            } elseif (property_exists($entity, $property)) {
                $constructorAttributes[$property] = $entity->$property;
            }
        }

        return new static(...$constructorAttributes);
    }

    public function setEntityAttributes(?object $entity, bool $isNew = true): object
    {
        [$publicProperties, $configurableAttributes] = static::getConfigMetadata();
        foreach (array_keys($publicProperties) as $property) {
            if ($isNew && $this->$property === null) {
                continue;
            }
            $setter = 'set' . ucfirst($property);
            if (method_exists($entity, $setter)) {
                $entity->$setter($this->$property);
            } elseif (property_exists($entity, $property)) {
                $entity->$property = $this->$property;
            }
        }

        return $entity;
    }
}