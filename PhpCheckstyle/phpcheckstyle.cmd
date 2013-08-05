echo "PHP Checkstyle script"
php -c C:\ms4w\Apache\cgi-bin\php.ini run.php --src ./test/issue53.php --outdir ./checkstyle_result --config default.cfg.xml --format html,xml --linecount --debug
pause
