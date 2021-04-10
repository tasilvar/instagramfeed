<?php
/**
 * Instagram feed via oAuth
 * @author Michał Jendraszczyk
 * @copyright (c) 2020, Michał Jendraszczyk
 * @license https://mages.pl
 */

include_once(dirname(__FILE__) . '/../../mjinstagramfeed.php');

class MjinstagramfeedShowModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        $this->className = 'Mjinstagramfeed';
        $this->name = (new Mjinstagramfeed())->name;
        $this->module = $this->name;
        $this->prefix = $this->name.'_';
        $this->bootstrap = true;
        parent::__construct();
        $sql = "SELECT * FROM "._DB_PREFIX_."mj_simple_instagram ORDER BY date_add DESC LIMIT 4";
        $result = DB::getInstance()->ExecuteS($sql, 1, 0);
        $json = json_encode($result);
        echo ($json);
        exit();
    }

    public function init()
    {
        parent::init();
    }
}
