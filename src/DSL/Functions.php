<?php
declare(strict_types=1);

namespace HyperSpec\DSL;

use Closure;
use HyperSpec\Example;
use HyperSpec\ExampleGroup;
use HyperSpec\Processor;

function describe(string $description, Closure $definition): void
{
    $exampleGroup = new ExampleGroup($description, Processor::$currentExampleGroup);
    Processor::addExampleGroup($exampleGroup);

    $previousExampleGroup = Processor::$currentExampleGroup;
    Processor::$currentExampleGroup = $exampleGroup;
    $definition();
    Processor::$currentExampleGroup = $previousExampleGroup;
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
    Processor::$currentExampleGroup->addInitializer($initializer);
}

function afterEach(Closure $finalizer): void
{
    Processor::$currentExampleGroup->addFinalizer($finalizer);
}

function it(string $description, Closure $definition): void
{
    Processor::$currentExampleGroup->addExample(new Example($description, $definition, Processor::$currentExampleGroup));
}

function xit(string $description, Closure $definition): void
{
}

function let(string $name, $value): void
{
    Processor::$currentExampleGroup->addSharedFixture($name, $value);
}

function subject($value): void
{
    let('subject', $value);
}
