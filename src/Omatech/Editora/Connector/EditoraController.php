<?php
namespace Omatech\Editora\Connector;

use App;
Use Session;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Omatech\Editora\Utils\Editora as Utils;
use Omatech\Editora\Extractor\Editora as Extractor;

session_start(); //Editora

class EditoraController extends Controller
{
    protected $utils;

    public function __construct() {
        $this->utils = App::make('Editora');
    }

    public function __invoke(Request $request) {

        /**
         *
         **/
        $language = $request->route('language');
        $nice_url  = $request->route('nice_url');
        $req_info = $request->input('req_info');

        $preview = $this->editMode($req_info);

        /**
         *
         **/
        $currentLang = $this->getBrowserLanguage();
        $currentLang = $this->getLanguageFromSession($currentLang);
        $currentLang = (isset($language)) ? $language : $currentLang;
        $currentLang = ($currentLang != '') ? $currentLang : env('APP_LANG');

        session(['locale' => $currentLang]);
        $_SESSION['u_lang'] = $currentLang;
        App::setLocale(session('locale'));

        /**
         *
         **/
        if(!$nice_url) {
            if(env('APP_NICEURL') === true) {
                $nice = $this->utils->get_nice_from_id(1, $currentLang);
                return redirect('/'.$currentLang.'/'.$nice);
            } else if(!$language && env('APP_NICEURL') === false) {
                return redirect('/'.$currentLang);
            }
        }

        /**
         *
         **/
        $urlData = $this->utils->get_url_data($currentLang, $nice_url);

        /**
         *
         **/
        if($urlData['type'] === "Error") abort(404);

        /**
         *
         **/
        $className = 'App\\Http\\Controllers\\Editora\\'.$urlData['class_tag'];
        $class = new $className;

        $class->inst_id = (array_key_exists('id', $urlData)) ? $urlData['id'] : 1;
        $class->preview = $preview;

        return $class->render();
    }

    /**
     *
     **/
    private function editMode($req_info) {
        $preview = false;

        if($req_info !== null && $req_info == 1) {
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') {
                $preview = true;
            } else {
                die('Not connected to CMS!');
            }
        }

        return $preview;
    }

    /**
     *
     **/
    private function getBrowserLanguage() {
        $http_accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $deflang = "";

        if(isset($http_accept) && strlen($http_accept) > 1)  {
            # Split possible languages into array
            $x = explode(',', $http_accept);
            foreach ($x as $val) {
                #check for q-value and create associative array. No q-value means 1 by rule
                if(preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i", $val, $matches))
                    $lang[$matches[1]] = (float)$matches[2];
                else
                    $lang[$val] = 1.0;
            }

            #return default language (highest q-value)
            $qval = 0.0;
            foreach ($lang as $key => $value) {
                if ($value > $qval) {
                    $qval = (float)$value;
                    $deflang = $key;
                }
            }
            $deflang = explode('-', $deflang);
            if(is_array($deflang)) $deflang = $deflang[0];
        }
        return strtolower($deflang);
    }

    /**
     *
     **/
    private function getLanguageFromSession($currentLang) {
        $language = (session('locale') !== null) ? session('locale') : $currentLang;
        return strtolower($language);
    }
}
