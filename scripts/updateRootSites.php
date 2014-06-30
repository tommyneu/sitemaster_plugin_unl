<?php
use SiteMaster\Plugins\Unl;

ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . '/../../../init.php');

$sites  = new \SiteMaster\Core\Registry\Sites\All();
$parser = new \SiteMaster\Core\Auditor\Parser\HTML5();
$metric = new \SiteMaster\Plugins\Unl\Metric('unl');

foreach ($sites as $site) {
    if (!$scan = $site->getLatestScan(true)) {
        //Don't have a scan yet, skip.
        continue;
    }

    //Download and parse the home page
    if (!$html = @file_get_contents($site->base_url)) {
        echo "\t unable to get HTML for " . $site->base_url . PHP_EOL;
        continue;
    }

    $xpath = $parser->parse($html);
    
    $attributes = \SiteMaster\Plugins\Unl\ScanAttributes::getByScansID($scan->id);
    $root = $metric->getRootSiteURL($xpath);
    if ($root && $attributes) {
        echo 'Updating root site for ' . $site->base_url . ' -- ' . $root . PHP_EOL;
        $attributes->root_site_url = $root;
        $attributes->save();
    } else {
        if (!$root) {
            echo "\t unable to get root site for " . $site->base_url . PHP_EOL;
        }
        
        if (!$attributes) {
            echo "\t unable to get attributes site for " . $site->base_url . PHP_EOL;
        }
    }
    
    //Don't flood servers with requests
    sleep(1);
}
