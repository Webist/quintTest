<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * FTP helper, checks if a filename the result set matches
 * @param string $fileName
 * @param array $items
 * @return string|null
 */
function hasFileName(string $fileName, array $items)
{
    foreach ($items as $index => $item) {
        // skip non-file
        if (strpos($index, 'file') === false) {
            continue;
        }

        if (strpos($item, $fileName) !== false) {
            return $fileName;
        }
    }
}

/**
 * Collects file info by reading in chunks
 * @notice, Reads also a single line large file. No limits :-)
 * @param $file
 * @param $chunkSize
 * @return array
 */
function fileInfo(string $file, int $chunkSize): array
{
    $in = fopen($file, 'rb');
    $chunkCount = 0;

    $chunks = [];

    while (!feof($in)) {
        // respect memory via chunkSize
        $string = fgets($in, $chunkSize);
        $chunkCount++;
        // file pointers
        $chunks[$chunkCount] = ['length' => strlen($string)];
    }
    fclose($in);

    return ['file' => $file, 'lineCount' => $chunkCount, 'chunks' => $chunks];
}

/**
 * Output file content
 * @param array $fileInfo
 * @return string
 */
function readLocalFile(array $fileInfo)
{
    $iterateFile = new \SplFileObject($fileInfo['file']);
    ob_start();
    foreach ($fileInfo['chunks'] as $chunk) {
        echo $iterateFile->fread($chunk['length']);
    }

    return ob_get_clean();
}


$localFile = 'exercise.txt';
$remoteFile = 'exercise.txt';

const FILE_READ_CHUNK_SIZE = 200;
const BACKUP_DIR = 'backup';

try {

// ftp connect
    $ftp = new \FtpClient\FtpClient();
    $ftp->connect('test.nl');
    $ftp->login('username', 'passwd');

    $remoteItems = $ftp->rawlist('.');

    if (!hasFileName($remoteFile, $remoteItems)) {
        throw new \FtpClient\FtpException(
            sprintf('No accessible file `%s` on the FTP server', $remoteFile)
        );
    }

    $tempFile = fopen($localFile, 'wb');
    $ftp->fget($tempFile, $remoteFile, FTP_ASCII);
    $ftp->close();
    fclose($tempFile);

    // Gather file info
    $fileInfo = fileInfo($localFile, FILE_READ_CHUNK_SIZE);

    // move file to backup directory in a new process without waiting on response
    $backupFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . BACKUP_DIR . DIRECTORY_SEPARATOR . '.'. time() . '_' . basename($localFile);
    shell_exec("mv $localFile $backupFile > /dev/null 2>&1 &");

    // Read file
    echo readLocalFile($fileInfo);

} catch (\FtpClient\FtpException $exception) {
    echo $exception->getMessage();

} catch (Exception $exception) {
    echo $exception->getMessage();
}

