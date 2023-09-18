Feature: Example groups
  Examples (tests) in HyperSpec are used to define the expected behavior of a piece of code.
  Examples are organized into logical groups, called example groups. You can define an example group
  with `describe` and create an example in the group with `it`.

  Examples that have the same context (text fixture) can be further organized into subgroups, which
  can be defined by using `context`.

  Scenario: Example group with one example
    Given a file named "single_example_group_spec.php" with:
    """
    describe('Some feature', function () {
        it('does something', function () {
        });
    });
    """
    When I run "hyperspec single_example_group_spec.php"
    Then the output should contain:
    """
    Some feature
      does something
    """

  Scenario: Example group with multiple examples using context
    Given a file named "nested_example_groups_spec.php" with:
    """
    describe('Some feature', function () {
        it('does something', function () {
        });

        context('in some context', function () {
            it('does one thing', function () {
            });

            it('does another thing as well', function () {
            });
        });

        context('in some other context', function () {
            it('does some other thing', function () {
            });
        });
    });
    """
    When I run "hyperspec nested_example_groups_spec.php"
    Then the output should contain:
    """
    Some feature
      does something
      in some context
        does one thing
        does another thing as well
      in some other context
        does some other thing
    """
