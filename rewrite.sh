#!/bin/bash

#Set the path to Apache configuration file
path='/etc/apache2/apache2.conf'

#Set rewrite directives with an initial comment
directives=(' ' '# URL rewriting' '<Directory /var/www/html/>' '\t RewriteEngine on' '\t RewriteCond %{REQUEST_FILENAME} !-f' '\t RewriteCond %{REQUEST_FILENAME} !-d' '\t RewriteRule (.*) /index.php/$1 [L]' '</Directory>')

#Put each directive in a line of the configuration file
if [ -f "$path" ]
then
for str in "${directives[@]}"; do
    echo -e $str >> "$path"
done    
echo "Directives added successfully"
else
echo "File not found"
fi