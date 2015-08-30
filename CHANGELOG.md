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
