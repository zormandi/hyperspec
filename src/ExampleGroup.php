<?php
declare(strict_types=1);

namespace HyperSpec;

use Closure;

class ExampleGroup
{
    /** @var Example[] */
    private array $examples = [];
    /** @var ExampleGroup[] */
    private array $exampleGroups = [];
    /** @var (ExampleGroup|Example)[] */
    private array $examplesAndGroups = [];
    /** @var Closure[] */
    private array $initializers = [];
    /** @var Closure[] */
    private array $finalizers = [];
    private array $sharedFixtures = [];

    public function __construct(public readonly string $description,
                                private readonly ?self $parent = null)
    {
    }

    public function addExample(Example $example): void
    {
        $this->examples[] = $example;
        $this->examplesAndGroups[] = $example;
    }

    public function examples(): array
    {
        return $this->examples;
    }

    public function addExampleGroup(ExampleGroup $exampleGroup): void
    {
        $this->exampleGroups[] = $exampleGroup;
        $this->examplesAndGroups[] = $exampleGroup;
    }

    public function exampleGroups(): array
    {
        return $this->exampleGroups;
    }

    public function examplesAndGroups(): array
    {
        return $this->examplesAndGroups;
    }

    public function addInitializer(Closure $initializer): void
    {
        $this->initializers[] = $initializer;
    }

    public function runInitializers(Example $context): void
    {
        if (!empty($this->parent)) {
            $this->parent->runInitializers($context);
        }

        foreach ($this->initializers as $initializer) {
            $initializer->bindTo($context)();
        }
    }

    public function addFinalizer(Closure $finalizer): void
    {
        $this->finalizers[] = $finalizer;
    }

    public function runFinalizers(Example $context): void
    {
        if (!empty($this->parent)) {
            $this->parent->runFinalizers($context);
        }

        foreach ($this->finalizers as $finalizer) {
            $finalizer->bindTo($context)();
        }
    }

    public function addSharedFixture(string $name, $value): void
    {
        $this->sharedFixtures[$name] = $value;
    }

    public function sharedFixtures(): array
    {
        $parentFixtures = [];
        if (!empty($this->parent)) {
            $parentFixtures = $this->parent->sharedFixtures();
        }

        return array_merge($parentFixtures, $this->sharedFixtures);
    }
}
