VERSION := 0.80

DESTDIR := /usr/share/php

dist:
	-[ -d php-sugar-$(VERSION) ] && rm -fr php-sugar-$(VERSION)/
	mkdir php-sugar-$(VERSION)/
	cp Sugar.php README LICENSE NEWS Makefile gen-doc.php gen-doc.tpl php-sugar-$(VERSION)/
	mkdir php-sugar-$(VERSION)/Sugar/
	cp Sugar/*.php php-sugar-$(VERSION)/Sugar/
	mkdir php-sugar-$(VERSION)/test/
	cp test/index.php php-sugar-$(VERSION)/test/
	mkdir php-sugar-$(VERSION)/test/templates/
	mkdir php-sugar-$(VERSION)/test/templates/cache/
	cp test/templates/*.tpl php-sugar-$(VERSION)/test/templates/
	cp test/plugins/*.php php-sugar-$(VERSION)/test/plugins/
	tar -zcf php-sugar-$(VERSION).tgz php-sugar-$(VERSION)/
	rm -fr php-sugar-$(VERSION)/

gendoc:
	[ -d doc ] || mkdir doc/
	php gen-doc.php > reference.html

phpdoc:
	phpdoc -o HTML:frames:earthli -f Sugar.php -d Sugar -t doc

install:
	mkdir -p $(DESTDIR)/Sugar
	cp Sugar/*php $(DESTDIR)/Sugar
	cp Sugar.php $(DESTDIR)/Sugar.php
