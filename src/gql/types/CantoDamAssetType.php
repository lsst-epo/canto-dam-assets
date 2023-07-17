<?php

namespace lsst\cantodamassets\gql\types;

use craft\gql\base\ObjectType;
use craft\helpers\Json;
use GraphQL\Type\Definition\ResolveInfo;
use lsst\cantodamassets\models\CantoFieldData;

class CantoDamAssetType extends ObjectType
{
    /**
     * @inheritdoc
     */
    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        /** @var CantoFieldData $source */
        $fieldName = $resolveInfo->fieldName;
        $assetDataArray = Json::decodeIfJson($source->cantoAssetData);
        return $assetDataArray[0][$fieldName] ?? null;
    }
}
