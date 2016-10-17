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
    protected $extractor;

    public function __construct() {
        $this->utils = App::make('Editora');
        $this->extractor = App::make('Extractor');
    }

    public function __invoke(Request $request) {
        /**
         *
         **/
        $language = $request->route('language');
        $niceUrl  = $request->route('nice_url');
        $req_info = $request->input('req_info');

        $preview = $this->editMode($req_info);

        /**
         *
         **/
        $this->setLanguageFromSession();
        if($language) $this->setLanguageToSession($language);

        /**
         *
         **/
        $urlData = $this->utils->get_url_data($language, $niceUrl);

        /**
         *
         **/
        if($urlData['type'] === "Error") abort(404);
        if($urlData['type'] === "ChangeLanguage") return redirect('/');

        /**
         *
         **/
        $className = 'App\\Http\\Controllers\\Editora\\'.$urlData['class_tag'];
        $class = new $className;

        $class->urlData = $urlData;
        $class->language = $this->getCurrentLanguage();
        $class->inst_id = (array_key_exists('id', $urlData)) ? $urlData['id'] : 1;
        $class->preview = $preview;

        return $class->render();
    }

    /**
     *
     **/
    protected function extract($query = null, $params = null, $object, $ferret) {
        return $this->extractor->extract($query, $params, $object, $ferret);
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
    private function setLanguageFromSession() {
        $lang = (Session::get('locale') !== null) ? Session::get('locale') : env('APP_LANG');
        $_SESSION['u_lang'] = Session::get('locale');
        App::setLocale($lang);
    }

    /**
     *
     **/
    private function getCurrentLanguage() {
        return App::getLocale();
    }

    /**
     *
     **/
    private function setLanguageToSession($language) {
        Session::put('locale', $language);
        $this->setLanguageFromSession();
    }
}
