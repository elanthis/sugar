VERSION := 0.81

DESTDIR := /usr/share/php

all:
	@echo 'Targets are'
	@echo '  dist    (generate distribution tarball)'
	@echo '  gendoc  (generate functionr reference documentation)'
	@echo '  manual  (generate DocBook manual)'
	@echo '  phpdoc  (generate Sugar API reference)'
	@echo '  install (install to DESTDIR)'

dist:
	-[ -d php-sugar-$(VERSION) ] && rm -fr php-sugar-$(VERSION)/
	mkdir php-sugar-$(VERSION)/
	cp Sugar.php README LICENSE NEWS Makefile php-sugar-$(VERSION)/

	mkdir php-sugar-$(VERSION)/tools/
	cp tools/gen-doc.php tools/gen-doc.tpl php-sugar-$(VERSION)/tools/

	mkdir php-sugar-$(VERSION)/doc/
	cp doc/sugardoc.css php-sugar-$(VERSION)/doc/

	mkdir php-sugar-$(VERSION)/Sugar/
	cp Sugar/*.php php-sugar-$(VERSION)/Sugar/

	mkdir php-sugar-$(VERSION)/test/
	cp test/index.php php-sugar-$(VERSION)/test/

	mkdir php-sugar-$(VERSION)/test/plugins/
	cp test/plugins/*.php php-sugar-$(VERSION)/test/plugins/

	mkdir php-sugar-$(VERSION)/test/templates/
	cp test/templates/*.tpl php-sugar-$(VERSION)/test/templates/

	mkdir php-sugar-$(VERSION)/test/templates/cache/

	tar -zcf php-sugar-$(VERSION).tgz php-sugar-$(VERSION)/
	rm -fr php-sugar-$(VERSION)/

gendoc:
	[ -d doc ] || mkdir doc
	php tools/gen-doc.php > doc/reference.html

manual:
	xmlto -o doc/manual xhtml sugar-manual.xml

phpdoc:
	phpdoc -ti 'Sugar Template Engine' -o HTML:frames:default -f Sugar.php \
			-q -d Sugar -t doc/phpdoc

install:
	mkdir -p $(DESTDIR)/Sugar
	cp Sugar/*php $(DESTDIR)/Sugar
	cp Sugar.php $(DESTDIR)/Sugar.php
