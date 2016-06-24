<?php

namespace Irisit\IrispassShared\Services;


use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class FlatCmService
{
    private $container_path;
    private $disabled_path;
    private $archives_path;

    public function __construct()
    {
        $this->master_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, config('irispass.cms.master_path')) . DIRECTORY_SEPARATOR;
        $this->container_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, config('irispass.cms.path')) . DIRECTORY_SEPARATOR;
        $this->disabled_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, config('irispass.cms.path')) . DIRECTORY_SEPARATOR . 'disabled' . DIRECTORY_SEPARATOR;
        $this->archives_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, config('irispass.cms.path')) . DIRECTORY_SEPARATOR . 'archives' . DIRECTORY_SEPARATOR;
    }

    public function process($identifier, $username, $mail, $password)
    {
        if ($this->checkExistance($identifier) === false) {
            $this->copyCMS($identifier);
            $this->sedReplace($identifier, $username, $mail, $password);
        } else {
            $this->reactivate($identifier);
        }

        return true;
    }

    /*
     * Step ONE
     */
    public function copyCMS($identifier)
    {

        $adapter = new Local($this->container_path);
        $filesystem = new Filesystem($adapter);
        $filesystem->createDir($identifier);

        $dest = $this->container_path . $identifier;

        $permissions = 0755;
        $this->xcopy($this->master_path, $dest, $permissions);

        exec('chmod -R 777 ' . $dest);
        exec('chown -R www-data:www-data ' . $dest);
    }

    /*
     * Step Two
     */
    public function sedReplace($identifier, $username, $mail, $password)
    {

        $path_to_directory = $this->container_path . $identifier;

        //data\_extra\Header.php
        $path_to_file = $path_to_directory . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '_extra' . DIRECTORY_SEPARATOR . 'Header.php';
        $file_contents = file_get_contents($path_to_file);
        $file_contents = str_replace('{SITENAME}', $identifier, $file_contents);
        file_put_contents($path_to_file, $file_contents);

        //data\_site\config.php
        $path_to_file = $path_to_directory . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '_site' . DIRECTORY_SEPARATOR . 'config.php';
        $file_contents = file_get_contents($path_to_file);
        $file_contents = str_replace('{EMAIL}', $mail, $file_contents);
        $file_contents = str_replace('{SITENAME}', $identifier, $file_contents);
        file_put_contents($path_to_file, $file_contents);

        //data\_site\config.php
        $path_to_file = $path_to_directory . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '_site' . DIRECTORY_SEPARATOR . 'users.php';
        $file_contents = file_get_contents($path_to_file);
        $file_contents = str_replace('{USERNAME}', $username, $file_contents);
        $file_contents = str_replace('{PASSWORD}', $this->hash512($password), $file_contents);
        $file_contents = str_replace('{EMAIL}', $mail, $file_contents);
        file_put_contents($path_to_file, $file_contents);
    }

    /*
     * DISABLE
     */
    public function disable($identifier)
    {
        $adapter = new Local($this->container_path);
        $filesystem = new Filesystem($adapter);
        $filesystem->createDir('disabled');
        if ($this->checkExistance($identifier) === true && is_dir($this->container_path . $identifier)) {
            exec('mv ' . $this->container_path . $identifier . ' ' . $this->disabled_path);
            return true;
        } else {
            return 'error';
        }
    }

    /*
     * Helpers
     */
    public function reactivate($identifier)
    {

        if ($this->checkExistance($identifier) === true) {
            exec('mv ' . $this->disabled_path . $identifier . ' ' . $this->container_path);
            return true;
        } else {
            return 'error';
        }

    }

    public function checkExistance($identifier)
    {
        $exists = false;

        $adapter = new Local($this->container_path);
        $filesystem = new Filesystem($adapter);
        $contents = $filesystem->listContents('/');
        foreach ($contents as $directory) {
            if ($directory['basename'] == $identifier) {
                $exists = true;
                return $exists;
            }
        }

        $adapter = new Local($this->disabled_path);
        $filesystem = new Filesystem($adapter);
        $contents = $filesystem->listContents('/');
        foreach ($contents as $directory) {
            if ($directory['basename'] == $identifier) {
                $exists = true;
                return $exists;
            }
        }

        return $exists;
    }

    public function isActive($identifier)
    {
        $exists = false;
        $adapter = new Local($this->container_path);
        $filesystem = new Filesystem($adapter);
        $contents = $filesystem->listContents('/');
        foreach ($contents as $directory) {
            if ($directory['basename'] == $identifier) {
                $exists = true;
                return $exists;
            }
        }
        return $exists;
    }

    public function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }
        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }
        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }
        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry", $permissions);
        }
        // Clean up
        $dir->close();
        return true;
    }

    public static function hash512($arg)
    {
        $arg = trim($arg);

        //sha512: looped with dynamic salt
        for ($i = 0; $i < 1000; $i++) {

            $ints = preg_replace('#[a-f]#', '', $arg);
            $salt_start = (int)substr($ints, 0, 1);
            $salt_len = (int)substr($ints, 2, 1);
            $salt = substr($arg, $salt_start, $salt_len);
            $arg = hash('sha512', $arg . $salt);
        }

        return $arg;
    }

    public function saveAsZip($identifier)
    {
        //initialize variables
        $archives = new Filesystem(new Local($this->container_path));
        $local = new Filesystem(new Local($this->container_path . $identifier));
        $zip = new Filesystem(new ZipArchiveAdapter($this->archives_path . time() . '-' . $identifier . '.zip'));

        //make sure the directory exists
        $archives->createDir('archives');

        //list dir for CMS
        $contents = $local->listContents('', true);

        foreach ($contents as $info) {
            if ($info['type'] === 'dir') {
                continue;
            }

            $zip->write($info['path'], $local->read($info['path']));
        }

        // This will trigger saving the zip.
        $zip = null;

        return true;
    }

    public function destroyCMS($identifier)
    {

        $adapter = new Local($this->container_path);
        $filesystem = new Filesystem($adapter);

        if ($this->checkExistance($identifier)) {
            if ($this->saveAsZip($identifier)) {
                $filesystem->deleteDir($identifier);
                return true;
            }
        }

        return false;
    }

}