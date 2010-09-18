Installation
============

To install Sugar, simply copy the :file:`Sugar.php` file into the PHP
include path.  Then copy the :file:`Sugar/` folder and its contents into the
same folder in which :file:`Sugar.php` is installed.

The default templates folder for the file storage driver is
:file:`./templates/`, relative to the working directory of the application.
The default cache directory is :file:`./templates/cache/` which also must be
writable to work.

Sugar can also be installed using PEAR::

    pear channel-discover pear.php-sugar.net
    pear install sugar/Sugar-alpha
