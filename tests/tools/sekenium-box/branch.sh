#!/bin/sh

branch="$1"
cd next
git fetch
git checkout $branch
git pull
git clean -f -d
git reset --hard

file="src/classes/XLite/Module/CDev/System/Main.php"

if [ -f "$file" ]
then
    rm $file
else
    echo "Module system already disabled."
fi

cd src
./restoredb standalone demo admin
#./restoredb standalone site

loop="Y"

while [ $loop = "Y" ]
do
    php admin.php >cache.log
    if grep --quiet "Re-building cache" cache.log; then
        cat cache.log
    else
        loop="N"
        rm cache.log
    fi
done