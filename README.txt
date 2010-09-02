Phly\Mustache
=============
Phly\Mustache is a Mustache (http://mustache.github.com) implementation written
for PHP 5.3+. It conforms to the principles of mustache, and allows for
extension of the format via pragmas.

At this time, it has support for the following:
 - Renders string templates
 - Renders file templates
 - Can use object properties for substitutions
 - Can use method return value for substitutions
 - Template may use conditionals
 - Conditional is skipped if value is false
 - Conditional is skipped if value is empty
 - Template iterates arrays
 - Template iterates traversable objects
 - Higher order sections render inside out
 - Template will dereference nested arrays
 - Template will dereference nested objects
 - Inverted sections render on empty values
 - Renders partials
 - Allows aliasing partials
 - Escapes standard characters
 - Triple mustaches prevent escaping
 - Honors implicit iterator pragma
 - Allows setting alternate template suffix
 - Strips comments from rendered output
 - Allows specifying alternate delimiters
 - Alternate delimiters set in section only apply to that section
 - Alternate delimiters apply to child sections
 - Alternate delimiters do not carry to partials
 - Pragmas are section specific
 - Pragmas do not extend to partials
 - Handles recursive partials
 - Lexer strips unwanted whitespace from tokens

Architecture
============
Phly\Mustache consists of four primary classes:

 - Lexer: tokenizes a template
 - Renderer: renders a list of tokens, using substitions provided via a view
 - Pragma: interface for pragmas, which may modify how tokens are handled
 - Mustache: facade/gateway class. Tokenizes and renders templates, caches
   tokens, provides partial aliasing, and acts as primary interface for
   end-users.

Usage
=====
Usage is fairly straightforward:

    include '/path/to/library/Phly/Mustache/_autoload.php';
    use Phly\Mustache\Mustache;

    $mustache = new Mustache();
    echo $mustache->render('some-template', $view);

By default, phly_mustache will look under the current directory for templates
ending with '.mustache'; you can create a stack of directories to search by
using the setTemplatePath() method:

    $mustache->setTemplatePath($path1)
             ->setTemplatePath($path2);

In the above, it will search first $path2, then $path1 to resolve the template.

You may also change the suffix it will use to resolve templates:

    $mustache->setSuffix('html'); // use '.html' as the suffix

If your templates use pragmas, you must first add pragma handlers to the
renderer. This can be done as follows:

    use Phly\Mustache\Pragma\ImplicitIterator as ImplicitIteratorPragma;
    $mustache->getRenderer()->addPragma(new ImplicitIteratorPragma());
    $mustache->render('template-with-pragma', $view);

Views can be either associative arrays or objects. For objects, any public
member, either a property or a method, may be referenced in your template. As an
example:

    class View
    {
        public $first_name = 'Matthew';

        public $last_name  = "Weier O'Phinney";

        public function full_name()
        {
            return $this->first_name . ' ' . $this->last_name;
        }
    }

Any property (or array key) may also refer to a valid callback; in such cases,
the return value of the callback will be used.

    $view = new \stdClass;
    $view->first_name = 'Matthew';
    $view->last_name  = "Weier O'Phinney";
    $view->full_name  = function() use ($view) {
        return $view->first_name . ' ' . $view->last_name;
    };

The following sections detail unique and/or advanced features of phly_mustache.

Autoloading
===========
phly_mustache follows the PSR-0 standard for class naming conventions, meaning
any PSR-0-compliant class loader will work. To simplify things out of the box,
the component contains an "_autoload.php" file which will register an autoloader
for the phly_mustache component with spl_autoload. You can simply include that
file, and start using phly_mustache.

Higher Order Sections
=====================
"Higher order sections" refer to callbacks that return callbacks. As an example,
consider the following template:

    {{#bolder}}Hi {{name}}.{{/bolder}}

and the following view:
    
    $view = new \stdClass;
    $view->name = 'Tater';
    $view->bolder = function() {
        return function($text, $renderer) {
            return '<b>' . $renderer($text) . '</b>';
        };
    };

In this case, the contents of the section, "Hi {{name}}." will be passed as the
first argument to the section, and a callback capable of rendering will be
passed as the second (this is basically a closure that curries in the current
Renderer object and calls the appropriate method). This allows you to re-use a
given "section" in order to create re-usable capabilities; think of them like
"view helpers" in systems like Zend_View, Solar_View, Savant, etc.

Partials
========
Partials are a basic form of inclusion within Mustache; anytime you find you
have re-usable bits of templates, move them into a partial, and refer to the
partial from the parent template.

Typically, you will only reference partials within your templates, using
standard syntax:

    {{>partial-name}}

However, you may optionally pass a list of partials when rendering. When you do
so, the list should be a set of alias/template pairs:

    $mustache->render($template, array(
        'winnings' => 'user-winnings',
    ));

In the above example, 'winnings' refers to the template
"user-winnings.mustache". Thus, within the $template being rendered, you may
refer to the following partial:

    {{>winnings}}

and it will resolve to the appropriate aliased template.

A few things to remember when using partials:

 - The parent template may change tag delimiters, but if you want to use the
   same delimiters in your partial, you will need to make the same declaration.
 - The parent template may utilize one or more pragmas, but those declarations
   will not perist to the partial; if you want those pragmas, you must reference
   them in your partial.

Basically, partials render in their own scope. If you remember that one rule,
you should have no problems.

Pragmas
=======
Pragmas are tags of the form:

 {{%PRAGMA-NAME option=value}}

where options are key/value pairs, and are entirely optional. Pragmas are
user-defined, and can be used to extend and/or modify the capabilities of the
renderer.

Pragmas should implement Phly\Mustache\Pragma, which defines methods for
retrieving the pragma name (used during registration of the pragma, and
referenced by templates; this is case sensitive currently), determining whether
or not the pragma can intercept rendering of a specific token type, and handling
the token. 

Pragmas should be registered _before_ rendering any template that references
them. 

    $this->mustache->getRenderer()->addPragma($pragmaObject);
    // ...
    $this->mustache->render(/*...*/);

When declared in a template, they exist for the duration of the current
scope, which means:

 - If declared in a section, they apply to that section and any child sections
   *only*
 - If declared for a file, they apply to that file and all child sections *only*
 - Pragmas are never passed on to partials; each partial is rendered with an
   empty set of pragmas, and must declare any pragmas it requires for
   appropriate rendering.

An example is the "IMPLICIT-ITERATOR" pragma, which is included with this
distribution. This pragma allows iteration of indexed arrays or Traversable
objects with scalar values, with the option of specifying the iterator "key" to
use within the template. You can review

    library/Phly/Mustache/Pragma/ImplicitIterator.php 

for details on how it accomplishes this, as well as the unit test

    PhlyTest\Mustache\MustacheTest::testHonorsImplicitIteratorPragma() 

for details on usage.

Caching Tokens
==============
Tokens from parsed templates may be cached for later usage; alternately, a new
instance of phly_mustache may be seeded with cached tokens from a previous
instance. 

To get the list of tokens, use the following:

    $tokens = $mustache->getAllTokens();

This will return a list of template name/token list pairs, based on the
templates compiled by this instance. You may then seed another instance using
the following:

    $mustache->restoreTokens($tokens);

This will overwrite any tokens already compiled by that instance.

Since the tokens are template name/token list pairs, you can safely pass them to
array_merge(), allowing multiple instances of phly_mustache to build up a large
cache of template tokens. This will greatly improve performance when rendering
templates on subsequent calls -- particularly if you cache the tokens in a
memory store such as memcached.
