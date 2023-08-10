<?php

namespace Fromholdio\SuperLinkerRedirection\Extensions;

use Fromholdio\SuperLinkerRedirection\Model\RedirectionSuperLink;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;

/**
 * This extension applies to FlysystemAssetStore, and ensures that an appropriate redirect response is returned when an
 * asset isn't found and the path matches a {@link RedirectedURL} object.
 */
class AssetRedirectionHandler extends Extension
{
    /**
     * @var array An array of HTTP status codes that should be acted upon if they are returned by the AssetStore.
     */
    private static array $act_upon = [
        404,
    ];

    public function updateResponse(HTTPResponse &$response, string $asset, array $context = []): void
    {
        // Only change the response if the response provided by FlysystemAssetStore matches one we should act on
        if (!in_array($response->getStatusCode(), $this->owner->config()->act_upon)) {
            return;
        }

        // We are unable to progress if there is no current Controller
        if (!Controller::has_curr()) {
            return;
        }

        // Get the current request, then attempt to find a RedirectedURL object that matches
        $controller = Controller::curr();
        $request = $controller->getRequest();

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
        }
    }
}
