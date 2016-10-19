<?php

namespace Omatech\Editora\Connector;

use App;
use Omatech\Editora\Extractor\Editora as Extractor;

class EditoraModel {
    public static function extract($query, $params, $object, $ferret) {
        $extractor = App::make('Extractor');
        $result = $extractor->extract($query, $params, $object, $ferret);

        if($params['debug'] === true) dd($result, $extractor->debug_messages);

        return $result;
    }
}
