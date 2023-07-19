<?php

namespace lsst\cantodamassets\gql\types;

use craft\gql\base\ObjectType;
use lsst\cantodamassets\gql\interfaces\CantoDamAssetInterface;

class CantoDamAssetType extends ObjectType
{
    public function __construct(array $config)
    {
        $config['interfaces'] = [
            CantoDamAssetInterface::getType(),
        ];

        parent::__construct($config);
    }
}
