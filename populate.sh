#!/bin/bash

for f in $(find $2 -regextype posix-egrep -regex '(.*?)\.(png|jpg|gif|jpeg)$'); do

	curl -H "Cookie: PHPSESSID=$1" 'http://localhost/clene2/api.php?context=post&action=image_insert' -F "image_file=@$f" 2>/dev/null
	#sleep 1
done
