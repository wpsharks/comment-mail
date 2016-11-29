<?php
/**
 * Template.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * Template.
 *
 * @since 141111 First documented version.
 */
class Template extends AbsBase
{
    /**
     * @var string Type of template.
     *
     * @since 141111 First documented version.
     */
    protected $type;

    /**
     * @var string Template file.
     *
     * @since 141111 First documented version.
     */
    protected $file;

    /**
     * @var string Template file path.
     *
     * @since 161118 Enhancing templates.
     */
    protected $file_path;

    /**
     * @var string Snippet sub-directory.
     *
     * @since 141111 First documented version.
     */
    protected $snippet_sub_dir;

    /**
     * @var bool Force default template?
     *
     * @since 141111 First documented version.
     */
    protected $force_default;

    /**
     * @var string Template file contents.
     *
     * @since 141111 First documented version.
     */
    protected $file_contents;

    /**
     * @var array Current vars.
     *
     * @since 141111 First documented version.
     */
    protected $current_vars;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param string      $file          Template file.
     * @param string|null $type          Template type. Defaults to an empty string.
     *                                   An empty string (or `NULL`) indicates the currently configured type.
     * @param bool        $force_default Force default template?
     *
     * @throws \exception If `$file` is empty.
     */
    public function __construct($file, $type = '', $force_default = false)
    {
        parent::__construct();

        if ($type) { // Use a specific type?
            $this->type = trim(strtolower((string) $type));
        }
        if (!$this->type) {
            $this->type = $this->plugin->options['template_type'];
        }
        if (!$this->type) { // Empty type property?
            throw new \exception(__('Empty type.', 'comment-mail'));
        }
        $this->file = (string) $file; // Initialize.
        $this->file = $this->plugin->utils_string->trimDeep($this->file, '', '/');
        $this->file = $this->plugin->utils_fs->nSeps($this->file);

        if (!$this->file) { // Empty file property?
            throw new \exception(__('Empty file.', 'comment-mail'));
        }
        $this->force_default = (bool) $force_default; // Before `getFilePath()`.

        $this->file_path       = $this->getFilePath();
        $this->snippet_sub_dir = dirname($this->file).'/snippet';

        $this->file_contents = $this->getFileContents();
        $this->current_vars  = []; // Initialize.
    }

    /**
     * Public access to file; relative path.
     *
     * @since 141111 First documented version.
     *
     * @return string Template file; relative path.
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Public access to file path.
     *
     * @since 161118 Enhancing templates.
     *
     * @return string Template file path.
     */
    public function filePath()
    {
        return $this->file_path;
    }

    /**
     * Public access to file contents.
     *
     * @since 141111 First documented version.
     *
     * @return string Unparsed template file contents.
     */
    public function fileContents()
    {
        return $this->file_contents;
    }

    /**
     * Parse template file.
     *
     * @since 141111 First documented version.
     *
     * @param array $vars Optional array of variables to parse.
     *
     * @return string Parsed template file contents.
     */
    public function parse(array $vars = [])
    {
        $vars['plugin'] = plugin(); // Plugin class.

        $vars['template'] = $this; // Template reference.

        if (strpos($this->file, 'site/') === 0) {
            $vars = array_merge($vars, $this->siteVars($vars));
        }
        if (strpos($this->file, 'email/') === 0) {
            $vars = array_merge($vars, $this->emailVars($vars));
        }
        $this->current_vars = &$vars; // Setup current variables.
        $is_php             = $this->plugin->utils_fs->extension($this->file) === 'php';

        if ($is_php && $this->file_path) {
            return trim($this->plugin->utils_php->getIsolatedInclude($this->file_path, $vars));
        } elseif ($is_php && $this->file_contents) {
            return trim($this->plugin->utils_php->evaluate($this->file_contents, $vars));
        }
        return trim($this->file_contents); // Default behavior.
    }

    /**
     * Parse snippet file.
     *
     * @since 141111 First documented version.
     *
     * @param string $file            File path, relative to snippet sub-directory.
     * @param array  $shortcodes_vars Optional array shortcodes/variables.
     *
     * @return string Parsed snippet file contents.
     */
    public function snippet($file, array $shortcodes_vars = [])
    {
        $file = (string) $file; // Force string.
        $file = $this->plugin->utils_string->trimDeep($file, '', '/');
        $file = $this->plugin->utils_fs->nSeps($file);

        $shortcodes_vars = // Merge w/ current vars.
            array_merge($this->current_vars, $shortcodes_vars);
        $shortcodes = []; // Initialize.

        foreach ($shortcodes_vars as $_key => $_value) {
            if (is_string($_key) && preg_match('/^\[(?:[^\s\[\]]+?)\]$/', $_key)) {
                if (is_string($_value) || is_integer($_value) || is_float($_value)) {
                    $shortcodes[$_key] = (string) $_value;
                }
            }
        } // unset($_key, $_value); // Housekeeping.

        $snippet = trim($this->snippetFileContents($file));

        $sc_conditionals = new ScConditionals($snippet, $shortcodes_vars);
        $snippet         = $sc_conditionals->parse(); // Evaluates [if expression] logic.

        $snippet = str_ireplace(array_keys($shortcodes), array_values($shortcodes), $snippet);
        $snippet = do_shortcode($snippet); // Support WordPress shortcodes also.

        return $snippet; // Final snippet output.
    }

    /**
     * Site template vars.
     *
     * @since 141111 First documented version.
     *
     * @param array $vars Optional array of variables to parse.
     *
     * @return array An array of all site template vars.
     */
    protected function siteVars(array $vars = [])
    {
        if (strpos($this->file, 'site/header') === 0) {
            return []; // Prevent infinite loop.
        }
        if (strpos($this->file, 'site/footer') === 0) {
            return []; // Prevent infinite loop.
        }
        // Parent template reference.

        $vars['parent_template'] = $this; // Parent reference.

        // All header-related templates.

        if (is_null($site_header_template = &$this->cacheKey(__FUNCTION__, 'site_header_template'))) {
            $site_header_template = new self('site/header.php');
        }
        if (is_null($site_header_styles_template = &$this->cacheKey(__FUNCTION__, 'site_header_styles_template'))) {
            $site_header_styles_template = new self('site/header-styles.php');
        }
        if (is_null($site_header_scripts_template = &$this->cacheKey(__FUNCTION__, 'site_header_scripts_template'))) {
            $site_header_scripts_template = new self('site/header-scripts.php');
        }
        if (is_null($site_header_tag_template = &$this->cacheKey(__FUNCTION__, 'site_header_tag_template'))) {
            $site_header_tag_template = new self('site/header-tag.php');
        }
        $site_header_styles  = $site_header_styles_template->parse($vars);
        $site_header_scripts = $site_header_scripts_template->parse($vars);
        $site_header_tag     = $site_header_tag_template->parse($vars);
        $site_header_vars    = compact('site_header_styles', 'site_header_scripts', 'site_header_tag');
        $site_header         = $site_header_template->parse(array_merge($vars, $site_header_vars));

        // All footer-related templates.

        if (is_null($site_footer_tag_template = &$this->cacheKey(__FUNCTION__, 'site_footer_tag_template'))) {
            $site_footer_tag_template = new self('site/footer-tag.php');
        }
        if (is_null($site_footer_template = &$this->cacheKey(__FUNCTION__, 'site_footer_template'))) {
            $site_footer_template = new self('site/footer.php');
        }
        $site_footer_tag  = $site_footer_tag_template->parse($vars);
        $site_footer_vars = compact('site_footer_tag'); // Only one for now.
        $site_footer      = $site_footer_template->parse(array_merge($vars, $site_footer_vars));

        return compact('site_header', 'site_footer'); // Header/footer.
    }

    /**
     * Email template vars.
     *
     * @since 141111 First documented version.
     *
     * @param array $vars Optional array of variables to parse.
     *
     * @return array An array of all email template vars.
     */
    protected function emailVars(array $vars = [])
    {
        if (strpos($this->file, 'email/header') === 0) {
            return []; // Prevent infinite loop.
        }
        if (strpos($this->file, 'email/footer') === 0) {
            return []; // Prevent infinite loop.
        }
        // Parent template reference.

        $vars['parent_template'] = $this; // Parent reference.

        // All header-related templates.

        if (is_null($email_header_template = &$this->cacheKey(__FUNCTION__, 'email_header_template'))) {
            $email_header_template = new self('email/header.php');
        }
        if (is_null($email_header_styles_template = &$this->cacheKey(__FUNCTION__, 'email_header_styles_template'))) {
            $email_header_styles_template = new self('email/header-styles.php');
        }
        if (is_null($email_header_scripts_template = &$this->cacheKey(__FUNCTION__, 'email_header_scripts_template'))) {
            $email_header_scripts_template = new self('email/header-scripts.php');
        }
        if (is_null($email_header_tag_template = &$this->cacheKey(__FUNCTION__, 'email_header_tag_template'))) {
            $email_header_tag_template = new self('email/header-tag.php');
        }
        $email_header_styles  = $email_header_styles_template->parse($vars);
        $email_header_scripts = $email_header_scripts_template->parse($vars);
        $email_header_tag     = $email_header_tag_template->parse($vars);
        $email_header_vars    = compact('email_header_styles', 'email_header_scripts', 'email_header_tag');
        $email_header         = $email_header_template->parse(array_merge($vars, $email_header_vars));

        // All footer-related templates.

        if (is_null($email_footer_tag_template = &$this->cacheKey(__FUNCTION__, 'email_footer_tag_template'))) {
            $email_footer_tag_template = new self('email/footer-tag.php');
        }
        if (is_null($email_footer_template = &$this->cacheKey(__FUNCTION__, 'email_footer_template'))) {
            $email_footer_template = new self('email/footer.php');
        }
        $email_footer_tag  = $email_footer_tag_template->parse($vars);
        $email_footer_vars = compact('email_footer_tag'); // Only one for now.
        $email_footer      = $email_footer_template->parse(array_merge($vars, $email_footer_vars));

        return compact('email_header', 'email_footer'); // Header/footer.
    }

    /**
     * Template file path.
     *
     * @since 161118 Enhancing templates.
     *
     * @throws \exception If unable to locate the template.
     *
     * @return string Template file path.
     */
    protected function getFilePath()
    {
        if ($this->force_default) {
            goto default_template;
        }
        check_theme_dirs: // Target point.

        $dirs = []; // Initialize.
        // e.g. `wp-content/themes/[theme]/[plugin slug]/type-a/[site/comment-form/file.php]`
        $dirs[] = get_stylesheet_directory().'/'.SLUG_TD.'/type-'.$this->type;
        $dirs[] = get_template_directory().'/'.SLUG_TD.'/type-'.$this->type;

        foreach ($dirs as $_dir /* In order of precedence. */) {
            if (is_file($_dir.'/'.$this->file) && is_readable($_dir.'/'.$this->file)) {
                return $_dir.'/'.$this->file;
            }
        } // unset($_dir); // Housekeeping.

        check_option_key: // Target point.

        // e.g. type `a` for `site/comment-form/file.php`.
        // becomes: `template__type_a__site__comment_form__file___php`.
        $option_key = static::dataOptionKey(['type' => $this->type, 'file' => $this->file]);

        if (!empty($this->plugin->options[$option_key])) {
            return ''; // Not applicable.
        }
        default_template: // Target point.

        // Default template directory.
        $dirs   = []; // Initialize.
        $dirs[] = dirname(__DIR__).'/templates/type-'.$this->type;

        foreach ($dirs as $_dir /* In order of precedence. */) {
            if (is_file($_dir.'/'.$this->file) && is_readable($_dir.'/'.$this->file)) {
                return $_dir.'/'.$this->file;
            }
        } // unset($_dir); // Housekeeping.

        throw new \exception(sprintf(__('Missing template: `type-%1$s/%2$s`.', 'comment-mail'), $this->type, $this->file));
    }

    /**
     * Template file contents.
     *
     * @since 141111 First documented version.
     *
     * @throws \exception If unable to locate the template.
     *
     * @return string Template file contents.
     */
    protected function getFileContents()
    {
        if ($this->file_path) {
            return file_get_contents($this->file_path);
        }
        // e.g. type `a` for `site/comment-form/file.php`.
        // becomes: `template__type_a__site__comment_form__file___php`.
        $option_key = static::dataOptionKey(['type' => $this->type, 'file' => $this->file]);

        if (!empty($this->plugin->options[$option_key])) {
            // Strip legacy template backup, if applicable; see `fromLteV160213()` in `UpgraderVs.php`
            return $this->plugin->options[$option_key] = preg_replace('/\<\?php\s+\/\*\s+\-{3,}\s+Legacy\s+Template\s+Backup\s+\-{3,}.*/uis', '', $this->plugin->options[$option_key]);
        }
        throw new \exception(sprintf(__('Missing template: `type-%1$s/%2$s`.', 'comment-mail'), $this->type, $this->file));
    }

    /**
     * Snippet file path.
     *
     * @since 161118 Enhancing templates.
     *
     * @param string $file File path, relative to snippet sub-directory.
     *
     * @throws \exception If unable to locate the snippet.
     *
     * @return string Snippet file path.
     */
    protected function snippetFilePath($file)
    {
        if ($this->force_default) {
            goto default_snippet;
        }
        check_theme_dirs: // Target point.

        $dirs = []; // Initialize.
        // e.g. `wp-content/themes/[theme]/[plugin slug]/type-a/[site/comment-form/snippet/file.php]`
        $dirs[] = get_stylesheet_directory().'/'.SLUG_TD.'/type-'.$this->type.'/'.$this->snippet_sub_dir;
        $dirs[] = get_template_directory().'/'.SLUG_TD.'/type-'.$this->type.'/'.$this->snippet_sub_dir;

        foreach ($dirs as $_dir /* In order of precedence. */) {
            if (is_file($_dir.'/'.$file) && is_readable($_dir.'/'.$file)) {
                return $_dir.'/'.$file;
            }
        } // unset($_dir); // Housekeeping.

        check_option_key: // Target point.

        // e.g. type `a` for `site/comment-form/snippet/file.php`.
        // becomes: `template__type_a__site__comment_form__snipppet__file___php`.
        $option_key = static::dataOptionKey(['type' => $this->type, 'file' => $this->snippet_sub_dir.'/'.$file]);

        if (!empty($this->plugin->options[$option_key])) {
            return ''; // Not applicable.
        }
        default_snippet: // Target point.

        // Default snippet directory.
        $dirs   = []; // Initialize.
        $dirs[] = dirname(__DIR__).'/templates/type-'.$this->type.'/'.$this->snippet_sub_dir;

        foreach ($dirs as $_dir /* In order of precedence. */) {
            if (is_file($_dir.'/'.$file) && is_readable($_dir.'/'.$file)) {
                return $_dir.'/'.$file;
            }
        } // unset($_dir); // Housekeeping.

        throw new \exception(sprintf(__('Missing snippet: `%1$s`.', 'comment-mail'), 'type-'.$this->type.'/'.$this->snippet_sub_dir.'/'.$file));
    }

    /**
     * Snippet file contents.
     *
     * @since 141111 First documented version.
     *
     * @param string $file File path, relative to snippet sub-directory.
     *
     * @throws \exception If unable to locate the snippet.
     *
     * @return string Snippet file contents.
     */
    protected function snippetFileContents($file)
    {
        if (($file_path = $this->snippetFilePath($file))) {
            return file_get_contents($file_path);
        }
        // e.g. type `a` for `site/comment-form/snippet/file.php`.
        // becomes: `template__type_a__site__comment_form__snipppet__file___php`.
        $option_key = static::dataOptionKey(['type' => $this->type, 'file' => $this->snippet_sub_dir.'/'.$file]);

        if (!empty($this->plugin->options[$option_key])) {
            return $this->plugin->options[$option_key];
        }
        throw new \exception(sprintf(__('Missing snippet: `%1$s`.', 'comment-mail'), 'type-'.$this->type.'/'.$this->snippet_sub_dir.'/'.$file));
    }

    /**
     * Transforms an option key into a type & file path.
     *
     * @since 141111 First documented version.
     *
     * @param string $option_key Template option key.
     *
     * @return \stdClass Object w/ two properties: `type` and `file`.
     */
    public static function optionKeyData($option_key)
    {
        $plugin = plugin(); // Plugin class.

        $type       = $file       = ''; // Initialize.
        $option_key = trim(strtolower((string) $option_key));

        if (preg_match('/^template__type_(?P<type>.+?)__/', $option_key, $_m)) {
            $type = trim(strtolower((string) $_m['type'])); // Key has type?
        }
        if (!$type) {
            $type = $plugin->options['template_type'];
        }
        unset($_m); // A little housekeeping.

        $file = $option_key; // Initialize.
        $file = preg_replace('/^template__type_.+?__/', '', $file);
        $file = str_replace('___', '.', $file);
        $file = str_replace('__', '/', $file);
        $file = str_replace('_', '-', $file);

        $file = $plugin->utils_string->trimDeep($file, '', '/');
        $file = $plugin->utils_fs->nSeps($file);

        return (object) compact('type', 'file');
    }

    /**
     * Transforms option data (type/file) into a plugin option key.
     *
     * @since 141111 First documented version.
     *
     * @param \stdClass|array Two properties: `type`, `file`.
     *
     * @return string The plugin option key for the given template data.
     */
    public static function dataOptionKey($data)
    {
        $plugin = plugin(); // Plugin class.

        $type = $file = ''; // Initialize.

        if (is_array($data)) {
            $data = (object) $data;
        }
        if (!is_object($data)) {
            $data = new \stdClass();
        }
        if (!empty($data->type)) { // Specific type?
            $type = trim(strtolower((string) $data->type));
        }
        if (!$type) {
            $type = $plugin->options['template_type'];
        }
        if (!empty($data->file)) { // In case it is empty.
            $file = trim(strtolower((string) $data->file));
        }
        $file = $plugin->utils_string->trimDeep($file, '', '/');
        $file = $plugin->utils_fs->nSeps($file);

        $option_key = $file; // Initialize.
        $option_key = str_replace('.', '___', $option_key);
        $option_key = str_replace('/', '__', $option_key);
        $option_key = str_replace('-', '_', $option_key);
        $option_key = 'template__type_'.$type.'__'.$option_key;

        return $option_key; // Plugin option key.
    }
}
