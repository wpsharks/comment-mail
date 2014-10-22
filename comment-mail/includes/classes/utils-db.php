<?php
/**
 * DB Utilities
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_db'))
	{
		/**
		 * DB Utilities
		 *
		 * @since 14xxxx First documented version.
		 */
		class utils_db extends abstract_base
		{
			/**
			 * @var \wpdb WP DB class reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			public $wp;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->wp = $GLOBALS['wpdb'];
			}

			/**
			 * Current DB prefix for this plugin.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return string Current DB table prefix.
			 */
			public function prefix()
			{
				return $this->wp->prefix.__NAMESPACE__.'_';
			}

			/**
			 * Typify result properties deeply.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $value Any value can be typified deeply.
			 *
			 * @return mixed Typified value.
			 */
			public function typify_deep($value)
			{
				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => &$_value)
					{
						if(is_array($_value) || is_object($_value))
							$_value = $this->typify_deep($_value);

						else if($this->is_integer_key($_key))
							$_value = (integer)$_value;

						else if($this->is_float_key($_key))
							$_value = (float)$_value;

						else $_value = (string)$_value;
					}
					unset($_key, $_value); // Housekeeping.
				}
				return $value; // Typified deeply.
			}

			/**
			 * Should an array/object key contain an integer value?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $key The input key to check.
			 *
			 * @return boolean TRUE if the key should contain an integer value.
			 */
			public function is_integer_key($key)
			{
				if(!$key || !is_string($key))
					return FALSE;

				$key = strtolower($key);

				if(in_array($key, array('user_initiated'), TRUE))
					return TRUE;

				if(in_array($key, array('id', 'time'), TRUE))
					return TRUE;

				if(preg_match('/_(?:id|time)$/', $key))
					return TRUE;

				return FALSE; // Default.
			}

			/**
			 * Should an array/object key contain a float value?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param mixed $key The input key to check.
			 *
			 * @return boolean TRUE if the key should contain a float value.
			 */
			public function is_float_key($key)
			{
				return FALSE; // Default; no float keys at this time.
			}

			/**
			 * Check DB engine compat. w/ fulltext indexes.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param string $sql Input SQL to check.
			 *
			 * @return string Output `$sql` w/ possible engine modification.
			 *    Only MySQL v5.6+ supports fulltext indexes with the InnoDB engine.
			 *    Otherwise, we use MyISAM for any table that includes a fulltext index.
			 *
			 * @note MySQL v5.6+ supports fulltext indexes w/ InnoDB.
			 *    See: <http://bit.ly/ZVeF42>
			 */
			public function fulltext_compat($sql)
			{
				if(!($sql = trim((string)$sql)))
					return $sql; // Empty.

				if(!preg_match('/^CREATE\s+TABLE\s+/i', $sql))
					return $sql; // Not applicable.

				if(!preg_match('/\bFULLTEXT\s+KEY\b/i', $sql))
					return $sql; // No fulltext index.

				if(!preg_match('/\bENGINE\=InnoDB\b/i', $sql))
					return $sql; // Not using InnoDB anyway.

				$mysql_version = $this->wp->db_version();
				if($mysql_version && version_compare($mysql_version, '5.6', '>='))
					return $sql; // MySQL v5.6+ supports fulltext indexes.

				return preg_replace('/\bENGINE\=InnoDB\b/i', 'ENGINE=MyISAM', $sql);
			}

			/**
			 * Comment status translator.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $status
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @return string `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @throws \exception If an unexpected status is encountered.
			 */
			public function comment_status__($status)
			{
				switch(trim(strtolower((string)$status)))
				{
					case '1':
					case 'approve':
					case 'approved':
						return 'approve';

					case '0':
					case '':
					case 'hold':
					case 'unapprove':
					case 'unapproved':
					case 'moderated':
						return 'hold';

					case 'trash':
					case 'post-trashed':
						return 'trash';

					case 'spam':
						return 'spam';

					case 'delete':
						return 'delete';

					default: // Throw exception on anything else.
						throw new \exception(sprintf(__('Unexpected comment status: `%1$s`.', $this->plugin->text_domain), $status));
				}
			}

			/**
			 * Post comment status translator.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $status
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `closed`, `close`).
			 *       - `1` (aka: `opened`, `open`).
			 *
			 * @return string `open`, `closed`.
			 *
			 * @throws \exception If an unexpected status is encountered.
			 */
			public function post_comment_status__($status)
			{
				switch(trim(strtolower((string)$status)))
				{
					case '1':
					case 'open':
					case 'opened':
						return 'open';

					case '0':
					case '':
					case 'close':
					case 'closed':
						return 'closed';

					default: // Throw exception on anything else.
						throw new \exception(sprintf(__('Unexpected post comment status: `%1$s`.', $this->plugin->text_domain), $status));
				}
			}
		}
	}
}