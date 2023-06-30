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

class Ftp
{
    public $ftp;
        
    public function __construct($args=[])
    {
        if (!function_exists('ftp_connect')) {
            throw new FtpException("ftp_connect function doesn't exists in this setup. You must enable PHP FTP support to interact with FTP servers.", 500);
        }
        foreach (['server', 'user', 'password'] as $v) {
            if (!array_key_exists($v, $args)) {
                throw new FtpException("Missing $v value in ".__METHOD__, 100);
            }
        }
        $parsed_server = parse_url($args['server']);
        $host = $parsed_server['host'] ?: $args['server'];
        $port = $parsed_server['port'] ?: 21;
        $this->ftp = @ftp_connect($host, $port);
        if (!$this->ftp) {
            throw new FtpException("Can't connect to ".$args['server']." server", 200);
        }
        if (!@ftp_login($this->ftp, $args['user'], $args['password'])) {
            throw new FtpException("Can't FTP login to ".$args['server']." server", 201);
        }
        $args['passive'] = isset($args['passive']) ? (bool)$args['passive'] : true;
        if (!@ftp_pasv($this->ftp, $args['passive'])) {
            throw new FtpException("Can't ".($args['passive'] ? "enable" : "disable")." passive mode in server ".$args['server'], 202);
        }
        if (isset($args['path'])) {
            try {
                $this->chdir($args['path']);
            } catch (Exception $e) {
                // Create missing path if possible
                $this->mkdirRecursive($args['path']);
                $this->chdir($args['path']);
            }
        }
        return $this;
    }
    
    public function close()
    {
        @ftp_close($this->ftp);
        unset($this->ftp);
        return true;
    }
    
    public function chdir($path)
    {
        $this->checkResource();
        if (!@ftp_chdir($this->ftp, $path)) {
            $msg = "No permission to change to remote dir '$path'";
            throw new FtpException($msg, 300);
        }
    }
    
    public function put($args=[])
    {
        foreach (['filename', 'source_file', 'path'] as $v) {
            if (!array_key_exists($v, $args)) {
                throw new FtpException("Missing $v value in ".__METHOD__, 100);
            }
        }
        if (!array_key_exists('method', $args) or !in_array($args['method'], [FTP_BINARY, FTP_ASCII])) {
            $args['method'] = FTP_BINARY;
        }
        if (isset($args['path'])) {
            $this->chdir($args['path']);
        }
        $this->checkResource();
        if (!@ftp_put($this->ftp, $args['filename'], $args['source_file'], $args['method'])) {
            error_log("Can't upload '".$args['filename']."' to '".$args['path']."' in " . __METHOD__);
            throw new FtpException("Can't upload '".$args['filename']."' in " . __METHOD__, 401);
        }
    }
    
    public function delete($file)
    {
        $this->checkResource();
        // Force binary mode
        $binary = ftp_raw($this->ftp, 'TYPE I'); // SIZE command works only in Binary
        // Check if the file exists
        $raw = ftp_raw($this->ftp, "SIZE $file")[0];
        preg_match('/^(\d+)\s+(.*)$/', $raw, $matches);
        $code = $matches[1];
        $return = $matches[2];
        if ($code>500) { // SIZE is supported and the file doesn't exits
            return;
        }
        if (!@ftp_delete($this->ftp, $file)) {
            throw new FtpException("Can't delete file '$file' in " . __METHOD__, 200);
        }
    }
    
    
    public function mkdirRecursive($path)
    {
        $this->checkResource();
        $cwd = @ftp_pwd($this->ftp);
        if (!$cwd) {
            throw new FtpException("Can't get current working directory in " . __METHOD__, 200);
        }
        $cwd .= '/';
        foreach (explode('/', $path) as $part) {
            $cwd .= $part . '/';
            if (empty($part)) {
                continue;
            }
            if (!@ftp_chdir($this->ftp, $cwd)) {
                if (@ftp_mkdir($this->ftp, $part)) {
                    @ftp_chdir($this->ftp, $part);
                } else {
                    error_log("Can't make recursive dir '$path' relative to '$cwd' in " . __METHOD__);
                    throw new FtpException("Can't make recursive dir in " . __METHOD__, 200);
                    return false;
                }
            }
        }
    }
    
    protected function checkResource()
    {
        if (!is_resource($this->ftp)) {
            throw new FtpException("Invaid FTP buffer in " . __METHOD__, 200);
        }
    }
}

class FtpException extends Exception
{
}
