<?php namespace Irisit\IrispassShared\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class OsjsService
{

    private $disk;

    private $vfs_path;

    private $users_directory;

    private $groups_directory;

    private $is_testing;

    public function __construct()
    {
        $this->disk = Storage::disk(config('irispass.osjs.disk'));

        $this->vfs_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, config('irispass.osjs.vfs_path'));
        $this->users_directory = 'home';
        $this->groups_directory = 'groups';
        $this->backup_directory = 'backup';

        $this->is_testing = (env('APP_ENV') == 'testing') ? true : false;
    }

    public function createDirectory($type, $name)
    {

        switch ($type) {
            case 'user' :
                $path = $this->users_directory . DIRECTORY_SEPARATOR . $name;
                break;

            case 'group' :
                $path = $this->groups_directory . DIRECTORY_SEPARATOR . $name;
                break;

            default:
                return null;
                break;
        }

        if ($this->disk->makeDirectory($path, false, $this->is_testing)) {
            $path = str_replace(['\\', '/'], '.', $path);
            return $path;
        }

        return null;

    }

    /**
     * @param $type
     * @param $old_name
     * @param $new_name
     * @param Filesystem $filesystem
     * @return mixed|null
     */
    public function renameDirectory($type, $old_name, $new_name)
    {

        $filesystem = new Filesystem();

        if ($old_name == $new_name) {
            return true;
        }

        switch ($type) {
            case 'user' :
                $old_path = $this->vfs_path . DIRECTORY_SEPARATOR . $this->users_directory . DIRECTORY_SEPARATOR . $old_name;
                $new_path = $this->vfs_path . DIRECTORY_SEPARATOR . $this->users_directory . DIRECTORY_SEPARATOR . $new_name;
                break;

            case 'group' :
                $old_path = $this->vfs_path . DIRECTORY_SEPARATOR . $this->groups_directory . DIRECTORY_SEPARATOR . $old_name;
                $new_path = $this->vfs_path . DIRECTORY_SEPARATOR . $this->groups_directory . DIRECTORY_SEPARATOR . $new_name;
                break;

            default:
                return null;
                break;
        }

        if ($filesystem->copyDirectory($old_path, $new_path)) {

            //make a backup in case of emergency
            $filesystem->copyDirectory($old_path, $this->vfs_path . DIRECTORY_SEPARATOR . $this->backup_directory . DIRECTORY_SEPARATOR . $type . '-' . $old_name . '-' . time());

            //delete the old one
            $filesystem->deleteDirectory($old_path);

            $path = str_replace(['\\', '/'], '.', $new_path);

            return $path;
        }

        return null;

    }

    public function deleteDirectory($type, $name)
    {

        $filesystem = new Filesystem();

        switch ($type) {
            case 'user' :
                $path = $this->vfs_path . DIRECTORY_SEPARATOR . $this->users_directory . DIRECTORY_SEPARATOR . $name;
                break;

            case 'group' :
                $path = $this->vfs_path . DIRECTORY_SEPARATOR . $this->groups_directory . DIRECTORY_SEPARATOR . $name;
                break;

            default:
                return null;
                break;
        }

        //make a backup in case of emergency
        $filesystem->copyDirectory($path, $this->vfs_path . DIRECTORY_SEPARATOR . $this->backup_directory . DIRECTORY_SEPARATOR . $type . '-' . $name . '-' . time());

        //delete the old one
        $filesystem->deleteDirectory($path);


        return true;

    }


}
