<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';


/**
 * FTP helper, checks if a remote file is accessible
 * @param string $fileName
 * @param array $items
 * @return bool
 */
/**
 * @param string $fileName
 * @param array $items
 * @return string|null
 */
function remoteFileReadable(string $fileName, array $items)
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
function fileInfo(string $file, $chunkSize): array
{
    $in = fopen($file, 'rb');
    $chunkCount = 0;

    $chunks = [];

    while (!feof($in)) {
        // respect memory via chunkSize
        $string = fgets($in, $chunkSize);
        $chunkCount++;

        $chunks[$chunkCount] = ['length' => strlen($string)];
    }
    fclose($in);

    return ['file' => $file, 'lineCount' => $chunkCount, 'chunks' => $chunks];
}

/**
 * Read/Output file by chunk length
 * @param $fileInfo
 */
function readLocalFile($fileInfo): void
{
    $iterateFile = new \SplFileObject($fileInfo['file']);

    foreach ($fileInfo['chunks'] as $chunk) {
        echo $iterateFile->fread($chunk['length']);
    }
}


$localFile = 'exercise.txt';
$remoteFile = 'exercise.txt';

const FILE_READ_CHUNK_SIZE = 200;
const OUTPUT_SIZE = 4;
const BACKUP_DIR = 'backup';

try {

// ftp connect
    $ftp = new \FtpClient\FtpClient();
    $ftp->connect('test.nl');
    $ftp->login('user', 'passwd');

    $remoteItems = $ftp->rawlist('.');

    if (!remoteFileReadable($remoteFile, $remoteItems)) {
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
    // Read file
    readLocalFile($fileInfo);

    // move file to backup
    rename($localFile,
        dirname(__DIR__) . DIRECTORY_SEPARATOR . BACKUP_DIR . DIRECTORY_SEPARATOR . time() . '_' . basename($localFile));

} catch (\FtpClient\FtpException $exception) {
    echo $exception->getMessage();

} catch (Exception $exception) {
    echo $exception->getMessage();
}







