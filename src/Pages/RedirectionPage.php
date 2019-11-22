<?php

namespace Fromholdio\SuperLinkerRedirection\Pages;

use Fromholdio\SuperLinkerRedirection\Extensions\RedirectionPageExtension;
use Page;
use SilverStripe\Forms\FieldList;

class RedirectionPage extends Page
{
    private static $table_name = 'RedirectionPage';
    private static $singular_name = 'Redirection Page';
    private static $plural_name = 'Redirection Pages';
    private static $icon_class = 'font-icon-p-redirect';

    private static $extensions = [
        RedirectionPageExtension::class
    ];

    public function Link($action = null)
    {
        $link = $this->getRedirectionLink();
        if ($link) {
            return $link;
        }
        return parent::Link($action);
    }

    public function AbsoluteLink($action = null)
    {
        $link = $this->getRedirectionAbsoluteLink();
        if ($link) {
            return $link;
        }
        return parent::AbsoluteLink($action);
    }

    public function getRegularLink($action = null)
    {
        return parent::Link($action);
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(
            function(FieldList $fields)
            {
                $fields->removeByName('Content', true);
                $fields->removeByName('Metadata');

                $fields->addFieldsToTab(
                    'Root.Main',
                    $this->getRedirectionTargetFields()
                );
            }
        );
        return parent::getCMSFields();
    }
}
