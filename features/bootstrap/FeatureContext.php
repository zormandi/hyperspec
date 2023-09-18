<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private string $rootDir;
    private string $specDir;
    private string $commandOutput;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->rootDir = dirname(__DIR__, 2);
        $this->specDir = $this->rootDir . '/temp';
    }

    /**
     * @Given a file named :filename with:
     */
    public function aFileNamedWith(string $filename, PyStringNode $string)
    {
        file_put_contents($this->specFilepath($filename), <<<SPEC
            use function HyperSpec\DSL\{afterEach, beforeEach, describe, context, it, subject, let};
            $string
            SPEC
        );
    }

    /**
     * @When I run :command
     */
    public function iRun(string $command)
    {
        list($command, $filename) = explode(' ', $command);
        $output = [];
        exec("$this->rootDir/bin/$command {$this->specFilepath($filename)}", $output);
        $this->commandOutput = join("\n", $output);
    }

    /**
     * @Then the output should contain:
     */
    public function theOutputShouldContain(PyStringNode $string)
    {
        Assert::assertStringContainsString($string, $this->commandOutput);
    }

    private function specFilepath($filename): string
    {
        return $this->specDir . '/' . $filename;
    }
}
