## Indentation

Use an indent of 4 spaces, with no tabs. This helps to avoid problems with diffs, patches, CVS history and annotations.

## Naming Convention

### Classes and Namespaces

Classes should be named using the camel case style with the exception of the first character which should be uppercase.

Namespaces are always lowercase.

### Variables and Functions

Variables and functions should be named using the camel case style.

### Constants

Constants should always be all-uppercase, with underscores to separate words. In most cases, it's advised to use a prefix to indicate what kind of constant it is (eg. PARAM_XXX)

_NOTE: Use meaningfull names. Sometimes it's better to use a longer, meaningful name, then a shorter meaningless name._

## Namespace Definitions

Namespaces are ordered alphabetically with a deeper namespace at the top. Bundle namespaces which are the same till 2 levels deep. Separate bundled namespaces by a empty line.

example:

    namespace ride\app;

    use ride\application\Application;

    use ride\library\validation\exception\ValidationException;
    use ride\library\Timer;

    use \Exception;


## Class Definitions

Class declarations have their opening brace on the same line. An empty line goes after the definition and before the end of the class. Functions have an empty line between:

    class FooBar {

        function method1() {
            ...
        }

        function method2() {
            ...
        }

    }


## Function Definitions

Function declarations have their opening brace on the same line. Arguments with default values go at the end of the argument list. Always attempt to return a meaningful value from a function if one is appropriate.

    function fooFunction($arg1, $arg2 = '') {
        ...
    }

## Control Structures

These include if, for, while, switch, etc. Here is an example of an if statement:

    if (condition1 || condition2) {
        ...
    } elseif ((condition3 || condition4) && condition5) {
        ...
    } else {
       ...
    }

Control statements should have one space between the control keyword and opening parenthesis, to distinguish them from function calls.

Always use curly braces even in situations where they are technically optional. Having them increases readability and decreases the likelihood of logic errors being introduced when new lines are added.

For switch statements:

    switch (condition) {
        case 1:
            ...
            break;
        case 2:
            ...
            break;
        default:
            ...
            break;
    }


## Function Calls

Functions should be called with no spaces between the function name, the opening parenthesis, and the first parameter; spaces between commas and each parameter, and no space between the last parameter, the closing parenthesis, and the semicolon. Here's an example:

    $var = foo($bar, $baz, $quux);

## PHP Code Tags

Always use _&lt;?php ?> to delimit PHP code, not the <? ?> shorthand. If you are writing a pure PHP file, don't close this tag. PHP doesn't bother and there is a smaller chance for misfortunate output.
