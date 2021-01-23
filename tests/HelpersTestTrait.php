<?php


namespace Tests\Enjoys\AssetsCollector;


trait HelpersTestTrait
{

    private function removeDirectoryRecursive($path, $removeParent = false)
    {
        $di = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \SplFileInfo $file */
        foreach ($ri as $file) {
            if ($file->isLink()) {
                $symlink = realpath($file->getPath()) . '/' . $file->getFilename();
                unlink($symlink);
                continue;
            }
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        if ($removeParent) {
            rmdir($path);
        }
    }

}