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

## Documentation

Documentation builds are available at:

- https://phly-mustache.readthedocs.org

You can also build documentation in one of two ways:

- [MkDocs](http://www.mkdocs.org): Execute `mkdocs build` from the repository
  root.
- [Bookdown](http://bookdown.io): Execute `bookdown doc/bookdown.json` from the
  repository root.

In each case, you can use PHP's built-in web server to serve the documentation:

```bash
$ php -S 0.0.0.0:8080 -t doc/html/
```

and then browse to http://localhost:8080/.

## Usage

Basic usage is:

```php
use Phly\Mustache\Mustache;

require 'vendor/autoload.php';

$mustache = new Mustache();
echo $mustache->render('some-template', $view);
```

By default, phly-mustache will look under the current directory for templates
ending with '.mustache'; you can create a stack of directories using the
default resolver:

```php
use Phly\Mustache\Resolver\DefaultResolver;

$resolver = new DefaultResolver(;
$resolver->addTemplatePath($path1);
$resolver->addTemplatePath($path2);

$resolver = $mustache->getResolver()->attach($defaultResolver);
```

In the above, it will search first `$path2`, then `$path1` to resolve the
template.

The default resolver is composed in an aggregate resolver by default; as such,
you can also fetch it by type from the aggregate instead of adding it manually:

```php
use Phly\Mustache\Resolver\DefaultResolver;

$resolver = $mustache->getResolver()->fetchByType(DefaultResolver::class);
```

Template names may be namespaced, using the syntax `namespace::template`:

```php
$resolver->addTemplatePath($path1, 'blog');
$resolver->addTemplatePath($path2, 'contact');
```

Per the above configuratin, rendering the template `contact::index` will resolve
to `$path2`. If it cannot, it will drop back to the default namespace (any paths
registered without a namespace).

You may also change the suffix it will use to resolve templates:

```php
$resolver->setSuffix('html'); // use '.html' as the suffix
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

Refer to the documentation([online](http://phly-mustache.readthedocs.org) /
[local](doc/book/)) for full usage details.

## Architecture

Phly\Mustache consists of five primary classes:

- **Lexer**: tokenizes mustache syntax.
- **Renderer**: renders a list of tokens, using substitions provided via a view.
- **Pragma**: interface for pragmas, which may modify how tokens are handled.
- **Resolver**: resolves a template name to mustache syntax or tokens.
- **Mustache**: facade/gateway class. Tokenizes and renders templates, caches
  tokens, provides partial aliasing, and acts as primary interface for
  end-users.
