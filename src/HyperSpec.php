<?php
declare(strict_types=1);

namespace HyperSpec;

class HyperSpec
{
    public static array $exampleGroups = [];
    public static ?ExampleGroup $currentExampleGroup = null;

    public static function addExampleGroup(ExampleGroup $exampleGroup): void
    {
        self::$exampleGroups[] = $exampleGroup;
    }
}
