# FROPCO ESO

## Notes

This is a tool written in PHP to pull data from ESO's servers and display it on a webpage. It was written by Kevin Kerr for Age of Mythology Fropco, which has now shut down. This script was not written for distribution. It was written specifically for AoMF and therefore should be considered an unfinished release. It has only been tested on two servers, and absolutely no support will be provided. Furthermore, I have no plans to continue to develop this script, I am simply providing it to the community for their enjoyment and use. You can change the skins, edit the php, or take out parts of it. I don't care. It would be nice if you left me a little credit though.

<http://fropco.com> will most likely contain any updates, but don't expect any. Feel free to mirror this file, or modify and then mirror it.

It is build with a simple little skin system, templates can be edited in the templates folder, and the main layout is done in index.php and the CSS file.

## Installation

This script is written in php4. The database needs to be MySQL 3 or later. This script has ONLY been tested on Linux machines running Apache. THe first thing you must do is to edit mysql.php. Then you can run sql_queries.php. Immediately afterwards delete that file!

If you want the graphs for game details to work you will need to get the JpGraph library. Just copy over the files from the src folder.

The detailed top1k list uses cron jobs and mySQL to update the top 1000 detailed list. You'll have to edit the cron/cronjobs.php to have the correct directory for the other files fropco eso uses. Then you will need to place that script in a protected folder and user cron jobs to run it. Personally I put apache password protection on the cron folder and then used the cron manager to run the program "curl" on the script. The script requires a POST value not a GET value which adds just a little bit of security, but it can still easily enough be ran, so its important you also add an HTTP password to the directory. Here is how I ran my cron jobs, curl is usually installed on linux systems.

`curl -u user:pass -d cron=TopK -d game=aom http://fropco.com/eso/cron/cronjobs.php`

`curl -u user:pass -d cron=TopK -d game=aomx http://fropco.com/eso/cron/cronjobs.php`

## Bugs

If you find a bug, fix it. This is designed as a release for people that already know php to modify for their usage. I realize the script isn't as well designed as it might have been, and it runs a bit slower than I'd like. Clean it up if you want.

If your time is off, the function to edit would be ParseTime() in engine.php