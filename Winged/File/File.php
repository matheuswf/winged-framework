<?php

namespace Winged\File;

use Winged\Directory\Directory;
use Winged\Formater\Formater;
use Winged\Utils\WingedLib;

class File
{

    public $file_path = null;
    public $file = null;
    public $handler = null;
    public $new = true;
    /**
     * @var $folder Directory
     */
    public $folder = null;

    /**
     * CoreFile constructor.
     * @param $file
     * @param bool $forceCreate
     */
    public function __construct($file, $forceCreate = true)
    {
        if (is_string($file)) {

            if (!is_int(stripos($file, './'))) {
                $file = './' . $file;
            } else {
                if (intval(stripos($file, './')) !== 0) {
                    $file = './' . $file;
                }
            }
            $file = str_replace(['//', './.'], ['/', './'], $file);
        }

        $file_accent = $file;

        $file = explode('/', $file);
        foreach ($file as $key => $value) {
            $v = Formater::removeAccents(Formater::removeSpaces($value));
            if ($v != $file) {
                $file[$key] = $v;
            }
        }
        $file = join('/', $file);

        $folder = null;
        $file_name = null;

        if ($file_accent != $file) {
            if (is_file(utf8_decode($file_accent)) && file_exists(utf8_decode($file_accent))) {
                $folder = explode('/', utf8_decode($file_accent));
                $file_name = array_pop($folder);
                $folder = new Directory(join('/', $folder));
                $this->folder = $folder;
                $this->file_path = utf8_decode($file_accent);
                $this->handler = fopen($this->file_path, 'r+');
                fclose($this->handler);
                $this->new = false;
                $this->file = $file_name;
            }
        } else {
            if (is_file($file) && file_exists($file)) {
                $folder = explode('/', $file);
                $file_name = array_pop($folder);
                $folder = new Directory(join('/', $folder));
                $this->folder = $folder;
                $this->file_path = $file;
                $this->handler = fopen($this->file_path, 'r+');
                fclose($this->handler);
                $this->new = false;
                $this->file = $file_name;
            } else {
                if ($forceCreate) {
                    $folder = explode('/', $file);
                    $file_name = array_pop($folder);
                    $folder = new Directory(join('/', $folder));
                    $this->handler = fopen($file, 'w+');
                    fwrite($this->handler, '');
                    fclose($this->handler);
                }
            }
            if (is_file($file) && file_exists($file)) {
                $this->file_path = $file;
                $this->folder = $folder;
                $this->file = $file_name;
            } else {
                $this->file_path = $file;
                $this->folder = null;
                $this->file = $file;
            }
        }
    }

    /**
     * Attempts to rename the file if it exists. If the file exists, try to use the native rename and if it succeeds the file path will change.
     * @param $name
     * @return $this
     */
    public function rename($name)
    {
        if ($this->file_path != null) {
            $expf = explode('/', $this->file_path);
            $end = array_pop($expf);
            $exp = explode('.', $end);
            $new_name = $name . '.' . end($exp);
            $npath = implode('/', $expf);
            $npath = WingedLib::dotslash(WingedLib::dotslash($npath), true);
            $npath = $npath . $new_name;
            if (rename($this->file_path, $npath)) {
                $this->file = $new_name;
                if (file_exists($this->file_path) && !is_dir($this->file_path)) {
                    unlink($this->file_path);
                }
                $this->file_path = $npath;
            }
        }
        return $this;
    }

    /**
     * Cut the file to the specified location, if the location does not exist, the location will be created dynamically.
     * @param string $to
     * @return $this|CoreFile
     */
    public function crop($to = '')
    {
        $new = $this->copy($to);
        if ($new->file_path != $this->file_path) {
            $this->delete();
            $this->folder = $new->folder;
            $this->file_path = $new->file_path;
            $this->file = $new->file;
        }
        return $this;
    }

    /**
     * Copies the file to the specified location and returns a new CoreFile object with the path of that copied file.
     * If it fails in this process, the return of the method is the very object that made the call.
     * @param string $to
     * @return CoreFile
     */
    public function copy($to = '')
    {
        $folder = null;
        if ($this->file_path != null && is_string($to)) {
            $file = explode('/', $this->file_path);
            $file = end($file);
            $folder = new Directory($to);
            if ($folder->exists()) {
                $to = $folder->folder . $file;
                copy($this->file_path, $to);
                return new CoreFile($to);
            }
        }
        return $this;
    }

    /**
     * Delete file if possible
     * @return bool
     */
    public function delete()
    {
        if ($this->file_path != null) {
            if (file_exists($this->file_path) && !is_dir($this->file_path)) {
                return unlink($this->file_path);
            }
        }
        return false;
    }

    /**
     * Clear file and writes any content within a file.
     * @param string $content
     * @return $this|bool
     */
    public function write($content = '')
    {
        if ($this->file_path != null) {
            $this->handler = fopen($this->file_path, 'w+');
            fwrite($this->handler, $content);
            fclose($this->handler);
            $this->new = false;
            if (get_class($this) === 'CoreImage') {
                $this->create(null);
            }
        }
        return $this;
    }

    /**
     * Append any content within a file.
     * @param string $content
     * @return $this|bool
     */
    public function append($content = '')
    {
        if ($this->file_path != null) {
            $this->handler = fopen($this->file_path, 'a+');
            fwrite($this->handler, $content);
            fclose($this->handler);
            $this->new = false;
            if (get_class($this) === 'CoreImage') {
                $this->create(null);
            }
        }
        return $this;
    }

    /**
     * Returns a handler off a empty file
     * @return bool
     */
    public function getWriteHandler()
    {
        if ($this->file_path != null) {
            return fopen($this->file_path, 'w+');
        }
        return false;
    }

    /**
     * Returns a handler a file with pointer in the EOF
     * @return bool
     */
    public function getAppendHandler()
    {
        if ($this->file_path != null) {
            return fopen($this->file_path, 'a+');
        }
        return false;
    }

    /**
     * Read and return content of file
     * @return $this|bool|string
     */
    public function read()
    {
        if ($this->file_path != null) {
            return file_get_contents($this->file_path);
        }
        return $this;
    }

    /**
     * Change extension of file.
     * @param string $ext
     * @return $this
     */
    public function changeExtension($ext = '')
    {
        if ($this->file_path != null) {
            if (is_string($ext)) {
                if ($ext != '') {
                    $file_path = explode('/', $this->file_path);
                    $file = array_pop($file_path);
                    $file = explode('.', $file);
                    $new_file_path = join('/', $file_path) . '/' . $file[0] . '.' . $ext;
                    if (rename($this->file_path, $new_file_path)) {
                        $this->file_path = $new_file_path;
                        $new_file_path = explode('/', $new_file_path);
                        $this->file = end($new_file_path);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Returns the file extension
     * @return string
     */
    public function getExtension()
    {
        if ($this->file_path != null) {
            $exp = explode('.', $this->file_path);
            $end = array_pop($exp);
            return $end;
        }
        return false;
    }

    public function getName()
    {
        if ($this->file_path != null) {
            $exp = explode('.', $this->file);
            array_pop($exp);
            return join('.', $exp);
        }
        return false;
    }

    /**
     * Checks whether the file exists
     * @return $this|bool
     */
    public function exists()
    {
        if ($this->folder != null) {
            return $this;
        }
        return false;
    }

    public function create($file)
    {
        $this->__construct($file);
        return $this;
    }

}

