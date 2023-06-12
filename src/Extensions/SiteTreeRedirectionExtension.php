<?php

namespace Fromholdio\SuperLinkerRedirection\Extensions;

use Fromholdio\SuperLinkerRedirection\Model\RedirectionSuperLink;
use SilverStripe\CMS\Model\SiteTreeExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

class SiteTreeRedirectionExtension extends SiteTreeExtension
{
    private static $redirections_tab_path = null;

    private static $has_many = [
        'RedirectionSuperLinks' => RedirectionSuperLink::class . '.SiteTree'
    ];

    private static $cascade_deletes = [
        'RedirectionSuperLinks'
    ];


    public function updateCMSFields(FieldList $fields)
    {
        $tabPath = $this->getOwner()->config()->get('redirections_tab_path');
        if (empty($tabPath)) {
            return;
        }

        $redirectsField = GridField::create(
            'RedirectionSuperLinks',
            $this->getOwner()->fieldLabel('RedirectionSuperLinks'),
            $this->getOwner()->getComponents('RedirectionSuperLinks'),
            GridFieldConfig_RecordEditor::create()
        );

        $tab = $fields->findOrMakeTab($tabPath);
        $tab->push($redirectsField);
    }
}
