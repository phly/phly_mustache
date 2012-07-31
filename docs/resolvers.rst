Resolvers
=========

Resolvers are a feature specific to ``Phly\Mustache``'s implementation. What a
resolver does is accept a template name, and return either the Mustache content,
or a set of tokens that Phly\\Mustache understands.

.. _resolvers-resolver-interface:

The ResolverInterface
---------------------

All resolvers simply implement ``Phly\Mustache\Resolver\ResolverInterface``,
which looks like this:

.. code-block:: php

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
         \*/
        public function resolve($template);
    }

You can attach a ``ResolverInterface`` implementation to the ``Mustache``
instance, using the ``setResolver()`` method:

.. code-block:: php

    $mustache->setResolver($resolver);

At that point, that resolver will be used. If a resolver returns a boolean
``false`` value, ``Mustache`` will raise a ``TemplateNotFoundException``.

To access the current resolver -- for example, to manipulate its state or set
configuration -- you can retrieve it from the ``Mustache`` instance as well:

.. code-block:: php

    $resolver = $mustache->getResolver();


.. _resolvers-default-resolver:

The DefaultResolver
-------------------

``Phly\Mustache`` comes with one resolver,
``Phly\Mustache\Resolver\DefaultResolver``. This implementation is a
filesystem-based implementation, and looks for a template within an internal
stack of templates. If found, it returns the content of that template.

It has three features you can manipulate:

* Filesystem directory separator, via ``setSeparator()``.
* File suffix, via ``setSuffix()``.
* Template path stack, via ``setTemplatePath()` and ``clearTemplatePath()`` (the
  latter removes all paths from the stack).

As this is the default implementation, the ``setSuffix()`` and
``setTemplatePath()`` methods have proxy methods in the main ``Mustache`` class
as well.

One interesting use case for manipulating the filesystem directory separator is
to allow using "dot notation" for template names, and having each segment map to
a directory:

.. code-block:: php

    $mustache->getResolver()->setSeparator('.');

    // Render foo/bar.mustache:
    echo $mustache->render('foo.bar');

.. _resolvers-use-cases:

Use Cases
---------

As the ``DefaultResolver`` provides a reasonable default, what other uses exist
for resolvers? Potential reasons for alternate implementations include:

* Caching resolvers. A resolver could pull from the filesystem on first
  invocation, but then cache any compiled tokens when complete.
* Database-backed resolvers. Store templates in a relational or document
  database.

Basically, resolvers provide a convenient extension point for providing Mustache
template content and/or tokens to the system.
