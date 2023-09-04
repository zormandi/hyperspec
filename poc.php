<?php
declare(strict_types=1);

/****************************
 * Implementation - classes *
 ****************************/
interface Verifiable
{
    public function verify(): void;
}

class ExampleGroup implements Verifiable
{
    /** @var (Example|ExampleGroup)[] */
    private array $examples = [];
    /** @var Closure[] */
    private array $initializers = [];
    private array $sharedFixtures = [];

    public function __construct(private string $description, private ?self $parent = null)
    {
    }

    public function addInitializer(Closure $initializer): void
    {
        $this->initializers[] = $initializer;
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

class Example implements Verifiable
{
    private array $fixtures = [];

    public function __construct(private string $description, private Closure $definition, private ExampleGroup $parent)
    {
    }

    public function verify(): void
    {
        $this->parent->runInitializers();
        $this->fixtures = $this->parent->sharedFixtures();
        $testCase = $this->definition->bindTo($this);
        $testCase();
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
            $fixture = $this->fixtures[$name]->bindTo($this);
            $this->fixtures[$name] = $fixture();
        }
        return $this->fixtures[$name];
    }
}

/************************
 * Implementation - DSL *
 ************************/

/** @var ExampleGroup[] $topLevelExampleGroups */
$topLevelExampleGroups = [];
/** @var ?ExampleGroup $currentExampleGroup */
$currentExampleGroup = null;

function xdescribe(string $description, Closure $definition): void
{
}

function describe(string $description, Closure $definition): void
{
    global $topLevelExampleGroups, $currentExampleGroup;

    $exampleGroup = new ExampleGroup($description, $currentExampleGroup);
    if (empty($currentExampleGroup)) {
        $topLevelExampleGroups[] = $exampleGroup;
    } else {
        $currentExampleGroup->addExampleGroup($exampleGroup);
    }
    $previousExampleGroup = $currentExampleGroup;
    $currentExampleGroup = $exampleGroup;
    $definition();
    $currentExampleGroup = $previousExampleGroup;
}

//function xcontext(string $description, Closure $definition): void {}
function context(string $description, Closure $definition): void
{
    describe($description, $definition);
}

function beforeEach(Closure $initializer): void
{
    global $currentExampleGroup;
    $currentExampleGroup->addInitializer($initializer);
}

//function xit(string $description, Closure $definition): void {}
function it(string $description, Closure $definition): void
{
    global $currentExampleGroup;
    $currentExampleGroup->addExample(new Example($description, $definition, $currentExampleGroup));
}

function let(string $name, mixed $value): void
{
    global $currentExampleGroup;
    $currentExampleGroup->addSharedFixture($name, $value);
}

function subject(mixed $value): void
{
    let('subject', $value);
}

/***************
 * Expectation *
 ***************/
class Expectation
{
    private bool $isNegated = false;

    public function __construct(private mixed $object)
    {
    }

    public function not(): self
    {
        $this->isNegated = true;

        return $this;
    }

    public function toEqual(mixed $expectedValue): void
    {
        if ($this->object != $expectedValue) {
            throw new RuntimeException("Expected $this->object to equal $expectedValue");
        }
    }
}

function expect(mixed $object): Expectation
{
    return new Expectation($object);
}

/************
 * Examples *
 ************/

class Calculator
{
    public static function add(int $a, $b)
    {
        return $a + $b;
    }
}

describe(Calculator::class, function () {
    describe('::add', function () {
        $testCases = [[1, 2, 3], [5, 5, 10], [10, 1, 11]];

        foreach ($testCases as list($number, $otherNumber, $expectedSum)) {
            it('returns the sum of the two numbers', function () use ($number, $otherNumber, $expectedSum) {
                $result = Calculator::add($number, $otherNumber);

                expect($result)->toEqual($expectedSum);
            });
        }
    });

    describe('::add', function () {
        subject(fn() => Calculator::add($this->number, $this->otherNumber));

        $testCases = [[1, 2, 3], [5, 5, 10], [10, 1, 11]];

        foreach ($testCases as list($number, $otherNumber, $expectedSum)) {
            context("for $number + $otherNumber", function () use ($number, $otherNumber, $expectedSum) {
                let('number', $number);
                let('otherNumber', $otherNumber);

                it('returns the sum of the two numbers', function () use ($expectedSum) {
                    expect($this->subject)->toEqual($expectedSum);
                });
            });
        }
    });
});

describe('Test', function () {
    beforeEach(function () {
        echo 'Hello ';
    });

    describe('->method', function () {
        let('foo', 1);

        beforeEach(function () {
            echo "world!\n";
        });

        it('can access shared fixtures via $this', function () {
            expect($this->foo)->toEqual(1);
        });

        context('when the fixture is changed', function () {
            let('foo', 2);

            beforeEach(function () {
                echo "Just this once!\n";
            });

            it('uses the changed value', function () {
                expect($this->foo)->toEqual(2);
            });
        });

        context('when the shared fixture is a closure', function () {
            let('foo', fn() => 2 * 3);

            it('uses the result of the closure', function () {
                expect($this->foo)->toEqual(6);
            });
        });

        context('when the shared fixtures reference each other', function () {
            let('foo', 2);
            let('bar', 3);
            let('baz', fn() => $this->foo * $this->bar);

            it('uses the result of the closure', function () {
                expect($this->baz)->toEqual(6);
            });
        });
    });
});

foreach ($topLevelExampleGroups as $exampleGroup) {
    $exampleGroup->verify();
}
