<?php
namespace Omatech\Editora\Connector;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

session_start(); //Editora Admin Session

class EditoraController extends Controller
{
    protected $utils;
    protected $class;

    public function __construct(Request $request) 
    {
        $this->utils = App::make('Utils');

        if(!empty(config('editora.middlewares')) && count(config('editora.middlewares'))) {
            $this->middleware(config('editora.middlewares'));
        }

        /**
         *
         **/
        $language = $request->route('language');
        $nice_url = $request->route('nice_url');
        $req_info = $request->input('req_info');

        $preview = $this->editMode($req_info);
        $request->request->add(['preview' => $preview]);

        /**
         *
         **/
        $currentLang = null;
        if(config('editora.ignoreBrowserLanguage') !== true) {
            $currentLang = $this->getBrowserLanguage();
        }
        $currentLang = $this->getLanguageFromSession($currentLang);
        $currentLang = (isset($language) && in_array($language, config('editora.availableLanguages'))) ? $language : $currentLang;
        $currentLang = ($currentLang != '') ? $currentLang : config('editora.defaultLanguage');
        $currentLang = (config('editora.forcedLanguage') !== '') ? config('editora.forcedLanguage') : $currentLang;

        session(['locale' => $currentLang]);
        $_SESSION['u_lang'] = $currentLang;
        App::setLocale(session('locale'));

        /**
         *
         **/
        if(!$nice_url) {
            if (!empty(config('editora.ignoreUrlLanguage')) && config('editora.ignoreUrlLanguage') === true && !in_array($language, config('editora.availableLanguages')) && $language!=null ) {
                $nice_url = $language;
                $language = config('editora.defaultLanguage');
            } else {
                if (config('editora.homeNiceUrl') === true) {
                    $nice = $this->utils->get_nice_from_id(1, $currentLang);
                    Redirect::to('/' . $currentLang . '/' . $nice)->send();
                } else if (!$language && config('editora.homeNiceUrl') === false) {
                    Redirect::to('/' . $currentLang . '/')->send();
                }
            }
        }

        /**
         *
         **/
        $urlData = $this->utils->get_url_data($currentLang, $nice_url);

        /**
         *
         **/
        if($urlData['type'] === "Error") $urlData['class_tag'] = "Error_404";
        if(!in_array($language, config('editora.availableLanguages'))) $urlData['class_tag'] = "Error_404";
        /**
         *
         **/
        $classTag = str_replace('_', '', ucwords($urlData['class_tag'], '_'));

        /**
         *
         **/
        $className = 'App\\Http\\Controllers\\Editora\\'.$classTag;
        $class = new $className;

        $class->inst_id = (array_key_exists('id', $urlData)) ? $urlData['id'] : 1;
        $class->preview = $preview;
        $class->utils   = $this->utils;

        $class->viewData['metaLanguages']   = $this->otherLanguagesMeta($class->inst_id, $currentLang, $nice_url);
        $class->viewData['currentLanguage'] = $currentLang;

        if (method_exists($class, 'middlewares')) {
            $this->middleware($class->middlewares());
        }

        $this->class = $class;
    }

    public function init(Request $request) 
    {
        return $this->class->render($request);
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
    private function getBrowserLanguage()
    {
        $http_accept = (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
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

        $lang = (in_array( strtolower($deflang), config('editora.availableLanguages') )) ? strtolower($deflang) : null;

        return $lang;
    }

    /**
     *
     **/
    private function getLanguageFromSession($currentLang)
    {
        $language = (session('locale') !== null) ? session('locale') : $currentLang;
        return strtolower($language);
    }

    /**
     *
     **/
    private function otherLanguagesMeta($inst_id, $currentLang, $nice_url) {
        $metaLanguages = [];
        $languages = $this->utils->other_languages_url($inst_id, $currentLang);

        if($languages !== null && $languages !== "") {
            foreach($languages as $language) {
                if($nice_url !== null)
                    $url = url()->to('/'.$language['language'].'/'.$language['niceurl']);
                else
                    $url = url()->to('/'.$language['language'].'/');

                $metaLanguages[$language['language']]['hreflang'] = $language['language'];
                $metaLanguages[$language['language']]['href'] = $url;
            }
        }

        return $metaLanguages;
    }
}
