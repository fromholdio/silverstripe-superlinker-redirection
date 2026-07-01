<?php

namespace Fromholdio\SuperLinkerRedirection\Extensions;

use Fromholdio\SuperLinkerRedirection\Model\RedirectionSuperLink;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Extension;

class RedirectionHandler extends Extension
{
    /**
     * Fallback behaviour (always active): when a request would otherwise 404, look for a
     * matching redirection and apply it. A Live page at the same URL is served normally,
     * because no 404 occurs and this hook never fires.
     */
    public function onBeforeHTTPError404(HTTPRequest $request): void
    {
        $this->owner->applyRedirectionForRequest($request);
    }

    /**
     * Force behaviour (opt-in via RedirectionSuperLink.do_force_redirections): apply a
     * matching redirection even when a Live page exists at the requested URL, by checking
     * before the page controller initialises. This makes redirections take precedence over
     * the site tree, comparable to Misdirection's enforce mode.
     */
    public function onBeforeInit(): void
    {
        if (!RedirectionSuperLink::config()->get('do_force_redirections'))
        {
            return;
        }
        $this->owner->applyRedirectionForRequest($this->owner->getRequest());
    }

    /**
     * Shared matcher used by both the 404 fallback and the force mode.
     *
     * Site/host scoping (and any other project-specific narrowing) is applied by extensions
     * via the updateRedirectionsFilter hook on the owner controller — so multisite scoping
     * works identically in both modes. Running this in controller context (rather than a
     * standalone middleware) is deliberate: it keeps that hook available.
     */
    public function applyRedirectionForRequest(HTTPRequest $request): void
    {
        $url = $request->getURL(true);
        $urlPath = Director::makeRelative($url);

        if ($this->owner->isDisallowedOriginPath($urlPath))
        {
            return;
        }

        $filter = ['RedirectionFromRelativeURL' => $urlPath];
        $this->owner->invokeWithExtensions('updateRedirectionsFilter', $filter, $urlPath);

        /** @var ?RedirectionSuperLink $redirect */
        $redirect = RedirectionSuperLink::get()->filter($filter)->first();
        if (!empty($redirect) && $redirect->getAbsoluteURL())
        {
            $response = HTTPResponse::create()
                ->redirect($redirect->getAbsoluteURL() ?? '', $redirect->getResponseCode());
            throw new HTTPResponse_Exception($response);
        }
    }

    /**
     * Guards force mode against shadowing reserved paths (admin, security, dev, etc.). Harmless
     * in fallback mode, where those paths don't 404 in the first place.
     */
    public function isDisallowedOriginPath(string $urlPath): bool
    {
        $path = '/' . ltrim($urlPath, '/');
        $disallowed = (array) RedirectionSuperLink::config()->get('disallowed_redirect_origin_url_paths');
        foreach ($disallowed as $prefix)
        {
            if ($path === $prefix || str_starts_with($path, rtrim($prefix, '/') . '/'))
            {
                return true;
            }
        }
        return false;
    }
}
