<?php
namespace adjai\backender\models;

use adjai\backender\core\Core;
use adjai\backender\core\DBModel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Backup extends DBModel
{
    // Function to save the database and directories to a zip file
    static function save($name = '') {
        if ($name === '') $name = date('YmdHis');
        $zipFile = BACKUPS_DIRECTORY . $name . '.zip';
        $sqlBackupFile = BACKUPS_DIRECTORY . 'db.sql';
        $latestBackup = self::getLatestBackup();

        // Save the database to an SQL file
        self::saveDatabase($sqlBackupFile);
        $zippedItems = [];
        // Create a new zip archive
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add the SQL file to the zip archive
            $zip->addFile($sqlBackupFile, basename($sqlBackupFile));
            $zippedItems[$sqlBackupFile] = [
                'type' => 'file',
                'md5' => md5_file($sqlBackupFile),
            ];

            // Add the specified directories to the zip archive
            foreach (DIRECTORIES_ADD_TO_BACKUP as $directory) {
                $directoryName = basename($directory);
                $zip->addEmptyDir($directoryName);
                $result = self::addDirectoryToZip($zip, $directory, $directoryName);
                $zippedItems = array_merge($zippedItems, $result);
            }
            $md5 = md5(json_encode($zippedItems));
            $zip->setArchiveComment($md5);

            $zip->close();

            // Remove the SQL file after adding it to the zip archive
            unlink($sqlBackupFile);

            // Check if the new backup is the same as the latest backup
            $ifChanged = true;
            if ($latestBackup) {
                if ($zip->open($latestBackup) === TRUE) {
                    if ($md5 === $zip->getArchiveComment()) {
                        $ifChanged = false;
                        unlink($zipFile);
                    }
                }
            }
            if ($ifChanged) self::limitBackups();
            return $ifChanged ? $zipFile : null;
        }
    }

    // Function to restore the database and directories from a zip file
    static function restore($name) {
        $zipFile = BACKUPS_DIRECTORY . $name . '.zip';

        // Extract the zip archive
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === TRUE) {
            // Create a temporary directory for extraction
            $tempDir = BACKUPS_DIRECTORY . 'temp_' . uniqid() . '/';
            mkdir($tempDir);

            // Extract the contents of the zip archive to the temporary directory
            $zip->extractTo($tempDir);
            $zip->close();

            // Restore the database from the SQL file
            $backupFile = $tempDir . 'db.sql';
            self::restoreDatabase($backupFile);

            // Restore the directories
            foreach (DIRECTORIES_ADD_TO_BACKUP as $directory) {
                $directoryName = basename($directory);
                $restoreDirectory = $tempDir . $directoryName;

                // Remove the existing directory
                self::removeDirectory($directory);

                // Move the restored directory to the original location
                rename($restoreDirectory, $directory);
            }

            // Remove the temporary directory
            self::removeDirectory($tempDir);

            return true;
        } else {
            return false;
        }
    }

    static function saveDatabase($filename) {
        $tables = Core::$db->rawQuery("SHOW TABLES");

        $output = '';
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $output .= "-- Table structure for table `$tableName`\n";
            $output .= "DROP TABLE IF EXISTS `$tableName`;\n";

            $createTable = Core::$db->rawQueryOne("SHOW CREATE TABLE `$tableName`");
            $output .= $createTable['Create Table'] . ";\n\n";

            $rows = Core::$db->rawQuery("SELECT * FROM `$tableName`");
            foreach ($rows as $row) {
                $output .= "INSERT INTO `$tableName` VALUES (";
                foreach ($row as $value) {
                    if (is_null($value)) {
                        $output .= 'NULL,';
                    } else {
                        $value = addslashes($value);
                        $value = str_replace("\n", "\\n", $value);
                        $output .= '"' . $value . '",';
                    }
                }
                $output = rtrim($output, ',');
                $output .= ");\n";
            }
            $output .= "\n";
        }
        file_put_contents($filename, $output);
        return true;
    }

    static function restoreDatabase($filename) {
        $templine = '';
        $lines = file($filename);

        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }

            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';') {
                Core::$db->rawQuery($templine);
                $templine = '';
            }
        }
    }

    private static function getLatestBackup() {
        $backupFiles = glob(BACKUPS_DIRECTORY . '*.zip');
        if (!empty($backupFiles)) {
            return max($backupFiles);
        }
        return null;
    }

    private static function limitBackups() {
        $backupFiles = glob(BACKUPS_DIRECTORY . '*.zip');
        if (count($backupFiles) > BACKUPS_COUNT) {
            array_multisort(
                array_map('filemtime', $backupFiles),
                SORT_ASC,
                $backupFiles
            );
            $backupFilesToDelete = array_slice($backupFiles, 0, -BACKUPS_COUNT);
            foreach ($backupFilesToDelete as $file) {
                unlink($file);
            }
        }
    }

    // Function to remove a directory and its contents recursively
    private static function removeDirectory($directory) {
        if (is_dir($directory)) {
            $files = glob($directory . '/{,.}*', GLOB_BRACE);
            foreach ($files as $file) {
                is_dir($file) ? self::removeDirectory($file) : unlink($file);
            }
            rmdir($directory);
        }
    }

    // Function to add a directory to the zip archive recursively
    private static function addDirectoryToZip($zip, $directory, $zipDirectory) {
        $zippedItems = [];
        $directory = str_replace('\\', '/', $directory);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $name => $file) {
            $filePath = $file->getRealPath();
            $filePath = str_replace('\\', '/', $filePath);
            $relativePath = str_replace($directory, '', $filePath);
            if ($file->isDir()) {
                $zippedItems[$filePath] = [
                    'type' => 'dir',
                ];
                $zip->addEmptyDir($zipDirectory . '/' . $relativePath);
            } else {
                $zippedItems[$filePath] = [
                    'type' => 'file',
                    'md5' => md5_file($filePath),
                ];
                $zip->addFile($filePath, $zipDirectory . '/' . $relativePath);
            }
        }
        return $zippedItems;
    }

}