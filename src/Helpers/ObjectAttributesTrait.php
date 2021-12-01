<?php declare(strict_types=1);
/**
 * Created 2021-11-28
 * Author Dmitry Kushneriov
 */

namespace App\Helpers;

trait ObjectAttributesTrait
{
    private static ?array $configMetadata = null;

    public static function getConfigMetadata(): array
    {
        if (null !== self::$configMetadata) {
            return self::$configMetadata;
        }

        $rc = new \ReflectionClass(static::class);

        $publicProperties = [];
        foreach ($rc->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $publicProperties[$reflectionProperty->getName()] = true;
        }

        $configurableAttributes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            if (!isset($publicProperties[$name = $param->getName()])) {
                $configurableAttributes[$name] = true;
            }
        }

        return [$publicProperties, $configurableAttributes];
    }
}