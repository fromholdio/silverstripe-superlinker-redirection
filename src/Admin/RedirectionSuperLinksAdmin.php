<?php

namespace Fromholdio\SuperLinkerRedirection\Admin;

use Fromholdio\SuperLinkerRedirection\Model\RedirectionSuperLink;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use Symbiote\GridFieldExtensions\GridFieldConfigurablePaginator;

class RedirectionSuperLinksAdmin extends ModelAdmin
{
    private static $managed_models = [
        RedirectionSuperLink::class
    ];

    private static $url_segment = 'redirections';
    private static $menu_title = 'Redirections';
    private static $menu_icon_class = 'font-icon-switch';

    public $showImportForm = false;

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();
        $config->removeComponentsByType([
            GridFieldExportButton::class,
            GridFieldPaginator::class,
            GridFieldPrintButton::class
        ]);
        $config->addComponent(GridFieldConfigurablePaginator::create());
        return $config;
    }
}
