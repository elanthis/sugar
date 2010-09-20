Sugar Roadmap
=============

Version 1.0
-----------

Version 0.86
~~~~~~~~~~~~

+ NO MORE SYNTAX CHANGES!!!
+ Object-oriented API for plugin functions, modifiers
+ Add Sugar_Chunk class instad of array for base execution unit

Version 0.90
~~~~~~~~~~~~

+ Make opcode integers instead of strings
+ Calling display() on a template should not be part of the same cache
  output as a currently running display; that is black magic; add a way
  to execute a template inside the cache output of another, via Runtime

Version 0.91
~~~~~~~~~~~~

+ Thoroughly document syntax
+ Thoroughly document built-ins
+ Thoroughly document standard functions
+ Thoroughly document client API
+ Thoroughly document extension API

Final 1.0
~~~~~~~~~

+ Critical fixes only!

Version 1.2
-----------

+ Smarty compatibility mode ?
+ Custom blocks ?
+ Compiler function extensions ?

Version 1.4
-----------

+ Multi-level caching ?

Version 2.0
-----------

+ remove deprecated features
+ generate cacheable bytecode (if PHP allows it by then)
  may need compilation to PHP, but only if we don't have to
  keep the file around (because Smarty does this and it
  breaks far, far too easily)
