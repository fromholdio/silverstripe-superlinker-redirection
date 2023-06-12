# silverstripe-superlinker-redirection

Use master/v1.x (compatible with SS 4 & 5).

This branch is under active development. It **will** change and break, likely including namespaces.

Replace for OOTB Silverstripe Redirector Page, using a SuperLink target to select a much wider range of target types (than simple internal/external)

## Requirements

* [silverstripe-framework](https://github.com/silverstripe/silverstripe-framework) ^4 or ^5 (untested with 4)

## Installation

`composer require fromholdio/silverstripe-superlinker-redirection`

## Todos

- Validation
- File has-many redirects field if possible
- Resolve issues with site tree has-many redirects field
  - Make link type auto-defined and readonly
  - Make destination readonly
  - Maybe custom gridfield, ideally with modal
- Finalise naming, consolidate & finalise
- Remove yml config currently in place for ease of development
- Proper i18n/_t()/translations
- Migration script that converts OOTB `RedirectorPage`s to `RedirectionPage`s
- Migration script for v1 to v2
- Documentation/readme
- Link tracking/syncing
