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
    public function onBeforeHTTPError404(HTTPRequest $request): void
    {
        $url = $request->getURL(true);
        $urlPath = Director::makeRelative($url);

        $filter = ['RedirectionFromRelativeURL' => $urlPath];
        $this->getOwner()->invokeWithExtensions('updateRedirectionsFilter', $filter, $urlPath);

        /** @var ?RedirectionSuperLink $redirect */
        $redirect = RedirectionSuperLink::get()->filter($filter)->first();
        if (!empty($redirect))
        {
            $response = HTTPResponse::create()
                ->redirect($redirect->getAbsoluteURL() ?? '', $redirect->getResponseCode());
            throw new HTTPResponse_Exception($response);
        }
    }
}
