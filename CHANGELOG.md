# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.0.0 - TBD

Initial tagged release.

### Added

- [#32](https://github.com/phly/mustach/pull/32) adds
  `Phly\Mustache\Exception\ExceptionInterface`.
- [#34](https://github.com/phly/mustach/pull/34) adds a joint MkDocs/Bookdown
  build chain for the documentation, simplifying documentation creation and
  rendering.
- [#33](https://github.com/phly/mustach/pull/33) adds `AggregateResolver` and
  namespace support to `DefaultResolver`. In `DefaultResolver`, it adds the new
  method `getNamespaces()` and `addTemplatePath()`. `Mustache` now composes an
  `AggregateResolver` by default, which in turn composes a `DefaultResolver` at
  low priority.
- [#35](https://github.com/phly/mustach/pull/35) adds:
  - `Phly\Mustache\Pragma\PragmaInterface`, which replaces
    `Phly\Mustache\Pragma`, removing `getRenderer()` from the interface, and
    adding an additional argument, `Phly\Mustache\Mustache $mustache` to the
    `handle()` method.
  - `Phly\Mustache\Pragma\PragmaNameAndTokensTrait`, which replaces
    `Phly\Mustache\Pragma\AbstractPragma`, removing the methods dealing with the
    renderer.
  - `Phly\Mustache\Pragma\PragmaCollection`, which aggregates pragmas.
  - `Phly\Mustache\Mustache::getPragmas()`, which returns a `PragmaCollection`
    instance.

### Deprecated

- Nothing.

### Removed

- [#32](https://github.com/phly/mustach/pull/32) removes the
  `Phly\Mustache\Exception` interface, in favor of
  `Phly\Mustache\Exception\ExceptionInterface`.
- [#34](https://github.com/phly/mustach/pull/34) removes ReStructured Text
  documentation in favor of Markdown.
- [#33](https://github.com/phly/mustach/pull/33) removes the method
  `setTemplatePath()` from the `DefaultResolver`, and removes the methods
  `setTemplatePath()`, `setSuffix()`, and `getSuffix()` from `Mustache`.
- [#35](https://github.com/phly/mustach/pull/35) removes:
  - `Phly\Mustache\Pragma`, in favor of `Phly\Mustache\Pragma\PragmaInterface`;
    the new interface removes the `getRenderer()` method.
  - All public methods in `Phly\Mustache\Renderer` related to adding, removing,
    and manipulating pragmas; these are now managed by `Mustache::getPragmas()`.
  - The `Mustache` argument to the `Phly\Mustache\Pragma\SubViews` constructor
    was removed, as the `Mustache` instance is now passed to the `handle()`
    method.

### Fixed

- [#31](https://github.com/phly/mustach/pull/31) refactors the project to:
    - Require PHP 5.5+. Some features from 5.4 have been specifically adopted
      (short array syntax), and more will be added in the future.
    - Follow [PSR-4](http://www.php-fig.org/psr/psr-4/).
    - Use PHP_CodeSniffer for coding standards checks.
    - Modernize the Travis configuration, and use docker builds, a proper build
      matrix with conditional tests based on environment, etc.
- [#32](https://github.com/phly/mustach/pull/32) refactors exceptions, having
  them extend appropriate SPL exceptions.
