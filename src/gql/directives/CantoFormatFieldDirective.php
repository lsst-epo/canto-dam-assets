<?php

namespace lsst\cantodamassets\gql\directives;

use craft\gql\base\Directive;
use craft\gql\GqlEntityRegistry;
use DateTime;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive as GqlDirective;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;


class CantoFormatFieldDirective extends Directive
{
    public const DEFAULT_SOURCE_FORMAT = 'YmdHisu';
    public const DEFAULT_DESTINATION_FORMAT = 'ISO8601';

    /**
     * @inheritdoc
     */
    public static function create(): GqlDirective
    {
        $typeName = static::name();

        return GqlEntityRegistry::getOrCreate($typeName, fn() => new self([
            'name' => $typeName,
            'locations' => [
                DirectiveLocation::FIELD,
            ],
            'args' => [
                new FieldArgument([
                    'name' => 'sourceFormat',
                    'type' => Type::string(),
                    'defaultValue' => self::DEFAULT_SOURCE_FORMAT,
                    'description' => 'The source format to use',
                ]),
                new FieldArgument([
                    'name' => 'destinationFormat',
                    'type' => Type::string(),
                    'description' => 'The destination format to use',
                    'defaultValue' => self::DEFAULT_DESTINATION_FORMAT,
                ])
            ],
            'description' => 'Formats a field in the desired destination format given a source format',
        ]));
    }

    /**
     * @inheritdoc
     */
    public static function name(): string
    {
        return 'cantoFormatField';
    }

    /**
     * @inheritdoc
     */
    public static function apply(mixed $source, mixed $value, array $arguments, ResolveInfo $resolveInfo): mixed
    {
        $sourceFormat = $arguments['sourceFormat'] ?? self::DEFAULT_SOURCE_FORMAT;
        $destinationFormat = $arguments['destinationFormat'] ?? self::DEFAULT_DESTINATION_FORMAT;

        $dateTime = DateTime::createFromFormat($sourceFormat, $value);

        if ($destinationFormat == "ISO8601") {
            return self::convertToISO8601($dateTime);
        } else {
            return $dateTime->format($destinationFormat);
        }

    }

    public static function convertToISO8601(DateTime $dateTime): string
    {
        $partialDestinationFormat = "Y-m-d\TH:i:s";
        $partialConvertedDateTime = $dateTime->format($partialDestinationFormat);
        $milliseconds = substr($dateTime->format("u"), 0, 3);
        return $partialConvertedDateTime . "." . $milliseconds . "Z";
    }

}
