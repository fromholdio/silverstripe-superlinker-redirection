# SilverStripe SuperLinker Redirection

Advanced redirector pages and standalone redirects using SuperLink targets.

## Overview

Replaces SilverStripe's built-in RedirectorPage with a more powerful system that:
- Supports all SuperLink types (not just internal/external)
- Provides standalone Redirection DataObjects
- Offers HTTP status code selection
- Includes collision detection
- Supports custom URL paths

## Requirements

* SilverStripe CMS ^6.0
* fromholdio/silverstripe-superlinker ^4.0.0
* fromholdio/silverstripe-relativeurlfield ^2.0.0
* fromholdio/silverstripe-hidden-pages ^3.0.0
* fromholdio/silverstripe-hasoneedit ^3.0.1

## Installation

```bash
composer require fromholdio/silverstripe-superlinker-redirection
```

Run dev/build:
```bash
vendor/bin/sake dev/build flush=1
```

## Features

### RedirectionPage

A page type that redirects to any SuperLink target.

**Features**:
- Redirect to any link type (SiteTree, External, Email, File, System, etc.)
- HTTP status code selection (301, 302, 303, 307, 308)
- Hidden from site tree by default
- Automatic redirect on page load

**Usage**:
```php
$redirector = RedirectionPage::create();
$redirector->Title = 'Old Product Page';
$redirector->URLSegment = 'old-product';
$redirector->write();

// Set redirect target
$redirector->RedirectLink()->LinkType = 'sitetree';
$redirector->RedirectLink()->SiteTreeID = $newPage->ID;
$redirector->RedirectLink()->write();

$redirector->publishRecursive();
```

### Redirection DataObject

Standalone redirects not tied to pages.

**Features**:
- Redirect from custom URL paths
- Collision detection with site tree
- HTTP status code selection
- Admin interface for management

**Usage**:
```php
$redirect = Redirection::create();
$redirect->FromURL = '/old-path';
$redirect->StatusCode = 301;
$redirect->write();

// Set redirect target
$redirect->RedirectLink()->LinkType = 'external';
$redirect->RedirectLink()->ExternalURL = 'https://example.com/new-path';
$redirect->RedirectLink()->write();
```

## Configuration

### HTTP Status Codes

Available status codes:
- **301** - Moved Permanently (default for SEO)
- **302** - Found (temporary redirect)
- **303** - See Other
- **307** - Temporary Redirect (preserves method)
- **308** - Permanent Redirect (preserves method)

### Hiding RedirectionPage from Site Tree

```yaml
Fromholdio\SuperLinkerRedirection\Pages\RedirectionPage:
  hide_from_hierarchy: true
  hide_from_cms_menu: false
```

## Usage Examples

### Example 1: Redirect Old Page to New Page

```php
$redirector = RedirectionPage::create([
    'Title' => 'Old About Page',
    'URLSegment' => 'old-about'
]);
$redirector->write();

$redirector->RedirectLink()->LinkType = 'sitetree';
$redirector->RedirectLink()->SiteTreeID = $newAboutPage->ID;
$redirector->RedirectLink()->write();

$redirector->publishRecursive();
```

### Example 2: Redirect to External Site

```php
$redirector = RedirectionPage::create([
    'Title' => 'External Resource',
    'URLSegment' => 'external-resource'
]);
$redirector->write();

$redirector->RedirectLink()->LinkType = 'external';
$redirector->RedirectLink()->ExternalURL = 'https://external-site.com/resource';
$redirector->RedirectLink()->write();

$redirector->publishRecursive();
```

### Example 3: Redirect to File Download

```php
$redirector = RedirectionPage::create([
    'Title' => 'Download Brochure',
    'URLSegment' => 'brochure'
]);
$redirector->write();

$redirector->RedirectLink()->LinkType = 'file';
$redirector->RedirectLink()->FileID = $brochureFile->ID;
$redirector->RedirectLink()->write();

$redirector->publishRecursive();
```

### Example 4: Standalone Redirect

```php
$redirect = Redirection::create([
    'FromURL' => '/old-blog/2020/article',
    'StatusCode' => 301
]);
$redirect->write();

$redirect->RedirectLink()->LinkType = 'sitetree';
$redirect->RedirectLink()->SiteTreeID = $newArticlePage->ID;
$redirect->RedirectLink()->write();
```

## Admin Interface

Redirections can be managed through the CMS:
1. Navigate to the Redirections admin
2. Add new redirections
3. Set source URL and target link
4. Choose HTTP status code
5. Save

## Migration from RedirectorPage

To migrate existing RedirectorPage instances:

```php
use SilverStripe\CMS\Model\RedirectorPage as CoreRedirectorPage;
use Fromholdio\SuperLinkerRedirection\Pages\RedirectionPage;

// Get all core redirector pages
$oldRedirectors = CoreRedirectorPage::get();

foreach ($oldRedirectors as $old) {
    $new = RedirectionPage::create([
        'Title' => $old->Title,
        'URLSegment' => $old->URLSegment,
        'ParentID' => $old->ParentID
    ]);
    $new->write();

    // Migrate redirect target
    if ($old->RedirectionType === 'Internal') {
        $new->RedirectLink()->LinkType = 'sitetree';
        $new->RedirectLink()->SiteTreeID = $old->LinkToID;
    } else {
        $new->RedirectLink()->LinkType = 'external';
        $new->RedirectLink()->ExternalURL = $old->ExternalURL;
    }
    $new->RedirectLink()->write();

    $new->publishRecursive();
    $old->doArchive();
}
```

## Known Issues & Todos

- Validation improvements needed
- File has-many redirects field
- Site tree has-many redirects field improvements
- Proper i18n/translations
- Link tracking/syncing

## Documentation

For complete SuperLinker documentation, see:
- [SuperLinker README](../silverstripe-superlinker/README.md)
- [SuperLinker Technical Guide](../silverstripe-superlinker/augment.md)

## License

BSD-3-Clause

## Support

- **GitHub**: https://github.com/fromholdio/silverstripe-superlinker-redirection
- **Issues**: https://github.com/fromholdio/silverstripe-superlinker-redirection/issues
