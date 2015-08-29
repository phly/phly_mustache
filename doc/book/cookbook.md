# Cookbook and FAQ

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
instance of `Phly\Mustache\Mustache` may be seeded with cached tokens from a
previous instance. 

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
