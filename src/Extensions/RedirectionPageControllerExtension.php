<?php

namespace Fromholdio\SuperLinkerRedirection\Extensions;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;

class RedirectionPageControllerExtension extends Extension
{
    private static $url_handlers = [
        '' => 'doredirection'
    ];

    private static $allowed_actions = ['doredirection'];

    public function doredirection(HTTPRequest $request)
    {
        $page = $this->getOwner()->data();
        $targetLink = $page->getRedirectionLink();
        if ($targetLink) {
            return $this->getOwner()->redirect($targetLink, 301);
        }
        return $this->getOwner()->httpError(404);
    }
}
