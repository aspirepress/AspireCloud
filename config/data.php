<?php

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Normalizers\ArrayableNormalizer;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\FormRequestNormalizer;
use Spatie\LaravelData\Normalizers\JsonNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;
use Spatie\LaravelData\Normalizers\ObjectNormalizer;
use Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer;
use Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer;
use Spatie\LaravelData\RuleInferrers\NullableRuleInferrer;
use Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer;
use Spatie\LaravelData\RuleInferrers\SometimesRuleInferrer;
use Spatie\LaravelData\Support\Creation\ValidationStrategy;
use Spatie\LaravelData\Transformers\ArrayableTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\LaravelData\Transformers\EnumTransformer;

return [
    'date_format' => DATE_ATOM,
    'date_timezone' => null,

    'features' => [
        'cast_and_transform_iterables' => true,
        'ignore_exception_when_trying_to_set_computed_property_value' => false,
    ],

    'transformers' => [
        DateTimeInterface::class => DateTimeInterfaceTransformer::class,
        Arrayable::class => ArrayableTransformer::class,
        BackedEnum::class => EnumTransformer::class,
    ],

    'casts' => [
        DateTimeInterface::class => DateTimeInterfaceCast::class,
        BackedEnum::class => EnumCast::class,
    ],

    'rule_inferrers' => [
        SometimesRuleInferrer::class,
        NullableRuleInferrer::class,
        RequiredRuleInferrer::class,
        BuiltInTypesRuleInferrer::class,
        AttributesRuleInferrer::class,
    ],

    'normalizers' => [
        ModelNormalizer::class,
        FormRequestNormalizer::class,
        ArrayableNormalizer::class,
        ObjectNormalizer::class,
        ArrayNormalizer::class,
        JsonNormalizer::class,
    ],

    'wrap' => null,
    'var_dumper_caster_mode' => 'development',

    'structure_caching' => [
        'enabled' => true,
        'directories' => [app_path('Data')],
        'cache' => [
            'store' => env('CACHE_STORE', env('CACHE_DRIVER', 'file')),
            'prefix' => 'laravel-data',
            'duration' => null,
        ],
        'reflection_discovery' => [
            'enabled' => true,
            'base_path' => base_path(),
            'root_namespace' => null,
        ],
    ],

    'validation_strategy' => ValidationStrategy::Always->value,
    'name_mapping_strategy' => ['input' => null, 'output' => null],
    'ignore_invalid_partials' => false,
    'max_transformation_depth' => 512,
    'throw_when_max_transformation_depth_reached' => true,

    'commands' => [
        'make' => [
            'namespace' => 'Data',
            'suffix' => '',
        ],
    ],

    'livewire' => ['enable_synths' => false],
];
