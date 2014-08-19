#!/bin/sh
echo "PHP CheckStyle script"
php run.php --src ./test --outdir ./checkstyle_result --config default.cfg.xml --format html,xml --linecount
