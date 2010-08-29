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

where options are key/value pairs, and are entirely optional. The renderer
handles pragmas internally. For each token, it will check to see if a pragma
handler can render the token; if so, that value will be used, otherwise it will
use the default rendering.

Each pragma has a method that will be called as a handler; extend the class and
override the appropriate method(s) in order to implement your pragma.

The following tokens currently accept pragmas:

 - Lexer::TOKEN_CONTENT: handleContentPragma()
 - Lexer::TOKEN_VARIABLE: handleVariablePragma()
 - Lexer::TOKEN_VARIABLE_RAW: handleRawVariablePragma()
 - Lexer::TOKEN_SECTION: handleSectionPragma()
 - Lexer::TOKEN_SECTION_INVERT: handleInvertedSectionPragma()

Currently, the IMPLICIT-ITERATOR pragma is available, allowing iteration of
indexed arrays or Traversable objects with scalar values. These require the
handleVariablePragma() and handleRawVariablePragma() methods.
