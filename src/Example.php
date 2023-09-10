<?php
declare(strict_types=1);

namespace HyperSpec;

use Closure;
use RuntimeException;

class Example implements Verifiable
{
    private array $fixtures = [];

    public function __construct(private string $description,
                                private readonly Closure $definition,
                                private readonly ExampleGroup $parent)
    {
    }

    public function verify(): void
    {
        $this->parent->runInitializers();
        $this->fixtures = $this->parent->sharedFixtures();
        $this->executeClosureWithinExampleContext($this->definition);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->fixtures[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        if (!array_key_exists($name, $this->fixtures)) {
            throw new RuntimeException("Referencing unknown shared fixture: '$name'");
        }

        if ($this->fixtures[$name] instanceof Closure) {
            $this->fixtures[$name] = $this->executeClosureWithinExampleContext($this->fixtures[$name]);
        }
        return $this->fixtures[$name];
    }

    private function executeClosureWithinExampleContext(Closure $closure): mixed
    {
        $closureWithinContext = $closure->bindTo($this);
        return $closureWithinContext();
    }
}
