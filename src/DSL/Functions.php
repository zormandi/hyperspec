<?php
declare(strict_types=1);

namespace HyperSpec\DSL;

use Closure;
use HyperSpec\Processor;

function describe(string $description, Closure $definition): void
{
    Processor::addExampleGroup($description, $definition);
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
    Processor::addInitializer($initializer);
}

function afterEach(Closure $finalizer): void
{
    Processor::addFinalizer($finalizer);
}

function it(string $description, Closure $definition): void
{
    Processor::addExample($description, $definition);
}

function xit(string $description, Closure $definition): void
{
}

function let(string $name, $value): void
{
    Processor::addSharedFixture($name, $value);
}

function subject($value): void
{
    let('subject', $value);
}
