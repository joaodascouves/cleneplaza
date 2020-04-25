#!/bin/bash

find $2 -regextype posix-egrep -regex '(.*?)\.(png|jpg|gif|jpeg)$' | tac | xargs -n1 -P2 -I{} curl -H "Cookie: PHPSESSID=$1" http://localhost/clene2/api.php?action=wall_image_insert -F image_file=@{} &>/dev/null
