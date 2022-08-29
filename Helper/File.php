<?php
/**
 * Copyright © Nos, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Nos\ReadLog\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Creates the csv files in export folder and move to archive when it's complete.
 * Log info and debug to a custom log file connector.log
 */
class File extends AbstractHelper
{
    /**
     * @var string
     */
    private string $outputFolder;

    /**
     * @var string
     */
    private string $outputArchiveFolder;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var DriverInterface
     */
    private DriverInterface $driver;

    /**
     * File constructor.
     *
     * @param DirectoryList $directoryList
     * @param Context $context
     * @param Filesystem $filesystem
     * @throws FileSystemException
     */
    public function __construct(
        DirectoryList $directoryList,
        Context       $context,
        Filesystem    $filesystem,
    )
    {
        $this->directoryList = $directoryList;
        $this->driver = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->getDriver();
        $varPath = $directoryList->getPath('var');
        $this->outputFolder = $varPath . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR . 'email';
        $this->outputArchiveFolder = $this->outputFolder . DIRECTORY_SEPARATOR . 'archive';

        parent::__construct($context);
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    private function getOutputFolder(): string
    {
        $this->createDirectoryIfNotExists($this->outputFolder);

        return $this->outputFolder;
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    public function getArchiveFolder(): string
    {
        $this->createDirectoryIfNotExists($this->outputArchiveFolder);

        return $this->outputArchiveFolder;
    }

    /**
     *  Return the full filepath.
     *
     * @param string $filename
     *
     * @return string
     * @throws FileSystemException
     */
    public function getFilePath(string $filename): string
    {
        return $this->getOutputFolder() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @param string $filepath
     * @param array $csv
     *
     * @return null
     * @throws FileSystemException
     */
    public function outputCSV(string $filepath, array $csv)
    {
        /*
         * Open for writing only; place the file pointer at the end of the file.
         * If the file does not exist, attempt to create it.
         */
        $handle = $this->driver->fileOpen($filepath, 'a');
        fputcsv($handle, $csv, ',', '"');
        $this->driver->fileClose($handle);
    }

    /**
     * If the path does not exist then create it.
     *
     * @param string $path
     *
     * @return null
     * @throws FileSystemException
     */
    private function createDirectoryIfNotExists(string $path)
    {
        if (!$this->driver->isDirectory($path)) {
            $this->driver->createDirectory($path, 0750);
        }
    }

    /**
     * Delete file or directory.
     *
     * @param string $path
     *
     * @return bool|string
     * @throws FileSystemException
     */
    public function deleteDir(string $path): bool|string
    {
        if (!str_contains($path, $this->directoryList->getPath('var'))) {
            return sprintf("Failed to delete directory - '%s'", $path);
        }

        return $this->driver->deleteDirectory($path);
    }

    /**
     * Get log file content.
     *
     * @param string $filename
     *
     * @return string
     * @throws FileSystemException
     */
    public function getLogFileContent(string $filename = 'connector'): string
    {
        switch ($filename) {
            case "connector":
                $filename = 'connector.log';
                break;
            case "system":
                $filename = 'system.log';
                break;
            case "exception":
                $filename = 'exception.log';
                break;
            case "debug":
                $filename = 'debug.log';
                break;
            default:
                return "Log file is not valid. Log file name is " . $filename;
        }
        $pathLogfile = $this->directoryList->getPath('log') . DIRECTORY_SEPARATOR . $filename;
        //tail the length file content
        $lengthBefore = 500000;
        try {
            $contents = '';
            $handle = $this->driver->fileOpen($pathLogfile, 'r');
            fseek($handle, -$lengthBefore, SEEK_END);
            if (!$handle) {
                return "Log file is not readable or does not exist at this moment. File path is "
                    . $pathLogfile;
            }

            if ($this->driver->stat($pathLogfile)['size'] > 0) {
                $contents = $this->driver->fileReadLine(
                    $handle,
                    $this->driver->stat($pathLogfile)['size']
                );
                if ($contents === false) {
                    return "Log file is not readable or does not exist at this moment. File path is "
                        . $pathLogfile;
                }
                $this->driver->fileClose($handle);
            }
            return $contents;
        } catch (\Exception $e) {
            return $e->getMessage() . $pathLogfile;
        }
    }

    /**
     * Check if file exists in email or archive folder
     *
     * @param string $filename
     * @return boolean
     * @throws FileSystemException
     */
    public function isFilePathExistWithFallback(string $filename): bool
    {
        $emailPath = $this->getOutputFolder() . DIRECTORY_SEPARATOR . $filename;
        $archivePath = $this->getArchiveFolder() . DIRECTORY_SEPARATOR . $filename;
        return $this->driver->isFile($emailPath) || $this->driver->isFile($archivePath);
    }
}
