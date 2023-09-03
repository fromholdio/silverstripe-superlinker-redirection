<?php

namespace Fromholdio\SuperLinkerRedirection\Model;

use Fromholdio\RelativeURLField\Forms\RelativeURLField;
use Fromholdio\SuperLinker\Model\SuperLink;
use Fromholdio\SuperLinkerRedirection\Admin\RedirectionSuperLinksAdmin;
use Fromholdio\SuperLinkerRedirection\Pages\RedirectionPage;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

class RedirectionSuperLink extends SuperLink
{
    private static $table_name = 'RedirectionSuperLink';
    private static $singular_name = 'Redirection';
    private static $plural_name = 'Redirections';

    private static $db = [
        'RedirectionFromRelativeURL' => 'Varchar',
        'RedirectionResponseCode' => 'Int'
    ];

    private static $belongs_to = [
        'RedirectionPage' => RedirectionPage::class . '.RedirectionSuperLink'
    ];

    private static $redirection_default_response_code = 301;

    private static $redirection_response_codes = [
        301 => 'Moved permanently',
        302 => 'Temporary redirect'
    ];

    private static $disallowed_redirect_origin_url_paths = [
        '/admin',
        '/Security',
        '/CMSSecurity',
        '/dev',
        '/graphql'
    ];

    private static $field_labels = [
        'RedirectionFromRelativeURL' => 'Origin URL',
        'RedirectionResponseCode' => 'Redirect type',
        'LinkType' => 'Destination type'
    ];

    private static $settings = [
        'link_text' => false,
        'open_in_new' => false,
        'no_follow' => false
    ];

    private static $types = [
        'email' => null,
        'phone' => null,
        'nolink' => null,
        'globalanchor' => null,
        'file' => [
            'use_upload_field' => true,
            'allow_uploads' => true,
            'allow_force_download' => false
        ]
    ];

    private static $summary_fields = [
        'getFormattedOriginURL' => 'Origin URL',
        'URL' => 'Target URL',
        'ResponseCodeLabel' => 'Redirect type',
        'TypeLabel' => 'Link type'
    ];

    private static $searchable_fields = [
        'RedirectionFromRelativeURL',
        'ResponseCode'
    ];

    public function getFormattedOriginURL(): DBHTMLText
    {
        $url = $this->getOriginURL();
        $url = empty($url) ? '' : '<strong>' . $url . '</strong>';
        /** @var DBHTMLText $field */
        $field = DBField::create_field('HTMLFragment', $url);
        return $field;
    }

    public function getOriginURL(): ?string
    {
        return $this->isConfiguredByRedirectionPage()
            ? ltrim($this->getComponent('RedirectionPage')?->regularLink(), '/')
            : $this->getField('RedirectionFromRelativeURL');
    }

    public function getOriginAbsoluteURL(): ?string
    {
        return $this->isConfiguredByRedirectionPage()
            ? ltrim($this->getComponent('RedirectionPage')?->regularAbsoluteLink(), '/')
            : Controller::join_links(
                $this->getBaseAbsoluteURL(),
                $this->getField('RedirectionFromRelativeURL')
            );
    }

    public function isConfiguredByRedirectionPage(): bool
    {
        return $this->getComponent('RedirectionPage')?->exists();
    }

    public function getResponseCode(): int
    {
        $code = (int) $this->getField('RedirectionResponseCode');
        $codes = $this->getAvailableResponseCodes();
        return empty($codes[$code])
            ? $this->getDefaultResponseCode()
            : $code;
    }

    public function getResponseCodeLabel(bool $doIncludeCode = true): string
    {
        $codes = $this->getAvailableResponseCodes();
        $code = $this->getResponseCode();
        if (empty($codes[$code])) {
            $label = '-';
        } else {
            $label = $codes[$code];
            if ($doIncludeCode) {
                $label = $code . ' - ' . $label;
            }
        }
        return $label;
    }

    protected function getDefaultResponseCode(): int
    {
        $code = static::config()->get('redirection_default_response_code');
        $this->extend('updateDefaultResponseCode', $code);
        return $code;
    }

    protected function getAvailableResponseCodes(): array
    {
        $codes = static::config()->get('redirection_response_codes');
        $this->extend('updateAvailableResponseCodes', $codes);
        return array_filter($codes);
    }

    public function getBaseAbsoluteURL(): ?string
    {
        $url = Director::absoluteBaseURL();
        $this->extend('updateBaseAbsoluteURL', $url);
        return $url;
    }

    public function isCMSFieldsReadonly(): bool
    {
        $curr = Controller::curr();
        $isReadonly = $this->isConfiguredByRedirectionPage()
            && ($curr instanceof CMSPageEditController);
        $this->extend('updateIsCMSFieldsReadonly', $isReadonly);
        return $isReadonly;
    }

    public function getCMSLinkFields(string $fieldPrefix = ''): FieldList
    {
        if (!$this->isInDB() && !empty($this->getField('SiteTreeID'))) {
            $this->setField('LinkType', 'sitetree');
        }

        $fromURLField = RelativeURLField::create(
            $fieldPrefix . 'RedirectionFromRelativeURL',
            $this->fieldLabel('RedirectionFromRelativeURL')
        );

        if ($this->isCMSFieldsReadonly())
        {
            $fromURLField->setBaseURL($this->getOriginAbsoluteURL());
            $fromURLField->setReadonly(true);

            $link = $this->getComponent('RedirectionPage')?->CMSEditLink() ?? null;
            $link = empty($link) ? 'Redirection Page' : '<a href="' . $link . '" target="_blank">' . 'Redirection Page</a>';
            $message = "<p style='margin-bottom:0;'>This redirection is managed in the site tree with a $link.<br>"
                . "To change its Origin URL, you will need to move the page to a different location in the site tree.</p>";
            $messageField = FieldGroup::create(
                'Note',
                LiteralField::create($fieldPrefix . 'RedirectionPageMessage', $message)
            );
            $messageField->setName($fieldPrefix . 'RedirectionPageMessageGroup');

            $codeField = ReadonlyField::create(
                $fieldPrefix . 'RedirectionResponseCodeReadOnly',
                $this->fieldLabel('RedirectionResponseCode'),
                $this->getResponseCodeLabel()
            );

            $fields = FieldList::create(
                $codeField,
                $fromURLField,
                $messageField
            );

            $url = $this->getAbsoluteURL();
            if (!empty($url)) {
                $toURLField = RelativeURLField::create(
                    $fieldPrefix . 'DestinationURL',
                    $this->fieldLabel('DestinationURL')
                );
                $toURLField->setBaseURL($url);
                $toURLField->setReadonly(true);
                $fields->push($toURLField);
            }

            $this->extend('updateCMSLinkFields', $fields, $fieldPrefix);
        }
        else {
            $fields = parent::getCMSLinkFields($fieldPrefix);

            $codeField = OptionsetField::create(
                $fieldPrefix . 'RedirectionResponseCode',
                $this->fieldLabel('RedirectionResponseCode'),
                $this->getAvailableResponseCodes(),
                $this->getDefaultResponseCode()
            );
            $fields->unshift($codeField);

            $fromURLField->setBaseURL($this->getBaseAbsoluteURL());
            $fields->insertAfter($fieldPrefix . 'RedirectionResponseCode', $fromURLField);
        }

        return $fields;
    }

    public function canDelete($member = null)
    {
        if ($this->isConfiguredByRedirectionPage()) {
            if (Controller::curr() instanceof RedirectionSuperLinksAdmin) {
                return false;
            }
        }
        return parent::canDelete($member);
    }
}
