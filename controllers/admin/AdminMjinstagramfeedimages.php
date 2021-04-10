<?php
/**
 * Instagram feed via oAuth 
 * @author Michał Jendraszczyk
 * @copyright (c) 2020, Michał Jendraszczyk
 * @license https://mages.pl
 */

include_once(dirname(__FILE__).'/../../mjinstagramfeed.php');

class AdminMjinstagramfeedimagesController extends ModuleAdminController
{
    public $module_name;
    public function __construct()
    {
        $this->module_name = "mjinstagramfeed";
        $getModule = new Mjinstagramfeed();
        if (!empty(Configuration::get($getModule->prefix.'client_id')) 
            && !empty(Configuration::get($getModule->prefix.'client_secret'))
            && !empty(Configuration::get($getModule->prefix.'redirect_uri')))
             {
            parent::__construct();
            Tools::redirectAdmin('index.php?controller=AdminModules&configure=' . $this->module_name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } else {
            Tools::redirectAdmin('index.php?controller=AdminModules&configure=' . $this->module_name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        }
    }
//    public function postProcess()
//    {
////        if (Tools::getValue('status') == 'auth') {
////            echo "auth()";
////            exit();
////        } else {
////            Tools::redirectAdmin('index.php?controller=AdminModules&configure=' . $this->module_name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
////        }
//    }
}