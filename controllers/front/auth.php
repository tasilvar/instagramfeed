<?php
/**
 * Instagram feed via oAuth 
 * @author Michał Jendraszczyk
 * @copyright (c) 2020, Michał Jendraszczyk
 * @license https://mages.pl
 */

include_once(dirname(__FILE__) . '/../../mjinstagramfeed.php');

class MjinstagramfeedAuthModuleFrontController extends ModuleFrontController
{
    public $_html;
    public $prefix;
    public $display_column_left = false;
    public $authorization;
    public $auth = false;
    public $satatus;
    public $authRedirection = false;

    public function __construct()
    {
        $this->className = 'Mjinstagramfeed';
        $this->name = (new Mjinstagramfeed())->name;
        $this->module = $this->name;
        $this->prefix = $this->name.'_';
        $this->bootstrap = true;
        parent::__construct();
    }

    public function init()
    {
        parent::init();
    }
    
    /**
     * Zwrócenie templatki pod wynik requesta
     */
    public function initContent()
    {
        //http://coodo.jm.projektyc.pl/?fc=module&module=mjinstagramfeed&controller=auth
        //http://localhost:5000/?fc=module&module=mjinstagramfeed&controller=auth
        parent::initContent();
        $this->setTemplate("module:mjinstagramfeed/views/templates/front/auth.tpl");
    }

    /**
     * Procesowanie requesta
     */
    public function postProcess()
    {
        parent::postProcess();

            // Autoryzacja
            if(empty(Tools::getValue('code')) && empty(Tools::getValue('access_token'))) {
               Tools::redirect("https://api.instagram.com/oauth/authorize?client_id=".Configuration::get($this->prefix.'client_id')."&redirect_uri=".Configuration::get($this->prefix.'redirect_uri')."&scope=user_profile,user_media&response_type=code");
           }

            $url = "https://api.instagram.com/oauth/access_token";
            $params = array(
                "client_id" => Configuration::get($this->prefix.'client_id'),
                "client_secret" => Configuration::get($this->prefix.'client_secret'),
                "grant_type" => "authorization_code",
                "redirect_uri" => Configuration::get($this->prefix.'redirect_uri'),
                "code" => Tools::getValue('code')
            );

            /**
             * Pobieranie tokenu
             */
             $getToken = new Mjinstagramfeed();
            if (empty(Tools::getValue('access_token'))) {
                $responseToken = $getToken->make_curl($url, "POST", $params);
                $objectTokenUserId = json_decode($responseToken);
                Configuration::updateValue($this->prefix.'access_token', $objectTokenUserId->access_token);
                /**
                 * Pobieranie długiego tokenu
                 */
                $this->getLongToken(Configuration::get($this->prefix.'client_secret'), $objectTokenUserId->access_token);
            }
            $access_token = Configuration::get($this->prefix.'access_token');
            
            /**
             * Pobieranie ID usera
             */
            if (empty(Configuration::get($this->prefix.'user_id'))) {
                $prepareIdUser = trim(substr($responseToken, strpos($responseToken,'"user_id":')),"}");
                $getIdUser = trim($prepareIdUser, ' "user_id":');
                
                if (is_numeric($getIdUser)) {
                   Configuration::updateValue($this->prefix.'user_id', $getIdUser);
                }
            }
            $getIdUser = Configuration::get($this->prefix.'user_id');

            //GET /{media-id}?fields={fields}&access_token={access-token}
            /**
             * Pobieranie mediów
             */
            if ($access_token) {
                if ($getIdUser) {
                    $urlMedia = "https://graph.instagram.com/".$getIdUser."?fields=media_count,media&access_token=".$access_token;
                    $getMedia = (new Mjinstagramfeed());
                    $responseMedia = $getMedia->make_curl($urlMedia, "GET", null);
                    $MediaResponse = json_decode($responseMedia, true);
        //            echo "ACCESS ".$access_token;
        //            print_r(json_decode($responseMedia, true));
        //            echo "U:".$getIdUser;
        //            exit();
                    foreach ($MediaResponse['media']["data"] as $media) {
                       $obrazki = $media;
                       $next_url = $MediaResponse['media']['paging']['next'];

                       foreach ($obrazki as $obrazek) {
                           $this->status = '1';
                            $this->processAddImages("https://graph.instagram.com/".$obrazek."?fields=id,media_type,caption,permalink,media_url,username,timestamp&access_token=".$access_token);
                       }
                    }
                }
            }
            Context::getContext()->smarty->assign('status', $this->status);
    }
    
    /**
     * Proces dodawania zdjęć do bazy i na serwer
     * @param type $url
     */
    private function processAddImages($url)
    {
        $urlPhoto = $url;//"https://graph.instagram.com/".$obrazek."?fields=id,media_type,caption,permalink,media_url,username,timestamp&access_token=".access_token;
        $getPhoto = (new Mjinstagramfeed());
        $responsePhoto = $getPhoto->make_curl($urlPhoto, "GET", null);
        $obraz = json_decode($responsePhoto, true);
//        print_r($obraz);
//        exit();
        /**
         * Jeżeli id postu nie istnieje w bazie
         */
        if ((new Mjinstagramfeed())->checkIfExist($obraz['id']) == 0) {
        /**
         * Zapisuj grafikę na serwerze i dodawaj do bazy
         */
        @copy($obraz["media_url"], dirname(__FILE__).'/../../img/'.$obraz["id"].'.jpg');
        $addImage = (new Mjinstagramfeed());
            $params = array(
                "post_id" => $obraz["id"],
                "post_link" => $obraz["permalink"],
                "title" => $obraz["caption"],
                "date_add" => date('Y-m-d H:i:s', strtotime($obraz['timestamp']))
            );

        $addImage->addInstagramFeed($params);
        }
    }
    /**
     * Pobranie tokenu ważnego przez 60 dni
     * @param type $client_secret
     * @param type $access_token
     */
    private function getLongToken($client_secret, $access_token)
    {
        $url = "https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=".$client_secret."&access_token=".$access_token;
        $getLongToken = new Mjinstagramfeed();
        Context::getContext()->cookie->authorization = 0;
        $responseLongToken = $getLongToken->make_curl($url, "GET", null);
        $resultLongToken = (json_decode($responseLongToken, 1));
        Configuration::updateValue($this->prefix.'access_token', $resultLongToken['access_token']);
        Configuration::updateValue($this->prefix.'expires_in', $resultLongToken['expires_in']);
        $dateExpires = date("Y-m-d", strtotime(date('Y-m-d').'+ '.$resultLongToken['expires_in'].' days'));
        Configuration::updateValue($this->prefix.'expires_date', $dateExpires);
    }
}
