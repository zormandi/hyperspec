<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';

require_once 'src/DSL/Functions.php';

use HyperSpec\Processor;
use function HyperSpec\DSL\{afterEach, beforeEach, describe, context, it, subject, let};

Processor::initialize();

/***************
 * Expectation *
 ***************/
class Expectation
{
    private bool $isNegated = false;

    public function __construct(private $actual)
    {
    }

    public function not(): self
    {
        $this->isNegated = true;

        return $this;
    }

    public function toEqual($expected): void
    {
        if ($this->actual != $expected) {
            throw new RuntimeException("Expected $this->actual to equal $expected");
        }
    }
}

function expect($actual): Expectation
{
    return new Expectation($actual);
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

    afterEach(function () {
        echo 'Goodbye ';
    });

    describe('->method', function () {
        let('foo', 1);

        beforeEach(function () {
            echo "world!\n";
        });

        afterEach(function () {
            echo "cruel world!\n";
        });

        it('can access shared fixtures via $this', function () {
            expect($this->foo)->toEqual(1);
        });

        context('when the fixture is changed', function () {
            let('foo', 2);

            beforeEach(function () {
                echo "Just this twice!\n";
            });

            it('uses the changed value', function () {
                expect($this->foo)->toEqual(2);
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

Processor::executeTests();
