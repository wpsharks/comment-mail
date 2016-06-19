<?php
/**
 * Shortcode Conditionals.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Shortcode Conditionals.
 *
 * @since 141111 First documented version.
 */
class ScConditionals extends AbsBase
{
    /**
     * @type string String we are working with.
     *
     * @since 141111 First documented version.
     */
    protected $string;

    /**
     * @type array Array of contextual vars.
     *
     * @since 141111 First documented version.
     */
    protected $vars;

    /**
     * @type array Array of tokens.
     *
     * @since 141111 First documented version.
     */
    protected $tokens;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param string $string String to parse.
     * @param array  $vars   Any contextual vars needed by expressions.
     */
    public function __construct($string, array $vars = [])
    {
        parent::__construct();

        $this->string = (string) $string;
        $this->vars   = (array) $vars;
        $this->tokens = [];

        # Accept shortcode variable keys too.

        foreach ($this->vars as $_key => &$_value) {
            $this->vars[trim($_key, '[]')] = &$_value;
        }
        unset($_key, $_value); // Housekeeping.
    }

    /**
     * String parser.
     *
     * @since 141111 First documented version.
     *
     * @return string Parsed string.
     */
    public function parse()
    {
        $this->tokenizeIfExpressions();
        $this->tokenizeIfExpressionEnds();

        $this->striPhpTags();
        $this->untokenizeAll();
        $this->evalExpressions();

        return $this->string;
    }

    /**
     * Expression tokenizer.
     *
     * @since 141111 First documented version.
     */
    protected function tokenizeIfExpressions()
    {
        $_this = $this; // Reference for closure.

        $this->string = preg_replace_callback(
            '/'.// Open regex; pattern delimiter.

            '\['.// Opening shortcode bracket.

            '(?P<if>if|elseif)'.// Conditional statement.
            '\s+'.// Followed by whitespace.

            '(?P<expression>[$\w \s !=<> \' &|]+)'.// Expression.

            '\]'.// Closing shortcode bracket.

            '/ix', // End of regex; pattern delimiter.
            //
            function ($m) use ($_this) {
                $if = $m['if'];
                $expression = $m['expression'];

                # Force `$` in var tests on the left side.

                $expression = preg_replace_callback(
                    '/'.// Open regex; pattern delimiter.

                    '(?<![!=<>])'.// Zero-length assertion; i.e. lookbehind.
                    // Not preceded by comparison operator; indicating left side.

                    '(^|[\s&|]+)'.// Beg., whitespace, or logical operators.
                    '(\!?)'.// Possible negation symbol; i.e. `!`.
                    '([a-z_]\w*)'.// Var/constant name.

                    '/ix', // End of regex; pattern delimiter.
                    //
                    function ($m) use ($_this) {
                        if (!array_key_exists($m[3], $_this->vars)) {
                            return $m[0];
                        } // Var does not exist; assume constant.
                        return $m[1].$m[2].'$'.$m[3]; // Force var; i.e. `$`.

                    },
                    $expression
                ); // End force `$` on vars.

                if (!preg_match(
                    '/'.// Open regex; pattern delimiter.

                    '^'.// Beginning of the string.

                    '(?:'.
                    '  \!?'.// Possible negation.
                    '  \$?'.// Possible variable; i.e. `$`.
                    '  [a-z_]\w*'.// Variable or constant name.
                    '  \s*'.// Possible whitespace after var/constant.

                    '  (?:'.// Begin logical|comparison operators.

                    '     (?:'.// Begin logical operators.
                    '        [&|]{2}'.// `&&`, `||` operators.
                    '        \s*'.// Any trailing whitespace after.
                    '     )'.// End logical operators.

                    '     |'.// Or, comparision operators.

                    '     (?:'.// Begin optional comparison.
                    '        [!=<>]{1,3}\s*'.// 1-3 operators + whitespace.
                    '        (?:TRUE|FALSE|NULL|[0-9.]|\'[^\']*?\')'.// Value.
                    '        \s*'.// Any trailing whitespace after.

                    # Also consider there could be `!= 1`; followed by `&&`, `||`.

                    '        (?:'.// Begin trailing logical operators.
                    '           [&|]{2}'.// `&&`, `||` operators.
                    '           \s*'.// Any trailing whitespace after.
                    '        )?'.// End trailing logical operators.

                    '     )'.// End comparision operators.

                    '  )?'.// End logical|comparison operators.

                    ')+'.// One or more repetitions.

                    '$'.// End of the string.

                    '/ix',
                    $expression
                ) // End of regex; pattern delimiter.

                ) { // We only allow variables to be tested by shortcodes; against integers, floats, strings, booleans.
                    throw new \exception(__('Invalid shortcode conditional expression.', 'comment-mail'));
                }
                $token = count($_this->tokens);
                $_this->tokens[$token] = '<?php '.$if.'('.$expression.'): ?>';

                return '{token:'.$token.'}'; # e.g. {token:123}

            },
            $this->string
        );
    }

    /**
     * Expression ends tokenizer.
     *
     * @since 141111 First documented version.
     */
    protected function tokenizeIfExpressionEnds()
    {
        $_this = $this; // Reference for closure.

        $this->string = preg_replace_callback(
            '/'.// Open regex; pattern delimiter.

            '\['.// Opening shortcode bracket.

            '(?P<end>else|endif|\/endif|\/if)'.// Ends.

            '\]'.// Closing shortcode bracket.

            '/ix', // End of regex; pattern delimiter.
            //
            function ($m) use ($_this) {
                switch (($end = strtolower($m['end']))) {
                    case 'else':
                        $end = 'else:';
                        break; // Break switch.

                    case 'endif':
                    case '/endif':
                    case '/if':
                        $end = 'endif;';
                        break; // Break switch.
                }
                $token = count($_this->tokens);
                $_this->tokens[$token] = '<?php '.$end.' ?>';

                return '{token:'.$token.'}'; # e.g. {token:456}

            },
            $this->string
        );
    }

    /**
     * Strips PHP tags.
     *
     * @since 141111 First documented version.
     */
    protected function striPhpTags()
    {
        $this->string = $this->plugin->utils_string->stripPhpTags($this->string);
    }

    /**
     * Untokenize all previously generated tokens.
     *
     * @since 141111 First documented version.
     */
    protected function untokenizeAll()
    {
        $_this = $this; // Reference for closure.

        $this->string = preg_replace_callback(
            '/\{token\:(?P<token>[0-9]+)\}/i',
            function ($m) use ($_this) {
                if (isset($_this->tokens[$m['token']])) {
                    return (string) $_this->tokens[$m['token']];
                }
                return ''; // Default return value.

            },
            $this->string
        );
    }

    /**
     * Evaluate PHP generated by shortcodes.
     *
     * @since 141111 First documented version.
     */
    protected function evalExpressions()
    {
        $this->string = @$this->plugin->utils_php->evaluate($this->string, $this->vars);
    }
}
