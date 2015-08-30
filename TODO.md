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
- [ ] Document resolvers
- [ ] Add optional namespace argument to `Mustache::setTemplatePath()`?
    - [X] Remove setTemplatePath(), setSeparator(), setSuffix() from Mustache
    - [ ] DefaultResolver will now incorporate Namespaced resolver
    - [ ] Add "getResolverByType()" to Aggregate
        - What happens if multiple of the same type are present?
    - [ ] Mustache will compose an Aggregate resolver composing a
      Default/Namespaced resolver by default
    - [ ] Update documentation to demonstrate:
        - [ ] Aggregating multiple resolvers
        - [ ] Manipulating paths on the default resolver
        - [ ] Retrieving a resolver by type from the aggregate
        - [ ] Indicate paths should typically be set before injecting a resolver

## Rename package?

- Repo to phly/phly-mustache
- Composer package to phly/phly-mustache
