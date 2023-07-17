<?php

namespace lsst\cantodamassets\gql\resolvers;

use craft\base\ElementInterface;
use craft\gql\base\Resolver;
use GraphQL\Type\Definition\ResolveInfo;
use lsst\cantodamassets\models\CantoFieldData;

class CantoDamAssetResolver extends Resolver
{
    /**
     * @inheritDoc
     */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        /** @var ElementInterface $source */
        $fieldName = $resolveInfo->fieldName;
        /** @var CantoFieldData $cantoFieldData */
        $cantoFieldData = $source->{$fieldName};
        return $cantoFieldData->cantoAssetData ?? null;
    }
}
