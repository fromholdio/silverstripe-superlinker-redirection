<?php

namespace Fromholdio\SuperLinkerRedirection\Pages;

use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\FieldType\DBHTMLText;

class RedirectionPageController extends PageController
{
    private static $allowed_actions = ['index'];

    public function index(HTTPRequest $request): DBHTMLText|HTTPResponse
    {
        /** @var RedirectionPage $page */
        $page = $this->data();
        $isFinished = $this->getResponse()->isFinished();
        if (!$isFinished && $page->hasValidTargetSuperLink()) {
            $link = $page->getTargetSuperLink();
            $this->redirect($link->getURL(), $link->getResponseCode());
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
