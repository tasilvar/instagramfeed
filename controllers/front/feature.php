<?php
/**
 * Instagram feed via oAuth
 * @author Michał Jendraszczyk
 * @copyright (c) 2020, Michał Jendraszczyk
 * @license https://mages.pl
 */

include_once(dirname(__FILE__) . '/../../mjinstagramfeed.php');

class MjinstagramfeedFeatureModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        $this->className = 'Mjinstagramfeed';
        $this->name = (new Mjinstagramfeed())->name;
        $this->module = $this->name;
        $this->prefix = $this->name.'_';
        $this->bootstrap = true;
        parent::__construct();
        
        $wyroznione_posty = (array)@array_reverse(unserialize(Configuration::get($this->prefix.'feature_posts_store')));
        $posty = array();
        if (count($wyroznione_posty) > 0) {
            foreach ($wyroznione_posty as $post) {
                $sql_post = "SELECT * FROM "._DB_PREFIX_."mj_simple_instagram WHERE id_mj_simple_instagram = '".$post."'";
                $posty[] = @DB::getInstance()->ExecuteS($sql_post, 1, 0)[0];
            }
        }
        
        $json = json_encode(($posty));
        echo ($json);
        exit();
    }

    public function init()
    {
        parent::init();
    }
}
