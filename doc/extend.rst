Extending Sugar
---------------

Sugar offers two core means of extending its functionality.  First,
users may register new functions to be used by templates.  Second,
users may over-ride the storage and cache drivers used by the Sugar
engine.

Storage drivers are classes derived from `Sugar_StorageDriver`.  The following
methods must be implemented.  All methods return true on success or
false on error, unless stated otherwise.

+ `Sugar_StorageDriver::stamp(Sugar_Ref $name)`

  Returns the template's timestamp, or false if the specified template
  does not exist.

+ `Sugar_StorageDriver::load(Sugar_Ref $name)`

  Returns the template source.

+ `Sugar_StorageDriver::path(Sugar_Ref $name)`

  Returns a user-friendly name for the template.

The `Sugar_Ref` class describes the requested template name.  It has the
following member variables which are used to distinguish templates:

+ `Sugar_Ref::$full`

  The full path name.

+ `Sugar_Ref::$storageName`

  The name of the storage driver.

+ `Sugar_Ref::$storage`

  The Sugar_StorageDriver object associated with the driver name.

+ `Sugar_Ref::$name`

  The base name of the template.

+ `Sugar_Ref::$cacheId`

  An optional cache identifier (only used for caching).

To register a new storage driver, use the `Sugar::addStorage()` method
of the Sugar object, passing in the desired name and an instance of
the new driver.

    $sugar->addStorage('db', new SugarDatabaseStorage($sugar));

When loading a template, the template name may be prefixed by a
storage driver name.

    $sugar->display('db:homepage');

If not storage driver is specified, the value of the
defaultStorage member variable is used.  By default this is set to
'file' which is the built-in file-based storage driver that comes
with Sugar.  This can be changed.

    $sugar->defaultStorage = 'db';

Cache drivers are classes derived from `Sugar_CacheDriver`.  The following
methods must be implemented.  All methods return true on success or
false on error, unless stated otherwise.

.. function:: Sugar_CacheDriver::stamp(Sugar_Ref $name, $type)

  Returns the cache timestamp, or false if the specified cache does not exist.

.. function:: Sugar_CacheDriver::load(Sugar_Ref $name, $type)

  Loads the specified cache data.

.. funtion:: Sugar_CacheDriver::store(Sugar_Ref $name, $type, array $data)

  Stores the specified cache, or throw  a Sugar_Exception on failure.

.. function:: Sugar_CacheDriver::erase(Sugar_Ref $name, $type)

  Erases the specified cache.

.. function:: Sugar_CacheDriver::clear()

  Erases all caches.

The `$type` parameter is a string, which will either be `'ctpl'` for
compiled templates or `'chtml'` or template caches.

To change the cache driver, set the $cache property of the Sugar
object to an instance of the new driver.

::

    $sugar->cache = new SugarCustomCache($sugar);
