<?php
/**
 * PHP Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_php'))
	{
		/**
		 * PHP Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_php extends abs_base
		{
			/**
			 * PHP's language constructs.
			 *
			 * @var array PHP's language constructs.
			 *    Keys are currently unimportant. Subject to change.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $constructs = array(
				'die'             => 'die',
				'echo'            => 'echo',
				'empty'           => 'empty',
				'exit'            => 'exit',
				'eval'            => 'eval',
				'include'         => 'include',
				'include_once'    => 'include_once',
				'isset'           => 'isset',
				'list'            => 'list',
				'require'         => 'require',
				'require_once'    => 'require_once',
				'return'          => 'return',
				'print'           => 'print',
				'unset'           => 'unset',
				'__halt_compiler' => '__halt_compiler'
			);

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Evaluates PHP code.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $___string String (possibly containing PHP tags).
			 *    If `$pure_php` is TRUE; this should NOT have PHP tags.
			 *
			 * @param array   $___vars An array of variables to bring into the scope of evaluation.
			 *    This is optional. It defaults to an empty array.
			 *
			 * @param boolean $___no_tags Defaults to a FALSE value.
			 *    If this is TRUE, the input `$string` should NOT include PHP tags.
			 *
			 * @return string Output string after having been evaluated by PHP.
			 *
			 * @throws \exception If unable to evaluate.
			 */
			public function evaluate($___string, $___vars = array(), $___no_tags = FALSE)
			{
				$___string = trim((string)$___string);

				if(!isset($___string[0]))
					return ''; // Empty.

				if($___vars) // Extract variables.
					extract($___vars, EXTR_PREFIX_SAME, 'xps');

				if($this->is_possible('eval'))
				{
					ob_start();

					if($___no_tags)
						eval($___string);
					else eval('?>'.$___string.'<?php ');

					return ob_get_clean();
				}
				throw new \exception(__('The PHP `eval()` function (an application requirement) has been disabled on this server. Please check with your hosting provider to resolve this issue and have the PHP `eval()` function enabled.', $this->plugin->text_domain).

				                     // The rest of this explanation is not translatable; allowing us to keep it on multiple lines.
				                     ' The use of `eval()` in this software is limited to areas where it is absolutely necessary to achieve a desired functionality.'.
				                     ' For instance, where PHP code is supplied by a site owner (or by their developer) to achieve advanced customization through a UI panel. This can be evaluated at runtime to allow for the inclusion of PHP conditionals or dynamic values.'.
				                     ' In cases such as these, the PHP `eval()` function serves a valid/useful purpose. This does NOT introduce a vulnerability, because the code being evaluated has actually been introduced by the site owner (i.e. the PHP code can be trusted in this case).'.
				                     ' This software may also use `eval()` to generate dynamic classes and/or API functions for developers; where the use of `eval()` again serves a valid/useful purpose; and where the underlying code was packaged by the software vendor (i.e. the PHP code can be trusted).'
				);
			}

			/**
			 * Is a particular function, static method, or PHP language construct possible?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string  $fsmlc Function, static method, or a PHP language construct.
			 *
			 * @param boolean $no_cache Defaults to a FALSE value.
			 *    TRUE to avoid a potentially cached value.
			 *
			 * @return boolean TRUE if (in `$this->constructs` || `is_callable()` || `function_exists()`).
			 *    Iff NOT disabled at runtime via `ini_get('disable_functions')` or with the Suhosin extension.
			 */
			public function is_possible($fsmlc, $no_cache = FALSE)
			{
				$fsmlc = ltrim(strtolower((string)$fsmlc), '\\');

				if(!is_null($possible = &$this->static_key(__FUNCTION__, $fsmlc)) && !$no_cache)
					return $possible; // Cached this already.

				if($fsmlc // Do we even have something to check?

				   && ( // A language construct, or callable.
						in_array($fsmlc, $this->constructs, TRUE)
						|| is_callable($fsmlc) || function_exists($fsmlc)
					)
				   // And only if it has not been disabled in some way.
				   && !in_array($fsmlc, $this->disabled_functions(), TRUE)

				) return ($possible = TRUE);

				return ($possible = FALSE);
			}

			/**
			 * Gets all disabled PHP functions.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return array An array of all disabled functions, else an empty array.
			 */
			protected function disabled_functions()
			{
				if(!is_null($disabled = &$this->static_key(__FUNCTION__)))
					return $disabled; // Cached this already.
				$disabled = array(); // Initialize.

				if(!function_exists('ini_get'))
					return $disabled; // Not possible.

				if(($_ini_val = trim(strtolower(ini_get('disable_functions')))))
					$disabled = array_merge($disabled, preg_split('/[\s;,]+/', $_ini_val, NULL, PREG_SPLIT_NO_EMPTY));
				unset($_ini_val); // Housekeeping.

				if(($_ini_val = trim(strtolower(ini_get('suhosin.executor.func.blacklist')))))
					$disabled = array_merge($disabled, preg_split('/[\s;,]+/', $_ini_val, NULL, PREG_SPLIT_NO_EMPTY));
				unset($_ini_val); // Housekeeping.

				if(filter_var(ini_get('suhosin.executor.disable_eval'), FILTER_VALIDATE_BOOLEAN))
					$disabled = array_merge($disabled, array('eval'));

				return $disabled;
			}
		}
	}
}