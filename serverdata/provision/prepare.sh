if [ ! -f /installed-aptkey-php54 ]; then
	touch /installed-aptkey-php54
	wget http://www.dotdeb.org/dotdeb.gpg -nv
    cat dotdeb.gpg | sudo apt-key add -
fi
