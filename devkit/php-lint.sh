#!/bin/sh

log=`find . -type f -name '*.php' -exec /usr/bin/env php -l '{}' ';' | grep -v 'No syntax '`;

if [ x"${log}" != x ]; then
	echo $log
	exit 1;
else
	echo "No syntax errors detected in PHP scripts";
fi

