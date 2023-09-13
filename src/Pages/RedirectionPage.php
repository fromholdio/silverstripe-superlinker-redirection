<?php

namespace Fromholdio\SuperLinkerRedirection\Pages;

use Fromholdio\SuperLinkerRedirection\Model\RedirectionSuperLink;
use Page;
use SGN\HasOneEdit\HasOneEdit;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;

class RedirectionPage extends Page
{
    private static $table_name = 'RedirectionPage';
    private static $singular_name = 'Redirection Page';
    private static $plural_name = 'Redirection Pages';
    private static $icon_class = 'font-icon-p-redirect';

    private static $show_stage_link = false;
    private static $show_live_link = false;

    private static $redirection_cms_tab_path = 'Root.Main';

    private static $has_one = [
        'RedirectionSuperLink' => RedirectionSuperLink::class
    ];

    private static $owns = [
        'RedirectionSuperLink'
    ];

    private static $cascade_deletes = [
        'RedirectionSuperLink'
    ];

    private static $cascade_duplicates = [
        'RedirectionSuperLink'
    ];

    public function getTargetSuperLink(): ?RedirectionSuperLink
    {
        /** @var ?RedirectionSuperLink $linkObj */
        $linkObj = $this->getComponent('RedirectionSuperLink');
        return $linkObj?->isLinkValid() ? $linkObj : null;
    }

    public function hasValidTargetSuperLink(): bool
    {
        return !is_null($this->getTargetSuperLink());
    }

    public function Link($action = null): ?string
    {
        $link = $this->getTargetSuperLink()?->getURL() ?? $this->regularLink($action);
        $this->extend('updateLink', $link, $action);
        return $link;
    }

    public function AbsoluteLink($action = null): ?string
    {
        $link = $this->getTargetSuperLink()?->getAbsoluteURL() ?? $this->regularAbsoluteLink($action);
        $this->extend('updateAbsoluteLink', $link, $action);
        return $link;
    }

    public function regularLink(?string $action = null): ?string
    {
        return parent::Link($action);
    }

    public function regularAbsoluteLink(?string $action = null): ?string
    {
        if ($this->hasMethod('alternateAbsoluteLink')) {
            return $this->alternateAbsoluteLink($action);
        }
        return Director::absoluteURL((string) $this->regularLink($action));
    }

    public function subPagesToCache(): array
    {
        return [];
    }

    public function getCMSRedirectionFields(): FieldList
    {
        $fieldPrefix = 'RedirectionSuperLink' . HasOneEdit::FIELD_SEPARATOR;
        /** @var FieldList $fields */
        $fields = $this->getComponent('RedirectionSuperLink')->getCMSLinkFields($fieldPrefix);

        $fields->removeByName($fieldPrefix . 'RedirectionFromRelativeURL');
        $fields->removeByName($fieldPrefix . 'RedirectionPageMessageGroup');

        $headerField = HeaderField::create(
            'RedirectionHeader',
            'This page will redirect users to a different URL:',
            2
        );
        $headerField->setAttribute('style', 'padding-top: 12px; padding-bottom: 6px;');
        $fields->unshift($headerField);

        $this->extend('updateCMSRedirectionFields', $fields);
        return $fields;
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function(FieldList $fields)
        {
            $fields->removeByName('Content', true);
            $fields->removeByName('Metadata');

            $tabPath = static::config()->get('redirection_cms_tab_path');
            if (empty($tabPath)) return $fields;
            $redirectFields = $this->getCMSRedirectionFields();
            $fields->addFieldsToTab($tabPath, $redirectFields->toArray());

            return $fields;
        });
        $fields = parent::getCMSFields();
        return $fields;
    }
}
