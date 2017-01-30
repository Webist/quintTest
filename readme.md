FTP file download and reader.

### Install 
run composer  
`php composer.phar install`

### How to use it

1. Edit file names in file src/exercise.php   
 `$localFile = 'exercise.txt';`  
 `$remoteFile = 'exercise.txt';`
 
2. Edit connection parameters in file src/exercise.php   
 `$ftp->connect('test.nl');`  
 `$ftp->login('user', 'passwd');`

3. run   (command line)
 `php src/exercise.php`
    
    
### Requirements
PHP >= 7.1  
composer