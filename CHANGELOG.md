# Changelog

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](http://semver.org/).

## [2.2.1]

### Added

- `do_force_redirections` config (default `false`). When enabled, a matching redirection takes
  precedence over a live page at the same URL — applied in `onBeforeInit`, before the page renders
  (comparable to Misdirection's enforce mode). The default 404-fallback behaviour is unchanged.
- Index on `RedirectionSuperLink.RedirectionFromRelativeURL` (force mode queries it every request);
  run `dev/build` after upgrading.
- Uniqueness validation for standalone redirection origin URLs.

## [1.0.0]

Initial project release
