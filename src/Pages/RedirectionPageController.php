<?php

namespace Fromholdio\SuperLinkerRedirection\Pages;

use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\FieldType\DBHTMLText;

class RedirectionPageController extends PageController
{
    private static $allowed_actions = ['index'];

    private static $missing_redirect_is_404 = true;

    public function index(HTTPRequest $request): DBHTMLText|HTTPResponse
    {
        /** @var RedirectionPage $page */
        $page = $this->data();
        $isFinished = $this->getResponse()->isFinished();
        if (!$isFinished) {
            if ($page->hasValidTargetSuperLink()) {
                $link = $page->getTargetSuperLink();
                return $this->redirect($link->getURL(), $link->getResponseCode());
            } elseif ($this->config()->missing_redirect_is_404) {
                return $this->httpError(404);
            }
        }
        return parent::handleAction($request, 'handleIndex');
    }

    public function getContent()
    {
        return "<p class=\"message message-setupWithoutRedirect\">" .
            _t(__CLASS__ . '.HASBEENSETUP', 'A redirection page has been set up without anywhere to redirect to.') .
            "</p>";
    }
}
