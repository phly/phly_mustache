# Resolvers

Resolvers are a feature specific to the `phly-mustache` implementation. What a
resolver does is accept a template name, and return either the mustache content,
or a set of tokens that `phly-mustache` understands.

## ResolverInterface

All resolvers implement `Phly\Mustache\Resolver\ResolverInterface`:

```php
namespace Phly\Mustache\Resolver;

interface ResolverInterface
{
    /**
     * Resolve a template name
     *
     * Resolve a template name to mustache content or a set of tokens.
     *
     * @param  string $template
     * @return string|array
     */
    public function resolve($template);
}
```

You can attach a `ResolverInterface` implementation to the `Mustache` instance,
using the `setResolver()` method:

```php
$mustache->setResolver($resolver);
```

At that point, that resolver will be used. If a resolver returns a boolean
`false` value, `Mustache` will raise a `TemplateNotFoundException`.

To access the current resolver — for example, to manipulate its state or set
configuration — you can retrieve it from the `Mustache` instance as well:

```php
$resolver = $mustache->getResolver();
```

By default, `Mustache` composes an `AggregateResolver`, which in turn composes a
`DefaultResolver` at the bottom of the stack.

## DefaultResolver

`Phly\Mustache\Resolver\DefaultResolver`.  is a filesystem-based implementation,
and looks for a template within an internal stack of templates. If found, it
returns the content of that template.

It has three features you can manipulate:

- Filesystem directory separator, via `setSeparator()`.
- File suffix, via `setSuffix()`.
- Template path stack, via `addTemplatePath()`.

`addTemplatePath()` accepts up to two arguments:

- The `$path` to add.
- The `$namespace` under which to add the path; if none is provided, the default
  (fallback) namespace is assumed.

When rendering, and hence resolving, namespaces are denoted with the syntax
`namespace::template`; if no `nameespace::` segment is present, the default
namespace is assumed. Internally, when you attempt to resolve a template, the
`DefaultResolver` will query first the stack of paths representing the
namespace, and then, if not found, the default (fallback) namespace. (In the
case that no namespace was provided, it queries only the default namespace.)

One interesting use case for manipulating the filesystem directory separator is
to allow using "dot notation" for template names, and having each segment map to
a directory:

```php
$mustache->getResolver()->setSeparator('.');

// Render foo/bar.mustache:
echo $mustache->render('foo.bar');
```

## AggregateResolver

`Phly\Mustache\Resolver\AggregateResolver` allows aggregating multiple
resolvers. When resolution is performed, the first resolver to return a
non-false response short-circuits execution, and its response is returned.

The `AggregateResolver` is both countable and iterable, and exposes the
following methods:

- `attach(ResolverInterface $resolver, $priority = 1)`: attach a resolver to the
  aggregate. `AggregateResolver` acts as a priority queue; large numbers have
  higher priority, lower numbers (including negative numbers!) have lower
  priority; resolvers registered at the same priority are executed in the order
  in which they are attached. Use `$priority` to ensure execution order.
- `hasType($type)`: query to see if a resolver of the given type is already
  registered in the aggregate.
- `fetchByType($type)`: retrieve resolvers that match the given type. If only
  one matches, that instance will be returned; if multiple resolvers match,
  they will be returned as an `AggregateResolver`.

## Use Cases

As the `DefaultResolver` provides a reasonable default, what other uses exist
for resolvers? Potential reasons for alternate implementations include:

- Caching resolvers. A resolver could pull from the filesystem on first
  invocation, but then cache any compiled tokens when complete.
- Database-backed resolvers. Store templates in a relational or document
  database.

Basically, resolvers provide a convenient extension point for providing mustache
template content and/or tokens to the system.
