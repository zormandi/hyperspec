<?php
declare(strict_types=1);

namespace HyperSpec;

class Processor
{
    public static ExampleGroup $rootExampleGroup;
    public static ExampleGroup $currentExampleGroup;

    public static function initialize(): void
    {
        self::$currentExampleGroup = self::$rootExampleGroup = new ExampleGroup('');
    }

    public static function addExampleGroup(ExampleGroup $exampleGroup): void
    {
        self::$currentExampleGroup->addExampleGroup($exampleGroup);
    }

    public static function executeTests(): void
    {
        self::$rootExampleGroup->verify();
    }
}
