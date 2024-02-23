<?php

namespace Fromholdio\SuperLinkerMenus\Tasks;

use Fromholdio\SuperLinkerMenus\Model\MenuItem;
use Fromholdio\SuperLinkerRedirection\Model\RedirectionSuperLink;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;

class SuperLinkerMisdirectionUpgradeTask extends BuildTask
{

    protected $enabled = true;

    protected $title = 'SuperLinker Misdirection Migration';

    protected $description = 'Upgrade Misdirection links to SuperLinker redirections';

    private static $segment = 'superlinker-misdirection-upgrade';

    public function run($request)
    {
        $this->log("Starting upgrade...");

        set_time_limit(0);

        $this->migrateMisdirections();

        $this->cleanupTables();

        $this->log("Upgrade done.");
    }

    private function migrateMisdirections()
    {
        $query = "SHOW TABLES LIKE 'LinkMapping'";
        $tableExists = DB::query($query)->value();
        if ($tableExists != null) {
            $records = DB::query("SELECT * FROM LinkMapping WHERE LinkMapping.LinkType = 'Regular Expression'");
            if ($records && $records->numRecords() > 0) {
                $this->log("");
                $this->log("!!! ATTENTION: Regular Expression redirects are not migrated !!!");
                $this->log("Please configure these as htaccess/nginx redirects on your server manually.");
                $this->log("");
            }
            $records = DB::query("SELECT * FROM LinkMapping WHERE LinkMapping.LinkType = 'Simple'");
            if ($records) {
                $this->log("migrate simple misdirections", false);
                foreach ($records as $record) {
                    $link = RedirectionSuperLink::create();
                    if ($record['RedirectType'] == 'Link') {
                        $link->LinkType = 'external';
                        $link->ExternalURL = $record['RedirectLink'];
                    } else {
                        $link->LinkType = 'sitetree';
                        $link->SiteTreeID = (int) $record['RedirectPageID'];
                    }
                    $link->RedirectionFromRelativeURL = $record['MappedLink'];
                    $link->RedirectionResponseCode = $record['ResponseCode'];
                    $link->write();
                    $this->log(".", false);
                }
                $this->log(" done.");
            }
        }
    }

    private function cleanupTables()
    {
        $this->log("clean up tables... ", false);

        $query = "ALTER TABLE LinkMapping RENAME LinkMapping_obsolete";
        DB::query($query);

        $this->log("done.");
    }

    public function log($message, $newLine = true)
    {
        if (Director::is_cli()) {
            echo "{$message}" . ($newLine ? "\n" : "");
        } else {
            echo "{$message}" . ($newLine ? "<br />" : "");
        }
        flush();
    }
}
