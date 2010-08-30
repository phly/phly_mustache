Phly\Mustache
=============
Phly\Mustache is a Mustache (http://mustache.github.com) implementation written
for PHP 5.3+. It conforms to the principles of mustache, and allows for
extension of the format via pragmas.

Architecture
============
Phly\Mustache consists of three primary classes:

 - Lexer: tokenizes a template
 - Renderer: renders a list of tokens, using substitions provided via a view
 - Mustache: facade/gateway class. Tokenizes and renders templates, caches
   tokens, provides partial aliasing, and acts as primary interface for
   end-users.

Pragmas
=======
Pragmas are tags of the form:

 {{%PRAGMA-NAME option=value}}

where options are key/value pairs, and are entirely optional. Pragmas are
user-defined, and can be used to extend the capabilities of the renderer.

Pragmas should implement Phly\Mustache\Pragma, which defines methods for
retrieving the pragma name (used during registration of the pragma, and
referenced by templates; this is case sensitive currently), determining whether
or not the pragma can intercept rendering of a specific token type, and handling
the token. 

Pragmas should be registered _before_ rendering any template that references
them.

An example is the "IMPLICIT-ITERATOR" pragma, which is included with this
distribution. This pragma allows iteration of indexed arrays or Traversable
objects with scalar values, with the option of specifying the iterator "key" to
use within the template. You can review

    Phly/Mustache/Pragma/ImplicitIterator.php 

for details on how it accomplishes this, as well as the unit test

    PhlyTest\Mustache\MustacheTest::testHonorsImplicitIteratorPragma() 

for details on usage.
