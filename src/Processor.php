<?php
declare(strict_types=1);

namespace HyperSpec;

use Closure;

class Processor
{
    private static ExampleGroup $rootExampleGroup;
    private static ExampleGroup $currentExampleGroup;

    public static function initialize(): void
    {
        self::$currentExampleGroup = self::$rootExampleGroup = new ExampleGroup('');
    }

    public static function addInitializer(Closure $initializer): void
    {
        self::$currentExampleGroup->addInitializer($initializer);
    }

    public static function addFinalizer(Closure $finalizer): void
    {
        self::$currentExampleGroup->addFinalizer($finalizer);
    }

    public static function addExampleGroup(string $description, Closure $definition): void
    {
        $exampleGroup = new ExampleGroup($description, self::$currentExampleGroup);
        self::$currentExampleGroup->addExampleGroup($exampleGroup);

        $previousExampleGroup = self::$currentExampleGroup;
        self::$currentExampleGroup = $exampleGroup;
        $definition();
        self::$currentExampleGroup = $previousExampleGroup;
    }

    public static function addExample(string $description, Closure $definition): void
    {
        self::$currentExampleGroup->addExample(new Example($description, $definition, self::$currentExampleGroup));
    }

    public static function addSharedFixture(string $name, $value): void
    {
        self::$currentExampleGroup->addSharedFixture($name, $value);
    }

    public static function executeTests(): void
    {
        self::$rootExampleGroup->verify();
    }
}
