<?php
/**
 * Instagram feed via oAuth
 * @author Michał Jendraszczyk
 * @copyright (c) 2020, Michał Jendraszczyk
 * @license https://mages.pl
 */

include_once '../../config/config.inc.php';
include_once 'mjinstagramfeed.php';

$cron = new Mjinstagramfeed();
echo "OK (".time()."s)";
