# Introduction

`phly-mustache` is a [Mustache](http://mustache.github.com) implementation
written for PHP. It conforms to the principles and [syntax](syntax.md) of
mustache, and allows for extension of the format via [pragmas](pragmas.md).

In addition, it offers one unique feature: template inheritance. The syntax
used follows that of [hogan.js](https://github.com/twitter/hogan.js), using the
`{{<parent}}` syntax.

Mustache is primarily a syntax and *specification* for templating. The
basic concepts are:

* **Templates**, which contain a variety of tokens, delimited with double
  braces, or mustaches: `{{` and `}}`. These typically are used for
  variable substitution, but a variety of simple control structures and
  mechanisms for dealing with iterable or hierarchical datasets are also
  provided.

* **Views**, which provide subsitutions for *templates*. In PHP, these
  can be either associative arrays or objects. Member variables may be
  any valid PHP values, including callbacks; when callbacks are used,
  the return value will be substituted — which provides a mechanism for
  computing values, filtering them, etc.

This guide will take you through the basics of using the `phly-mustache`
library, provide a thorough reference of the syntax supported, and cover all
implementation-specific features.
