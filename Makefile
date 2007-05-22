VERSION := 0.40

dist:
	-[ -d php-sugar-$(VERSION) ] && rm -fr php-sugar-$(VERSION)/
	mkdir php-sugar-$(VERSION)/
	cp Sugar.php README LICENSE Makefile php-sugar-$(VERSION)/
	mkdir php-sugar-$(VERSION)/Sugar/
	cp Sugar/*.php php-sugar-$(VERSION)/Sugar/
	mkdir php-sugar-$(VERSION)/test/
	cp test.php php-sugar-$(VERSION)/test/
	mkdir php-sugar-$(VERSION)/test/templates/
	cp templates/*.tpl php-sugar-$(VERSION)/test/templates/
	tar -zcf php-sugar-$(VERSION).tgz php-sugar-$(VERSION)/
	rm -fr php-sugar-$(VERSION)/
