<?php

/* --------------------------------------------------------------------

  Chevereto
  http://chevereto.com/

  @author	Rodolfo Berrios A. <http://rodolfoberrios.com/>
            <inbox@rodolfoberrios.com>

  Copyright (C) Rodolfo Berrios A. All rights reserved.

  BY USING THIS SOFTWARE YOU DECLARE TO ACCEPT THE CHEVERETO EULA
  http://chevereto.com/license

  --------------------------------------------------------------------- */

namespace CHV;

use G;
use Exception;

// todo props standard

class LocalStorage
{
    public $url;
    public $path;
    public $realPath;
    public $deleted = [];

    public function __construct($args=[])
    {
        $this->url = $args['url'];
        $this->path = $args['bucket'];
        $this->realPath = realpath($this->path) . '/';
        if (is_writeable($this->realPath) == false) {
            throw new Exception("Can't work in target directory", 100);
        }
    }
    protected function checkPath()
    {
        // todo check path on construct?
    }
    public function put($args=[])
    {
        // [filename] => photo-1460378150801-e2c95cb65a50.jpg
        // [source_file] => /tmp/photo-1460378150801-e2c95cb65a50.jpg
        // [path] => /path/sdk/2018/08/18/
        extract($args);
        if (is_writable($path) == false) {
            throw new Exception("Can't write in target directory", 400);
        }
        $target_filename = $path . $filename;
        $uploaded = copy($source_file, $target_filename);
        if ($uploaded == false) {
            throw new Exception("Can't move source file to its destination", 400);
        }
        @chmod($target_filename, 0644);
        clearstatcache();
    }
    public function delete($filename)
    {
        $filename = $this->getWorkingPath($filename);
        if (file_exists($filename) == false) {
            return;
        }
        if (@unlink($filename) == false) {
            throw new Exception("Can't delete file '$filename' in " . __METHOD__, 200);
        }
        clearstatcache();
    }
    public function deleteMultiple(array $filenames=[])
    {
        $this->deleted = [];
        foreach ($filenames as $k => $v) {
            try {
                $this->delete($v);
                array_push($this->deleted, $v);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
        }
    }
    public function mkdirRecursive($dirname)
    {
        $dirname = $this->getWorkingPath($dirname);
        if (is_dir($dirname)) {
            return;
        }
        $path_perms = fileperms($this->realPath);
        $old_umask = umask(0);
        $make_pathname = mkdir($dirname, $path_perms, true);
        chmod($dirname, $path_perms);
        umask($old_umask);
        if (!$make_pathname) {
            throw new Exception('$dirname '. $dirname . ' is not a dir', 130);
        }
    }
    protected function getWorkingPath($dirname)
    {
        if (G\starts_with('/', $dirname) == false) { // relative thing
            return $this->realPath . $dirname;
        }
        return realpath($dirname);
    }
}
