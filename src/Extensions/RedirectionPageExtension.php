<?php

namespace Fromholdio\SuperLinkerRedirection\Extensions;

use Fromholdio\MiniGridField\Forms\HasOneMiniGridField;
use Fromholdio\SuperLinkerTargets\Model\Target;
use SilverStripe\CMS\Model\SiteTreeExtension;
use SilverStripe\Forms\HeaderField;

class RedirectionPageExtension extends SiteTreeExtension
{
    private static $description = 'Setup a redirection to any target';

    private static $has_one = [
        'RedirectionTarget' => Target::class
    ];

    private static $owns = [
        'RedirectionTarget'
    ];

    private static $cascade_deletes = [
        'RedirectionTarget'
    ];

    private static $field_labels = [
        'RedirectionTarget' => 'Target'
    ];

    public function getRedirectionLink()
    {
        $target = $this->getOwner()->RedirectionTarget();
        if (!$target || !$target->exists()) {
            return null;
        }
        return $target->Link();
    }

    public function getRedirectionAbsoluteLink()
    {
        $target = $this->getOwner()->RedirectionTarget();
        if (!$target || !$target->exists()) {
            return null;
        }
        return $target->AbsoluteLink();
    }

    public function getRedirectionTargetFields()
    {
        $fields = [
            HeaderField::create(
                'RedirectionHeader',
                'This page will redirect users to another URL',
                2
            ),
            HasOneMiniGridField::create(
                'RedirectionTarget',
                $this->getOwner()->fieldLabel('RedirectionTarget'),
                $this->getOwner()
            )
        ];
        return $fields;
    }

    public function subPagesToCache()
    {
        return [];
    }
}
