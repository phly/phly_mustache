# phly-mustache

[![Build Status](https://secure.travis-ci.org/phly/phly_mustache.png?branch=develop)](http://travis-ci.org/phly/phly_mustache)

phly-mustache is a [Mustache](http://mustache.github.com) implementation written
for PHP. It conforms to the principles of mustache, and allows for
extension of the format via pragmas.

In particular, it offers support for [template
inheritance](https://github.com/mustache/spec/pull/75) ala
[hogan.js](https://github.com/twitter/hogan.js), using the `{{<parent}}` syntax.

For full documentation, please visit 
[ReadTheDocs](http://phly_mustache.readthedocs.org/).

The mailing list is at https://groups.google.com/d/forum/phly_mustache

## Installation

Install via composer:

```bash
$ composer require phly/mustache
```

## Usage

Basic usage is:

```php
use Phly\Mustache\Mustache;

require 'vendor/autoload.php';

$mustache = new Mustache();
echo $mustache->render('some-template', $view);
```

By default, phly-mustache will look under the current directory for templates
ending with '.mustache'; you can create a stack of directories to search by
using the setTemplatePath() method:

```php
$mustache
    ->setTemplatePath($path1)
    ->setTemplatePath($path2);
```

In the above, it will search first `$path2`, then `$path1` to resolve the
template.

You may also change the suffix it will use to resolve templates:

```php
$mustache->setSuffix('html'); // use '.html' as the suffix
```

If your templates use pragmas, you must first add pragma handlers to the
renderer. This can be done as follows:

```php
use Phly\Mustache\Pragma\ImplicitIterator as ImplicitIteratorPragma;

$mustache->getRenderer()->addPragma(new ImplicitIteratorPragma());
$mustache->render('template-with-pragma', $view);
```

Views can be either associative arrays or objects. For objects, any public
member, either a property or a method, may be referenced in your template. As an
example:

```php
class View
{
    public $first_name = 'Matthew';

    public $last_name  = "Weier O'Phinney";

    public function full_name()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
```

Any property (or array key) may also refer to a valid callback; in such cases,
the return value of the callback will be used.

```
$view = new stdClass;
$view->first_name = 'Matthew';
$view->last_name  = "Weier O'Phinney";
$view->full_name  = function() use ($view) {
    return $view->first_name . ' ' . $view->last_name;
};
```

Refer to the [documentation](http://phly-mustache.readthedocs.org) for full
usage details.

## Architecture

Phly\Mustache consists of five primary classes:

- **Lexer**: tokenizes mustache syntax.
- **Renderer**: renders a list of tokens, using substitions provided via a view.
- **Pragma**: interface for pragmas, which may modify how tokens are handled
- **Resolver**: resolves a template name to mustache syntax or tokens.
- **Mustache**: facade/gateway class. Tokenizes and renders templates, caches
  tokens, provides partial aliasing, and acts as primary interface for
  end-users.

## Whitespace Stripping

Because this is a very literal compiler, whitespace can sometimes be an issue. A
number of measures have been built in to reduce such issues by stripping
whitespace (primarily newlines) surrounding certain tokens, but they come at a
slight performance penalty.

For markup languages like XML, XHTML or HTML5, you likely will not run into
issues in the final rendered output. As such, you can optionally disable
whitespace stripping:

```php
$mustache->getLexer()->disableStripWhitespace(true);
```

## Caching Tokens

Tokens from parsed templates may be cached for later usage; alternately, a new
instance of phly_mustache may be seeded with cached tokens from a previous
instance. 

To get the list of tokens, use the following:

```php
$tokens = $mustache->getAllTokens();
```

This will return a list of template name/token list pairs, based on the
templates compiled by this instance. You may then seed another instance using
the following:

```php
$mustache->restoreTokens($tokens);
```

This will overwrite any tokens already compiled by that instance.

Since the tokens are template name/token list pairs, you can safely pass them to
`array_merge()`, allowing multiple instances of phly-mustache to build up a large
cache of template tokens. This will greatly improve performance when rendering
templates on subsequent calls — particularly if you cache the tokens in a
memory store such as memcached.

## Pragmas shipped with phly-mustache

### IMPLICIT-ITERATOR

This pragma allows iteration of indexed arrays or Traversable objects with
scalar values, with the option of specifying the iterator "key" to use within
the template. By default, a variable key "." will be replaced by the current
value of the iterator.

A sample template:

```mustache
{{#some_iterable_data}}
    {{.}}
{{/some_iterable_data}}
```

To use an explicit iterator key, specify it via the "iterator" option of the
pragma:

```mustache
{{%IMPLICIT-ITERATOR iterator=bob}}
{{#some_iterable_data}}
    {{bob}}
{{/some_iterable_data}}
```

### SUB-VIEWS

The Sub-Views pragma allows you to implement the two-step view pattern using
Mustache. When active, any variable whose value is an instance of
Phly\Mustache\Pragma\SubView will be substituted by rendering the template and
view that object encapsulates.

The SubView class takes a template name and a view as a constructor:

```php
use Phly\Mustache\Pragma\SubView;
$subView = new SubView('some-partial', array('name' => 'Matthew'));
```

That object is then assigned as a value to a view key:

```php
$view = new stdClass;
$view->content = $subView;
```

The template might look like this:

```mustache
{{!layout}}
{{%SUB-VIEWS}}
<html>
<body>
    {{content}}
</body>
</html>
```

and the partial like this:

```mustache
{{!some-partial}}
Hello, {{name}}!
```

Rendering the view:

```php
use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma\SubViews;

$mustache = new Mustache();
$subViews = new SubViews($mustache);
$rendered = $mustache->render('layout', $view);
```

will result in:

```html
<html>
<body>
    Hello, Matthew!
</body>
</html>
```

Sub views may be nested, and re-used.
