# silverstripe-superlinker-redirection

Requires Silverstripe 5.2+.

Replace for OOTB Silverstripe Redirector Page, using a SuperLink target to select a much wider range of target types (than simple internal/external)

## Requirements

* [silverstripe-framework](https://github.com/silverstripe/silverstripe-framework) ^5

## Installation

`composer require fromholdio/silverstripe-superlinker-redirection`

## Force redirections over live pages

By default a `RedirectionSuperLink` fires only as a **404 fallback**: if a published page already
exists at the redirection's origin URL, that page is served and the redirect never runs.

Set `do_force_redirections` to make redirections take precedence over the site tree — a matching
redirect then fires **even when a live page exists** at the URL. The check runs in `onBeforeInit`,
before the page controller renders (comparable to Misdirection's enforce mode):

```yaml
Fromholdio\SuperLinkerRedirection\Model\RedirectionSuperLink:
  do_force_redirections: true
```

- Default is `false` — the original 404-fallback behaviour is unchanged.
- Reserved paths in `disallowed_redirect_origin_url_paths` (`/admin`, `/dev`, …) are never redirected.
- Site/host scoping still applies: the check runs in controller context and invokes the
  `updateRedirectionsFilter` extension hook, so any multisite `SiteID` narrowing works the same as
  in fallback mode.
- Force mode queries `RedirectionFromRelativeURL` on every request; it's indexed on the model — run
  `dev/build` after upgrading.

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
