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

    public function addFinalizer(Closure $finalizer): void
    {
        $this->finalizers[] = $finalizer;
    }

    public function runInitializers(): void
    {
        if (!empty($this->parent)) {
            $this->parent->runInitializers();
        }
        foreach ($this->initializers as $initializer) {
            $initializer();
        }
    }

    public function addExample(Example $example)
    {
        $this->examples[] = $example;
    }

    public function addExampleGroup(self $exampleGroup)
    {
        $this->examples[] = $exampleGroup;
    }

    public function addSharedFixture(string $name, mixed $value)
    {
        $this->sharedFixtures[$name] = $value;
    }

    public function sharedFixtures(): array
    {
        $result = [];
        if (!empty($this->parent)) {
            $result = $this->parent->sharedFixtures();
        }

        return array_merge($result, $this->sharedFixtures);
    }

    public function verify(): void
    {
        foreach ($this->examples as $example) {
            $example->verify();
        }
    }
}
