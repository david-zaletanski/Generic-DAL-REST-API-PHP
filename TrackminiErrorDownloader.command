#! /bin/sh
# Above line informs that this is a shell script.

echo Opening FTP connection...
# Start of "here" document. Here document allows us to execute within FTP.
ftp <<**
open trackula.me
cd /var/log/apache2/
get error.log /Users/dzale/Desktop/error.log
bye
**
# End of "here" document.
cd /Users/dzale/Desktop/
open -a "Sublime Text" error.log