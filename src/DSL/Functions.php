<?php
declare(strict_types=1);

namespace HyperSpec\DSL;

use Closure;
use HyperSpec\Example;
use HyperSpec\ExampleGroup;
use HyperSpec\HyperSpec;

class Functions {}

function describe(string $description, Closure $definition): void
{
    $exampleGroup = new ExampleGroup($description, HyperSpec::$currentExampleGroup);
    if (empty(HyperSpec::$currentExampleGroup)) {
        HyperSpec::addExampleGroup($exampleGroup);
    } else {
        HyperSpec::$currentExampleGroup->addExampleGroup($exampleGroup);
    }

    $previousExampleGroup = HyperSpec::$currentExampleGroup;
    HyperSpec::$currentExampleGroup = $exampleGroup;
    $definition();
    HyperSpec::$currentExampleGroup = $previousExampleGroup;
}

function context(string $description, Closure $definition): void
{
    describe($description, $definition);
}

function xcontext(string $description, Closure $definition): void
{
}

function beforeEach(Closure $initializer): void
{
    HyperSpec::$currentExampleGroup->addInitializer($initializer);
}

function afterEach(Closure $finalizer): void
{
    HyperSpec::$currentExampleGroup->addFinalizer($finalizer);
}

function it(string $description, Closure $definition): void
{
    HyperSpec::$currentExampleGroup->addExample(new Example($description, $definition, HyperSpec::$currentExampleGroup));
}

function xit(string $description, Closure $definition): void
{
}

function let(string $name, mixed $value): void
{
    HyperSpec::$currentExampleGroup->addSharedFixture($name, $value);
}

function subject(mixed $value): void
{
    let('subject', $value);
}
