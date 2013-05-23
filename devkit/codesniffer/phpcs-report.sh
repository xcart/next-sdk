#!/bin/sh
#
# $Id$
#
# Local PHP Code Shiffer
#
# Usage example:
#
# ./phpcs-report.sh
# ./phpcs-report.sh file1
# ./phpcs-report.sh file1 file2
#

files_list=""

for f in $@; do
    [ -r "$f" ] && files_list=$files_list" "$f;
done

base=`dirname $0`

if [ x"${files_list}" = x ]; then
	files_list='classes';
fi

standard=`pwd`/$base/sniffs/XLite

/usr/bin/env php $base/phpcs.php -s --report=full --standard=$standard $files_list
