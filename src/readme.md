Test purpose single file FTP solution.

#### Features
+ Remote (FTP) file existence check
+ FTP file download (save to local drive)
+ File info with lineCount and chunk size.   
Chunk size is the file pointer, so easy to read without memory issues.
+ Output file to the screen via SPLFileObject iteration
+ Processed file will be moved to `backup` folder with timestamp prefix.
+ Running file in a multi-process should be not a problem