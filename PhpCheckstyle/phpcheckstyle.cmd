echo "PHP Checkstyle script"
php run.php --src ./test/t_and_equal.php --outdir ./checkstyle_result --config default.cfg.xml --format html,xml --linecount --debug
pause
