# Usage and Installation 

## Installation

To install `phly-mustache`, require ``phly/mustache`` via
[Composer](http://getcomposer.org):

```bash
$ composer require phly/mustache
```

## Usage

Usage is fairly straightforward:

```php
require 'vendor/autoload.php';

$mustache = new Phly\Mustache\Mustache();
echo $mustache->render('name-of-template', 'view');
```

Alternately, import the classes and/or namespaces you will use:

```php
use Phly\Mustache\Mustache;
require 'vendor/autoload.php';

$mustache = Mustache();
```

By default, `phly-mustache` will look under the current directory for templates
ending with the suffix `.mustache`.

You can create a stack of directories to search by adding template paths,
optionally with namespaces, to the default resolver.

```php
use Phly\Mustache\Resolver\DefaultResolver;

$defaultResolver = $mustache->getResolver()->getByType(DefaultResolver::class);
$defaultResolver->addTemplatePath($path1);
$defaultResolver->addTemplatePath($path2);
```

In the above, it will search first `$path2`, then `$path1` to resolve the template.

Namespaces can be used to limit searches to the namespace specified, falling
back to the default namespace. This is accomplished by specifying the template
in the format `namespace::template`, and registering paths to match only
specific namespaces:

```php
use Phly\Mustache\Resolver\DefaultResolver;

// Either create an instance manually:
$resolver = new DefaultResolver();

// and push it to Mustache:
$mustache->setResolver($resolver);

// or onto the default aggregate composed in Mustache:
$mustache->getResolver()->attach($resolver);

// Or pull from the default aggregate:
$resolver = $mustache->getResolver()->fetchByType(DefaultResolver::class);

// Now, add templates:
$resolver->addTemplatePath('templates'); // default namespace
$resolver->addTemplatePath('templates/blog', 'blog');
$resolver->addTemplatePath('templates/contact', 'contact');

$content = $mustache->render('blog::index');
```

In the above example, the renderer will resolve the path to
`templates/blog/index.mustache`; if the file is not found there, it will fall
back to `templates/index.mustache`. In no circumstance will it resolve to
`templates/contact/index.mustache` for that template name.

You may also change the suffix it will use to resolve templates:

```php
use Phly\Mustache\Mustache;
use Phly\Mustache\Resolver\DefaultResolver;

$mustache = new Mustache();
$defaultResolver = $mustache->getResolver()->getByType(DefaultResolver::class);
$defaultResolver->setSuffix('mst'); // now looks for files ending in ".mst"
```

> ### Resolvers
>
> By default, the `Mustache` instance composes an instance of
> `Phly\Mustache\Resolver\AggregateResolver`, which in turn composes an instance
> of `Phly\Mustache\Resolver\DefaultResolver` at the bottom of the stack. This
> allows you to attach additional resolvers that you want to resolve earlier —
> for example, if you've written a caching resolver, or one that queries a
> database or NoSQL storage — while ensuring you have a working resolver
> out-of-the-box.
>
> You can compose your own, specific resolver by using either
> `Mustache::setResolver()` or `Mustache::getResolver()->attach()`.
