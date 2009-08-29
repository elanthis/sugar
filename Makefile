VERSION := 0.82

DESTDIR := /usr/share/php

all:
	@echo 'Targets are'
	@echo '  dist    (generate distribution tarball)'
	@echo '  gendoc  (generate functionr reference documentation)'
	@echo '  manual  (generate DocBook manual)'
	@echo '  phpdoc  (generate Sugar API reference)'
	@echo '  install (install to DESTDIR)'

dist:
	-[ -d sugar-$(VERSION) ] && rm -fr sugar-$(VERSION)/
	mkdir sugar-$(VERSION)/
	cp Sugar.php README LICENSE NEWS Makefile sugar-$(VERSION)/

	mkdir sugar-$(VERSION)/tools/
	cp tools/gen-doc.php tools/gen-doc.tpl sugar-$(VERSION)/tools/

	mkdir sugar-$(VERSION)/doc/
	cp doc/sugardoc.css doc/sugar-manual.xml sugar-$(VERSION)/doc/

	mkdir sugar-$(VERSION)/Sugar/
	cp Sugar/*.php sugar-$(VERSION)/Sugar/

	mkdir sugar-$(VERSION)/test/
	cp test/index.php sugar-$(VERSION)/test/

	mkdir sugar-$(VERSION)/test/plugins/
	cp test/plugins/*.php sugar-$(VERSION)/test/plugins/

	mkdir sugar-$(VERSION)/test/templates/
	cp test/templates/*.tpl sugar-$(VERSION)/test/templates/

	mkdir sugar-$(VERSION)/test/templates/cache/

	tar -zcf sugar-$(VERSION).tar.gz sugar-$(VERSION)/
	rm -fr sugar-$(VERSION)/

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
