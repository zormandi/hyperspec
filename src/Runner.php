<?php
declare(strict_types=1);

namespace HyperSpec;

class Runner
{
    public function __construct(private readonly string $pattern)
    {
    }

    public function verifySpecs(): void
    {
        Processor::initialize();
        eval(file_get_contents($this->pattern));
        $this->verifyExampleGroup(Processor::rootExampleGroup(), -1);
    }

    public function verifyExampleGroup(ExampleGroup $exampleGroup, int $depth = 0): void
    {
        $this->printDescription($depth, $exampleGroup->description);
        foreach ($exampleGroup->examplesAndGroups() as $item) {
            if ($item instanceof ExampleGroup) {
                $this->verifyExampleGroup($item, $depth + 1);
            } else {
                $this->verifyExample($item, $depth + 1);
            }
        }
    }

    private function verifyExample(Example $example, int $depth): void
    {
        $this->printDescription($depth, $example->description);
        $example->verify();
    }

    public function printDescription(int $depth, string $description): void
    {
        if (empty($description)) {
            return;
        }

        echo str_repeat("  ", $depth) . $description . "\n";
    }
}
