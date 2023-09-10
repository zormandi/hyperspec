<?php
declare(strict_types=1);

namespace HyperSpec;

use Closure;

class ExampleGroup implements Verifiable
{
    /** @var (Example|ExampleGroup)[] */
    private array $examples = [];
    /** @var Closure[] */
    private array $initializers = [];
    /** @var Closure[] */
    private array $finalizers = [];
    private array $sharedFixtures = [];

    public function __construct(private string $description,
                                private readonly ?self $parent = null)
    {
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

    public function addExample(Example $example): void
    {
        $this->examples[] = $example;
    }

    public function addExampleGroup(self $exampleGroup): void
    {
        $this->examples[] = $exampleGroup;
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

    public function verify(): void
    {
        foreach ($this->examples as $example) {
            $example->verify();
        }
    }
}
