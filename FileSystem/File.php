<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 11/03/2018
 * Time: 14:00
 */

namespace Framework\FileSystem;

/**
 * Class File
    * Class to handle functionality relating to a given file
 * @package Framework\FileSystem
 */
final class File {
    /**
     * @var string
     *
     * Path of the file
     */
    private $path;

    /**
     * @var string
     *
     * Mode of the file
     */
    private $mode;

    /**
     * @var bool|resource
     *
     * File resource handler
     */
    private $handler;

    /**
     * File constructor.
     *
     * @param string $path - Path of the file
     * @param string $mode - Mode of the file
     */
    public function __construct($path, $mode) {
        $this->path = $path;
        $this->mode = $mode;
        $this->handler = fopen($this->path, $this->mode);
    }

    /**
     * Closes the file on destruction
     */
    public function __destruct() {
        fclose($this->handler);
    }

    /**
     * @return mixed - Returns the file path
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param mixed $path - Sets the file path
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * @return mixed - Returns the file mode
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * @return bool - Returns true if the file at the given path exists
     */
    public function exists() {
        return file_exists($this->path);
    }

    /**
     * @return bool - Returns true if the file is readable
     */
    public function isReadable() {
        return is_readable($this->path);
    }

    /**
     * @return bool - Returns true if the file is writable
     */
    public function isWritable() {
        return is_writable($this->path);
    }

    /**
     * @return int - Returns the size of the file.
     */
    public function size() {
        return filesize($this->path);
    }

    /**
     * @return bool|false|int - Returns the file's content
     */
    public function read() {
        return $this->exists() && $this->isReadable() ? readfile($this->path) : false;
    }

    /**
     * @return bool|\Generator - Reads the file line by line, yielding each line
     */
    public function readLines() {
        if (!$this->exists() || !$this->isReadable())
            return false;

        if($this->handler) {
            while (($line = fgets($this->handler)) !== false)
                yield $line;

            return true;
        }
        return false;
    }

    /**
     * Writes content to the file
     *
     * @param string $content - Content to write to the file
     * @return bool - Returns true if successful or false if failed
     */
    public function write($content) {
        if(!$this->exists() || !$this->isWritable() || $this->mode == "r")
            return false;

        if ($this->handler) {
            if (is_array($content)) {
                foreach ($content as $line)
                    fputs($this->handler, $line);
            } else {
                fputs($this->handler, $content);
            }
            return true;
        }
        return false;
    }

    /**
     * @return bool|int - Returns the current position of the file read/write pointer
     */
    public function tell() {
        return ftell($this->handler);
    }

    /**
     * Seeks on a file pointer
     *
     * @param $position - Position to move the pointer
     * @return int - Returns 0 on success or -1, else -1
     */
    public function seek($position) {
        return fseek($this->handler, $position);
    }

    /**
     * @return bool - Rewind the position of the file pointer
     */
    public function rewind() {
        return rewind($this->handler);
    }

    /**
     * @return array - Returns information about the file
     */
    public function stat() {
        return fstat($this->handler);
    }
}