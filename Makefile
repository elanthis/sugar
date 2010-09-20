VERSION := 0.84

DESTDIR := /usr/share/php

SPHINXBUILD := sphinx-build
ALLSPHINXOPTS := -d .build/doctrees .

all:
	@echo 'Targets are'
	@echo '  dist    (generate distribution tarball)'
	@echo '  gendoc  (generate functionr reference documentation)'
	@echo '  html    (generate HTML manual)'
	@echo '  phpdoc  (generate Sugar API reference)'
	@echo '  install (install to DESTDIR)'
	@echo '  clean   (remove all generated files)'

dist:
	-[ -d 'sugar-$(VERSION)' ] && rm -fr 'sugar-$(VERSION)/'
	mkdir 'sugar-$(VERSION)/'
	cp Sugar.php README LICENSE Makefile 'sugar-$(VERSION)/'

	mkdir 'sugar-$(VERSION)/bin/'
	cp bin/sugardoc 'sugar-$(VERSION)/bin/'

	mkdir 'sugar-$(VERSION)/doc/'
	cp doc/conf.py doc/Makefile doc/*.rst 'sugar-$(VERSION)/doc/'

	mkdir 'sugar-$(VERSION)/Sugar/'
	for file in `find Sugar -name '*.php'` ; do install -D "$$file" "sugar-$(VERSION)/$$file" ; done

	mkdir 'sugar-$(VERSION)/test/'
	cp test/index.php test/Test.php test/run-test 'sugar-$(VERSION)/test/'

	mkdir 'sugar-$(VERSION)/test/plugins/'
	cp test/plugins/*.php 'sugar-$(VERSION)/test/plugins/'

	mkdir 'sugar-$(VERSION)/test/tpl/'
	cp test/tpl/*.tpl 'sugar-$(VERSION)/test/tpl/'

	mkdir 'sugar-$(VERSION)/test/tests/'
	cp test/tests/*.php 'sugar-$(VERSION)/test/tests/'
	cp test/tests/*.tpl 'sugar-$(VERSION)/test/tests/'
	cp test/tests/*.txt 'sugar-$(VERSION)/test/tests/'

	mkdir 'sugar-$(VERSION)/test/cache/'

	tar -zcf 'sugar-$(VERSION).tar.gz' 'sugar-$(VERSION)/'
	rm -fr 'sugar-$(VERSION)/'

gendoc:
	[ -d doc ] || mkdir doc
	php tools/gen-doc.php > doc/reference.html

html:
	( cd doc ; make html )

phpdoc:
	phpdoc -ti 'Sugar Template Engine' -o HTML:frames:default -f Sugar.php \
			-q -d Sugar -t doc/phpdoc

install:
	mkdir -p '$(DESTDIR)/Sugar'
	cp Sugar/*php '$(DESTDIR)/Sugar'
	cp Sugar.php '$(DESTDIR)/Sugar.php'

clean:
	[ -f 'sugar-$(VERSION)' ] && rm -f 'sugar-$(VERSION).tar.gz'
	[ -d '.build' ] && rm -fr .build

.PHONY: all dist gendoc html phpdoc install clean
