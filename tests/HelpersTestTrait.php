<?php


namespace Tests\Enjoys\AssetsCollector;


trait HelpersTestTrait
{

    private function removeDirectoryRecursive($path, $removeParent = false)
    {
        if(!file_exists($path)){
            return;
        }
        $di = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \SplFileInfo $file */
        foreach ($ri as $file) {
            if ($file->isLink()) {
                $symlink = realpath($file->getPath()) . DIRECTORY_SEPARATOR . $file->getFilename();
                if(PHP_OS_FAMILY == 'Windows'){
                    (is_dir($symlink)) ? rmdir($symlink) : unlink($symlink);
                }else{
                    unlink($symlink);
                }
                continue;
            }
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        if ($removeParent) {
            rmdir($path);
        }
    }

}