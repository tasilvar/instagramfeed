<?php
/**
 * Instagram feed via oAuth
 * @author Michał Jendraszczyk
 * @copyright (c) 2020, Michał Jendraszczyk
 * @license https://mages.pl
 */

class Mjinstagramfeed extends Module
{
    public $prefix;
    public $instagram_url;
    public $fields_form = array();
    
    public function __construct()
    {
        $this->instagram_url = "https://instagram.com";
        $this->name = "mjinstagramfeed";
        $this->tab = 'administration';
        $this->author = "MAGES Michał Jendaszczyk";
        $this->version = "1.0.0";
        
        parent::__construct();
        $this->bootstrap = true;
                
        $this->prefix = $this->name."_";
        
        $this->displayName = $this->l("Instagram feed");
        $this->description = $this->l('It allows you to download photos from Instagram with implemented new Instagram API');
        $this->confirmUninstall= $this->l("Usunąć ? ");
    }
    private function installModuleTab($tabClass, $tabName, $idTabParent)
    {
        $tab = new Tab();
        $tab->name = $tabName;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = $idTabParent;
        $tab->position = 99;
        if (!$tab->save()) {
            return false;
        }
        return true;
    }

    public function uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab) {
            $tab = new Tab($idTab);
            $tab->delete();
        }
        return true;
    }
    
    private function installTables()
    {
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mj_simple_instagram` (
            `id_mj_simple_instagram` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` VARCHAR(256),
            `post_link` VARCHAR(256),
            `title` VARCHAR(256),
            `date_add` DATETIME,
            PRIMARY KEY  (`id_mj_simple_instagram`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($sql as $query) {
            Db::getInstance()->execute($query);
        }
        return true;
    }
    
    public function install()
    {
        return parent::install()
                && $this->registerHook("displayHomeBottomContent")
                && $this->registerHook("displayHome")
                && $this->registerHook("displayFooterProduct")
                && $this->registerHook("displayInstagramFeed")
                && $this->registerHook("displayBackOfficeTop")
                && $this->installModuleTab('AdminMjinstagramfeedimages', array(Configuration::get('PS_LANG_DEFAULT') => 'Instagram feed'), Tab::getIdFromClassName('AdminCatalog'))
                && $this->installTables()
                && Configuration::updateValue($this->prefix."cron", _PS_BASE_URL_.__PS_BASE_URI__."?fc=module&module=mjinstagramfeed&controller=auth");
    }
    public function hookDisplayFooterProduct($params)
    {
        //return $this->hookDisplayHome($params);
    }
    public function hookDisplayInstagramFeed($params)
    {
        return $this->hookDisplayHome($params);
    }
    public function uninstall()
    {
        return parent::uninstall()
                && $this->uninstallModuleTab('AdminMjinstagramfeedimages');
    }
    public function getFormWidget()
    {
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Ustawienia'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Client ID'),
                    'size' => '5',
                    'name' => $this->prefix.'client_id',
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Client Secret'),
                    'size' => '5',
                    'name' => $this->prefix.'client_secret',
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Redirect URI'),
                    'size' => '5',
                    'name' => $this->prefix.'redirect_uri',
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('User name'),
                    'size' => '5',
                    'name' => $this->prefix.'user_name',
                    'required' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ),
        );

        if (Configuration::get($this->prefix.'expires_date')) {
            $expires = Configuration::get($this->prefix.'expires_date');
        } else {
            $expires = '--';
        }
        
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('AAutoryzuj i pobierz zdęcia'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('URL to CRON Job'),
                    'size' => '5',
                    'name' => $this->prefix.'cron',
                    'required' => true,
                ),
                ),
            'buttons' => array(
                    'refreshToken' => array(
                        'title' => $this->l('Refresh token (valid to: '.$expires.')'),
                        'name' => 'refreshToken',
                        'type' => 'submit',
                        'id' => $this->prefix.'refreshToken',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'icon-link'
                    ),
                    'pobierzZdjecia' => array(
                        'title' => $this->l('Authorize and download pictures'),
                        'icon' => 'icon-download',
                        'name' => 'pobierzZdjecia',
                        'id' => $this->prefix.'pobierzZdjecia',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                    ),
                )
            );
        
        $this->fields_form[2]['form'] = array(
        'legend' => array(
            'title' => $this->l('Dane autoryzacyjne'),
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('ID user'),
                'size' => '5',
                'name' => $this->prefix.'user_id',
                'required' => true,
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Access token'),
                'size' => '5',
                'name' => $this->prefix.'access_token',
                'required' => true,
            ),
            ),
        );
          
        $featurePosts = DB::getInstance()->ExecuteS("SELECT * FROM "._DB_PREFIX_."mj_simple_instagram");
        $this->fields_form[3]['form'] = array(
        'legend' => array(
            'title' => $this->l('Feauted posts'),
        ),
        'input' => array(
            array(
                'type' => 'select',
                'label' => $this->l('Select feature posts'),
                'multiple' => true,
                'size' => '5',
                'style' => 'width:100%',
                'name' => $this->prefix.'feature_posts[]',
                'required' => true,
                'options' => array(
                    'query' => $featurePosts,
                    'id' => 'id_mj_simple_instagram',
                    'name' => 'post_link',
                ),
            ),
        ),
        'submit' => array(
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right',
            ),
        );

        $wyroznione_posty = (array)@array_reverse(unserialize(Configuration::get($this->prefix.'feature_posts_store')));
        $kontener_wyroznione_posty = '';
        $pozycje_postu = array();
        foreach ($wyroznione_posty as $post) {
            $sql_post = "SELECT * FROM "._DB_PREFIX_."mj_simple_instagram WHERE id_mj_simple_instagram = '".$post."'";
            $single_post = @DB::getInstance()->ExecuteS($sql_post, 1, 0)[0];

            $kontener_wyroznione_posty =
                array(
                    'type' => 'text',
                    'desc' => 'Pozycja postu',
                    'label' => $this->l('Pozycja postu: '.$single_post['post_link']),
                    'name' => $this->prefix.'pozycja_'.$single_post['id_mj_simple_instagram'],
                    'disabled' => false,
                    'required' => true,
                    'data-val' => $single_post['id_mj_simple_instagram']
                );
            array_push($pozycje_postu, $kontener_wyroznione_posty);
        }
        $this->fields_form[4]['form'] = array(
        'legend' => array(
            'title' => $this->l('Ordering position posts from instagram'),
        ),
            'input' => $pozycje_postu,
            'submit' => array(
            'title' => $this->l('Save'),
            'name' => 'save_pozycje_insta',
            'id' => 'save_pozycje_insta',
            'class' => 'btn btn-default pull-right',
        ));
        return $this->fields_form;
    }
    public function renderForm()
    {
        $this->getFormWidget();

        $form = new HelperForm();

        foreach ($this->fields_form[0]['form']['input'] as $input) {
            $form->tpl_vars['fields_value'][$input['name']] = Tools::getValue($input['name'], Configuration::get($input['name']));
        }
        
        foreach ($this->fields_form[1]['form']['input'] as $input) {
            $form->tpl_vars['fields_value'][$input['name']] = Tools::getValue($input['name'], Configuration::get($input['name']));
        }
        foreach ($this->fields_form[2]['form']['input'] as $input) {
            $form->tpl_vars['fields_value'][$input['name']] = Tools::getValue($input['name'], Configuration::get($input['name']));
        }
        
        foreach ($this->fields_form[4]['form']['input'] as $input) {
            $form->tpl_vars['fields_value'][$input['name']] = Tools::getValue($input['name'], Configuration::get($input['name']));
        }
        $form->tpl_vars['fields_value'][$this->prefix . 'feature_posts[]'] =  Tools::getValue($this->prefix . 'feature_posts[]', unserialize(Configuration::get($this->prefix.'feature_posts_store')));
        
        return $form->generateForm($this->fields_form);
    }
    public function postProcess()
    {
        $this->getFormWidget();
        if (Tools::isSubmit('pobierzZdjecia')) {
            Tools::redirect("https://api.instagram.com/oauth/authorize?client_id=".Configuration::get($this->prefix.'client_id')."&redirect_uri=".Configuration::get($this->prefix.'redirect_uri')."&scope=user_profile,user_media&response_type=code");
        }
        if (Tools::isSubmit('refreshToken')) {
            $url = "https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=".Configuration::get($this->prefix.'access_token');
            $getRefreshLongToken = new Mjinstagramfeed();
            $responseRefreshLongToken = $getRefreshLongToken->makeCurl($url, "GET", null);
            $resultRefreshLongToken = (json_decode($responseRefreshLongToken, 1));
            Configuration::updateValue($this->prefix.'access_token', $resultRefreshLongToken['access_token']);
            Configuration::updateValue($this->prefix.'expires_in', $resultRefreshLongToken['expires_in']);
            
            $days = round($resultRefreshLongToken['expires_in']/(3600*24));
            $dateExpires = date("Y-m-d", strtotime(date('Y-m-d').'+ '.$days.' days'));
            Configuration::updateValue($this->prefix.'expires_date', $dateExpires);
//            print_r($resultRefreshLongToken);
//            echo "TEST: ".Configuration::get($this->prefix.'expires_date');
//            exit();
        }

        if (Tools::isSubmit('submitAddconfiguration')) {
            foreach ($this->fields_form[0]['form']['input'] as $input) {
                Configuration::updateValue($input['name'], Tools::getValue($input['name']));
            }
            
            foreach ($this->fields_form[1]['form']['input'] as $input) {
                Configuration::updateValue($input['name'], Tools::getValue($input['name']));
            }
            foreach ($this->fields_form[4]['form']['input'] as $input) {
                Configuration::updateValue($input['name'], Tools::getValue($input['name']));

                $sql = "UPDATE "._DB_PREFIX_."mj_simple_instagram SET pozycja = '".Tools::getValue('mjinstagramfeed_pozycja_'.$input['data-val'])."' WHERE id_mj_simple_instagram = '".$input['data-val']."'";
                DB::getInstance()->Execute($sql, 1, 0);
            }
            Configuration::updateValue($this->prefix.'cron', Configuration::get($this->prefix.'redirect_uri').'?access_token='.Configuration::get($this->prefix.'access_token'));
            
            Configuration::updateValue($this->prefix.'feature_posts_store', serialize(Tools::getValue($this->prefix.'feature_posts')));
            return $this->displayConfirmation($this->l('Ustawienia zapisane'));
        }
    }
    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }
    public function makeCurl($url, $method, $params)
    {
        $cu = curl_init();

        curl_setopt($cu, CURLOPT_URL, $url);
        curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cu, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
        if ($params) {
            curl_setopt($cu, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        return curl_exec($cu);
    }
    public function hookDisplayHome($params)
    {
        return $this->hookdisplayHomeBottomContent($params);
    }
    public function hookdisplayHomeBottomContent($params)
    {
        $sql = "SELECT * FROM "._DB_PREFIX_."mj_simple_instagram ORDER BY date_add DESC LIMIT 4";
        $result = DB::getInstance()->ExecuteS($sql, 1, 0);
        
        $this->context->smarty->assign(
            array(
                'instagram_feeds' => $result,
                'instagram_profile' => $this->instagram_url.'/'.Configuration::get($this->prefix.'user_name'),
                'user_name' => Configuration::get($this->prefix.'user_name'),
            )
        );
        
        // Wyróżnione posty
        $wyroznione_posty = (array)@array_reverse(unserialize(Configuration::get($this->prefix.'feature_posts_store')));
        $posty = array();
        
        if (count($wyroznione_posty) > 0) {
                $sql_post = "SELECT * FROM "._DB_PREFIX_."mj_simple_instagram WHERE id_mj_simple_instagram IN (".implode(",", $wyroznione_posty).") ORDER BY pozycja ASC LIMIT 8";
                $posty = @DB::getInstance()->ExecuteS($sql_post, 1, 0);
        }
       // echo implode(",",$wyroznione_posty);
        
//        echo $sql_post;
//        print_r($posty);
//        exit();
        $this->context->smarty->assign(
            array(
                'wyroznione_posty' => $posty,
            )
        );
        return $this->fetch('module:mjinstagramfeed/views/templates/hook/home_mjinstagramfeed.tpl');
    }
    public function addInstagramFeed($params)
    {
        $sql = "INSERT INTO "._DB_PREFIX_."mj_simple_instagram(post_id, post_link, title, date_add) VALUES('".$params['post_id']."', '".$params['post_link']."', '".$params['title']."', '".$params['date_add']."')";
        return DB::getInstance()->Execute($sql, 1, 0);
    }
    public function checkIfExist($id_post)
    {
        $sql = "SELECT * FROM "._DB_PREFIX_."mj_simple_instagram WHERE post_id = '".$id_post."'";
        return count(DB::getInstance()->ExecuteS($sql, 1, 0));
    }
}
