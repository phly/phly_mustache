# TODO

## Tests 

- [ ] tokenization

## Whitespace handling
- [ ] Some lingering issues with too much indentation and/or extra newlines; all
- [ ] such tests are marked "whitespace-issues"

## Better packaging

- [X] Switch to PSR-4
    - [X] autoload
    - [X] autoload-dev
    - [X] Make sure all docs are updated as well!
- [X] .gitattribute file to exclude docs, tests
- [X] new Travis matrix
- [X] phpcs
- [X] LICENSE -> LICENSE.md
- [X] Update license docblocks of all classes
- [X] Change homepage to point to RTD
- [X] change test bootstrapping
- [X] Remove documentation from README and link to docs
- [X] Add installation instructions to README, usage docs
- [X] Push minimum supported version to 5.5

## Change docs to markdown

- [X] Remove sphinx build chain
    - [X] conf.py
    - [X] make.bat
    - [X] Makefile
    - [X] _static, api directories
- [X] Convert existing rst files to markdown
- [X] Add bookdown.json
- [X] Document using bookdown to generate docs
- [X] Add build rules for mkdocs.yml
- [X] Add mkdocs.yml

## New resolvers

Branch: feature/resolvers

- [X] Namespaced template resolver
- [X] Aggregate resolver
- [X] Document resolvers
- [X] Add optional namespace argument to `Mustache::setTemplatePath()`?
    - [X] Remove setTemplatePath(), setSeparator(), setSuffix() from Mustache
    - [X] DefaultResolver will now incorporate Namespaced resolver
    - [X] Rename `setTemplatePath()` to `addTemplatePath()` (resolves #24)
    - [X] Add "getResolverByType()" to Aggregate
    - [X] Mustache will compose an Aggregate resolver composing a
      Default/Namespaced resolver by default
    - [X] Update documentation to demonstrate:
        - [X] Aggregating multiple resolvers
        - [X] Manipulating paths on the default resolver
        - [X] Retrieving a resolver by type from the aggregate
        - [X] Indicate paths should typically be set before injecting a resolver
- [X] Ensure default resolver is at priority less than 1
- [X] Change documentation of resolvers to inject a new DefaultResolver, instead
    of fetching by type?

## Pragmas

- [X] Move interface into Pragma namespace.
- [X] Remove renderer awareness from Pragma interface; add new argument to
    `handle()`, a `Mustache` instance.
- [X] Replace `AbstractPragma` with a trait.
- [X] Add a `PragmaCollection`.
- [X] Add a `getPragmas()`  method to `Mustache` class.  This will return the
  composed `PragmaCollection`.
- [X] Refactor existing pragmas
    - [X] ImplicitIterator
    - [X] SubViews
- [X] Documentation
    - [X] Update all docs that reference adding pragmas.
    - [X] Document PragmaInterface and writing pragmas.
- [ ] Alter pragma interface to allow lexer usage
    - [ ] add `parse` method that lexer will call
    - [ ] update lexer to query pragmas
    - [ ] rename `handle` to `render`
    - [ ] document changes

## AggregateResolver

- [ ] Is there any need to allow replacing the resolver at this point, if you
  can specify one at higher priority in the Aggregate?

## Escaping [#37]

- [ ] Use `Zend\Escaper` by default.
- [ ] Create a pragma for handling contextual escaping (CSS, JS, URLs)

## Rename package? [#38]

- Repo to phly/phly-mustache
- Composer package to phly/phly-mustache
