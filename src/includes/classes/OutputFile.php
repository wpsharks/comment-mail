<?php
/**
 * File Output Handler.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail;

/**
 * File Output Handler.
 *
 * @since 141111 First documented version.
 */
class OutputFile extends AbsBase
{
    /**
     * @type string Data to output.
     *
     * @since 141111 First documented version.
     */
    protected $data;

    /**
     * @type string Data file to output.
     *
     * @since 141111 First documented version.
     */
    protected $data_file;

    /**
     * @type string File name to output.
     *
     * @since 141111 First documented version.
     */
    protected $file_name;

    /**
     * @type string Content type.
     *
     * @since 141111 First documented version.
     */
    protected $content_type;

    /**
     * @type string Content disposition.
     *
     * @since 141111 First documented version.
     */
    protected $content_disposition;

    /**
     * @type int Chunk size.
     *
     * @since 141111 First documented version.
     */
    protected $chunk_size;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $args Configuration arguments.
     *
     * @throws \exception If a security flag is triggered on `$this->data_file`.
     */
    public function __construct(array $args)
    {
        parent::__construct();

        $default_args = [
            'data'                => '',
            'data_file'           => '',
            'file_name'           => '',
            'content_type'        => '',
            'content_disposition' => 'attachment',
            'chunk_size'          => 2097152,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $this->data      = (string) $args['data'];
        $this->data_file = (string) $args['data_file'];

        if ($this->data_file) { // Run security flag checks on the path.
            $this->plugin->utils_fs->checkPathSecurity($this->data_file);
        }
        if ($this->data_file && is_file($this->data_file) && is_readable($this->data_file)) {
            $this->data = ''; // Favor the data file over raw data.
        }
        $this->file_name           = (string) $args['file_name'];
        $this->content_type        = (string) $args['content_type'];
        $this->content_disposition = (string) $args['content_disposition'];

        $this->chunk_size = (integer) $args['chunk_size'];
        $this->chunk_size = $this->chunk_size < 1 ? 1 : $this->chunk_size;

        $this->maybeOutput();
    }

    /**
     * Sends output file.
     *
     * @since 141111 First documented version.
     */
    protected function maybeOutput()
    {
        $this->prepare();
        $this->sendHeaders();
        $this->maybeSendData();
        $this->maybeSendDataFile();
        exit(); // Stop here.
    }

    /**
     * Prepare environment.
     *
     * @since 141111 First documented version.
     */
    protected function prepare()
    {
        $this->plugin->utils_env->prepForLargeOutput();
    }

    /**
     * Send headers; always.
     *
     * @since 141111 First documented version.
     */
    protected function sendHeaders()
    {
        status_header(200);

        header('Accept-Ranges: none');

        header('Content-Encoding: none');
        header('Content-Type: '.$this->content_type);
        header('Content-Length: '.$this->contentLength());

        nocache_headers(); // No browser cache.
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);

        header(
            'Content-Disposition:'.
            ' '.$this->content_disposition.';'.
            ' filename="'.$this->plugin->utils_string->escDq($this->file_name).'";'.
            ' filename*=UTF-8\'\''.rawurlencode($this->file_name)
        );
    }

    /**
     * Determine content length.
     *
     * @since 141111 First documented version.
     */
    protected function contentLength()
    {
        if (!is_null($content_length = &$this->cacheKey(__FUNCTION__))) {
            return $content_length; // Already cached this.
        }
        if ($this->data_file) { // File has precedence.
            return $content_length = filesize($this->data_file);
        }
        return $content_length = strlen($this->data);
    }

    /**
     * Send data; if applicable.
     *
     * @since 141111 First documented version.
     */
    protected function maybeSendData()
    {
        if ($this->data_file) {
            return; // Nothing to do here.
        }
        $content_length = // Initialize.
        $_bytes_to_read = $this->contentLength();

        while ($_bytes_to_read > 0) { // While we have bytes.
            $_reading_from = $content_length - $_bytes_to_read;
            $_reading      = $_bytes_to_read > $this->chunk_size
                ? $this->chunk_size : $_bytes_to_read;

            echo substr($this->data, $_reading_from, $_reading);

            $_bytes_to_read -= $_reading;

            flush(); // Flush to browser.
        }
        unset($_bytes_to_read, $_reading_from, $_reading);
    }

    /**
     * Send data file; if applicable.
     *
     * @since 141111 First documented version.
     */
    protected function maybeSendDataFile()
    {
        if (!$this->data_file) {
            return; // Nothing to do here.
        }
        if (!($resource = fopen($this->data_file, 'rb'))) {
            return; // Not applicable.
        }
        $content_length = // Initialize.
        $_bytes_to_read = $this->contentLength();

        while ($_bytes_to_read > 0) { // While we have bytes.
            $_reading_from = $content_length - $_bytes_to_read;
            $_reading      = $_bytes_to_read > $this->chunk_size
                ? $this->chunk_size : $_bytes_to_read;

            echo fread($resource, $_reading);

            $_bytes_to_read -= $_reading;

            flush(); // Flush to browser.
        }
        unset($_bytes_to_read, $_reading_from, $_reading);

        fclose($resource); // Close resource handle.
    }
}
