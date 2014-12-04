<?php
/**
 * Shortcode; Var/Constant Conditionals
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\scvc_conds'))
	{
		/**
		 * Shortcode; Var/Constant Conditionals
		 *
		 * @since 141111 First documented version.
		 */
		class scvc_conds extends abs_base
		{
			/**
			 * @var string String we are working with.
			 *
			 * @since 141111 First documented version.
			 */
			protected $string;

			/**
			 * @var array Array of contextual vars.
			 *
			 * @since 141111 First documented version.
			 */
			protected $vars;

			/**
			 * @var array Array of tokens.
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
			 * @param array  $vars Any contextual vars needed by expressions.
			 */
			public function __construct($string, array $vars = array())
			{
				parent::__construct();

				$this->string = (string)$string;
				$this->vars   = (array)$vars;
				$this->tokens = array();

				# Accept shortcode variable keys too.

				foreach($this->vars as $_key => &$_value)
					$this->vars[trim($_key, '[]')] = &$_value;
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
				$this->tokenize_if_expressions();
				$this->tokenize_if_expression_ends();

				$this->strip_php_tags();
				$this->untokenize_all();
				$this->eval_expressions();

				return $this->string;
			}

			/**
			 * Expression tokenizer.
			 *
			 * @since 141111 First documented version.
			 */
			protected function tokenize_if_expressions()
			{
				$_this = $this; // Reference for closure.

				$this->string = preg_replace_callback(
					'/'. // Open regex; pattern delimiter.

					'\['. // Opening shortcode bracket.

					'(?P<if>if|elseif)'. // Conditional statement.
					'\s+'. // Followed by whitespace.

					'(?P<expression>[$\w \s !=<> \' &|]+)'. // Expression.

					'\]'. // Closing shortcode bracket.

					'/ix', // End of regex; pattern delimiter.

					function ($m) use ($_this)
					{
						$if         = $m['if'];
						$expression = $m['expression'];

						# Force `$` in var tests on the left side.

						$expression = preg_replace_callback(
							'/'. // Open regex; pattern delimiter.

							'(?<![!=<>])'. // Zero-length assertion; i.e. lookbehind.
							// Not preceded by comparison operator; indicating left side.

							'(^|[\s&|]+)'. // Beg., whitespace, or logical operators.
							'(\!?)'. // Possible negation symbol; i.e. `!`.
							'([a-z_]\w*)'. // Var/constant name.

							'/ix', // End of regex; pattern delimiter.

							function ($m) use ($_this)
							{
								if(!array_key_exists($m[3], $_this->vars))
									return $m[0]; // Var does not exist; assume constant.
								return $m[1].$m[2].'$'.$m[3]; // Force var; i.e. `$`.

							}, $expression); // End force `$` on vars.

						if(!preg_match(
							'/'. // Open regex; pattern delimiter.

							'^'. // Beginning of the string.

							'(?:'.
							'  \!?'. // Possible negation.
							'  \$?'. // Possible variable; i.e. `$`.
							'  [a-z_]\w*'. // Variable or constant name.
							'  \s*'. // Possible whitespace after var/constant.

							'  (?:'. // Begin logical|comparison operators.

							'     (?:'. // Begin logical operators.
							'        [&|]{2}'. // `&&`, `||` operators.
							'        \s*'. // Any trailing whitespace after.
							'     )'. // End logical operators.

							'     |'. // Or, comparision operators.

							'     (?:'. // Begin optional comparison.
							'        [!=<>]{1,3}\s*'. // 1-3 operators + whitespace.
							'        (?:TRUE|FALSE|NULL|[0-9.]|\'[^\']*?\')'. // Value.
							'        \s*'. // Any trailing whitespace after.

							# Also consider there could be `!= 1`; followed by `&&`, `||`.

							'        (?:'. // Begin trailing logical operators.
							'           [&|]{2}'. // `&&`, `||` operators.
							'           \s*'. // Any trailing whitespace after.
							'        )?'. // End trailing logical operators.

							'     )'. // End comparision operators.

							'  )?'. // End logical|comparison operators.

							')+'. // One or more repetitions.

							'$'. // End of the string.

							'/ix', $expression) // End of regex; pattern delimiter.

						) // We only allow variables to be tested by shortcodes; against integers, floats, strings, booleans.
							throw new \exception(__('Invalid shortcode conditional expression.', $_this->plugin->text_domain));

						$token                 = count($_this->tokens);
						$_this->tokens[$token] = '<?php '.$if.'('.$expression.'): ?>';

						return '{token:'.$token.'}'; # e.g. {token:123}

					}, $this->string);
			}

			/**
			 * Expression ends tokenizer.
			 *
			 * @since 141111 First documented version.
			 */
			protected function tokenize_if_expression_ends()
			{
				$_this = $this; // Reference for closure.

				$this->string = preg_replace_callback(
					'/'. // Open regex; pattern delimiter.

					'\['. // Opening shortcode bracket.

					'(?P<end>else|endif|\/if)'. // Conditional end.

					'\]'. // Closing shortcode bracket.

					'/ix', // End of regex; pattern delimiter.

					function ($m) use ($_this)
					{
						$token = count($_this->tokens);

						if(strcasecmp($m['end'], '/if') === 0)
							$m['end'] = 'endif'; // Force PHP syntax.

						$_this->tokens[$token] = '<?php '.$m['end']. // e.g. `else`, `endif`.
						                         (stripos($m['end'], 'end') === 0 ? ';' : ':').
						                         ' ?>';
						return '{token:'.$token.'}'; # e.g. {token:456}

					}, $this->string);
			}

			/**
			 * Strips PHP tags.
			 *
			 * @since 141111 First documented version.
			 */
			protected function strip_php_tags()
			{
				$this->string = $this->plugin->utils_string->strip_php_tags($this->string);
			}

			/**
			 * Untokenize all previously generated tokens.
			 *
			 * @since 141111 First documented version.
			 */
			protected function untokenize_all()
			{
				$_this = $this; // Reference for closure.

				$this->string = preg_replace_callback('/\{token\:(?P<token>[0-9]+)\}/i', function ($m) use ($_this)
				{
					if(isset($_this->tokens[$m['token']]))
						return (string)$_this->tokens[$m['token']];
					return ''; // Default return value.

				}, $this->string);
			}

			/**
			 * Evaluate PHP generated by shortcodes.
			 *
			 * @since 141111 First documented version.
			 */
			protected function eval_expressions()
			{
				$this->string = $this->plugin->utils_php->evaluate($this->string, $this->vars);
			}
		}
	}
}