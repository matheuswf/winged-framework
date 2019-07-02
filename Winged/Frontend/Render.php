<?php

namespace Winged\Frontend;

use Winged\Controller\Controller;
use Winged\File\File;
use Winged\Buffer\Buffer;

/**
 * render view files in project
 *
 * Class Render
 *
 * @package Winged\Frontend
 */
class Render extends Assets
{

    /**
     * @var bool
     */
    public $first_render = true;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * abstraction for render
     *
     * @param       $path
     * @param array $vars
     *
     * @return bool|string
     */
    protected function _render($path, $vars = [])
    {
        if (file_exists($path) && !is_directory($path)) {
            return $this->_include($path, $vars);
        } else {
            trigger_error("File {$path} can't rendred because file not found.", E_USER_WARNING);
        }
        return false;
    }

    /**
     * abstraction for include file and local vars
     *
     * @param       $path
     * @param array $vars
     *
     * @return bool|string
     */
    private function _include($path, $vars = [])
    {
        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                if (!is_int($key) && is_string($key)) {
                    ${$key} = $value;
                }
            }
        }
        if ($this->first_render) {
            $this->first_render = false;
            Buffer::reset();
            $file = new File($path, false);
            $read = $file->read();
            $_pos = stripos($read, '?>');
            if (is_int($_pos)) {
                if ($_pos > 0) {
                    $read = "\n?>\n" . trim($read);
                }
            } else {
                $read = "\n?>\n" . trim($read);
            }
            if ($read[strlen($read) - 1] != ';') {
                $read = $read . "\n<?php\n";
            }
            eval($read);
            $content = Buffer::getKill();
            return $content;
        } else {
            $file = new File($path, false);
            $read = $file->read();
            $_pos = stripos($read, '?>');
            if (is_int($_pos)) {
                if ($_pos > 0) {
                    $read = "\n?>\n" . trim($read);
                }
            } else {
                $read = "\n?>\n" . trim($read);
            }
            if ($read[strlen($read) - 1] != ';') {
                $read = $read . "\n<?php\n";
            }
            eval($read);
        }
        return false;
    }

}