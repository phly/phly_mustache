# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.0.0 - TBD

### Added

- [#32](https://github.com/phly/mustache/pull/32) adds
  `Phly\Mustache\Exception\ExceptionInterface`.
- [#34](https://github.com/phly/mustache/pull/34) adds a joint MkDocs/Bookdown
  build chain for the documentation, simplifying documentation creation and
  rendering.
- [#33](https://github.com/phly/mustache/pull/33) adds `AggregateResolver` and
  namespace support to `DefaultResolver`. In `DefaultResolver`, it adds the new
  method `getNamespaces()` and `addTemplatePath()`. `Mustache` now composes an
  `AggregateResolver` by default, which in turn composes a `DefaultResolver` at
  low priority.
- [#38](https://github.com/phly/mustache/pull/38) updates `Mustache`:
  - The constructor now can accept an `AggregateResolver` instance.
  - `getResolver()` now *always* returns an `AggregateResolver` instance.
- [#35](https://github.com/phly/mustache/pull/35) adds:
  - `Phly\Mustache\Pragma\PragmaInterface`, which replaces
    `Phly\Mustache\Pragma`, removing `getRenderer()` from the interface, and
    adding an additional argument, `Phly\Mustache\Mustache $mustache` to the
    `handle()` method. `handle()` was renamed to `render()`.
  - `Phly\Mustache\Pragma\PragmaNameAndTokensTrait`, which replaces
    `Phly\Mustache\Pragma\AbstractPragma`, removing the methods dealing with the
    renderer.
  - `Phly\Mustache\Pragma\PragmaCollection`, which aggregates pragmas.
  - `Phly\Mustache\Mustache::getPragmas()`, which returns a `PragmaCollection`
    instance.
- [#37](https://github.com/phly/mustache/pull/37) adds a new pragma,
  `CONTEXTUAL-ESCAPE`, handled by `Phly\Mustache\Pragma\ContextualEscape`. It
  allows specifying an escape context for variables using the syntax
  `{{varname|context}}`, and supports the contexts `html`, `attr`, `js`, `css`,
  and `url`. When encountered, it will pass the current value for the variable
  to the appropriate `Zend\Escaper\Escaper` method in order to escape it.

### Deprecated

- Nothing.

### Removed

- [#32](https://github.com/phly/mustache/pull/32) removes the
  `Phly\Mustache\Exception` interface, in favor of
  `Phly\Mustache\Exception\ExceptionInterface`.
- [#34](https://github.com/phly/mustache/pull/34) removes ReStructured Text
  documentation in favor of Markdown.
- [#33](https://github.com/phly/mustache/pull/33) removes the method
  `setTemplatePath()` from the `DefaultResolver`, and removes the methods
  `setTemplatePath()`, `setSuffix()`, and `getSuffix()` from `Mustache`.
- [#35](https://github.com/phly/mustache/pull/35) removes:
  - `Phly\Mustache\Pragma`, in favor of `Phly\Mustache\Pragma\PragmaInterface`;
    the new interface removes the `getRenderer()` method, and renames the
    `handle()` method to `render()`.
  - All public methods in `Phly\Mustache\Renderer` related to adding, removing,
    and manipulating pragmas; these are now managed by `Mustache::getPragmas()`.
  - The `Mustache` argument to the `Phly\Mustache\Pragma\SubViews` constructor
    was removed, as the `Mustache` instance is now passed to the `handle()`
    method.
- [#38](https://github.com/phly/mustache/pull/38) updates `Mustache` to remove
  the `setResolver()` method; an `AggregateResolver` can be added at
  construction if desired.
- [#41](https://github.com/phly/mustache/pull/41) removes all protected methods
  and properties, making the private.
- [#40](https://github.com/phly/mustache/pull/40) modifies the
  `PragmaInterface::render()` signature to remove the `$token` and `$data`
  arguments, and replace them with `array $tokenStruct`.
- [#37](https://github.com/phly/mustache/pull/37) removes the ability to specify
  a custom escaper callback; it now only accepts `Zend\Escaper\Escaper`
  instances.

### Fixed

- [#31](https://github.com/phly/mustache/pull/31) refactors the project to:
    - Require PHP 5.5+. Some features from 5.4 have been specifically adopted
      (short array syntax), and more will be added in the future.
    - Follow [PSR-4](http://www.php-fig.org/psr/psr-4/).
    - Use PHP_CodeSniffer for coding standards checks.
    - Modernize the Travis configuration, and use docker builds, a proper build
      matrix with conditional tests based on environment, etc.
- [#32](https://github.com/phly/mustache/pull/32) refactors exceptions, having
  them extend appropriate SPL exceptions.
- [#40](https://github.com/phly/mustache/pull/40) fixes the `Lexer` to only
  delegate to a composed `PragmaInterface` instance *if* the given pragma has
  been matched in the current scope.
