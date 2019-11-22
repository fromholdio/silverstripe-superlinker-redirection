<?php

namespace Fromholdio\SuperLinkerRedirection\Pages;

use Fromholdio\SuperLinkerRedirection\Extensions\RedirectionPageControllerExtension;
use PageController;

class RedirectionPageController extends PageController
{
    private static $extensions = [
        RedirectionPageControllerExtension::class
    ];
}
