<?php

namespace lsst\cantodamassets\gql\resolvers;

use craft\gql\base\Resolver;
use GraphQL\Type\Definition\ResolveInfo;

class CantoDamAssetResolver extends Resolver
{
    /**
     * @inheritDoc
     */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $result = 'woof';

        return $result;
    }
}
