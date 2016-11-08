<?php

namespace Omatech\Editora\Connector;

use App;
use Omatech\Editora\Extractor\Editora as Extractor;
use Omatech\Editora\Extractor\GraphQLPreprocessor;

class EditoraModel {
    public static $debugMessages = "";

    public static function extract($query, $params, $object, $ferret) {
        $extractor = App::make('Extractor');
        $result = $extractor->extract($query, $params, $object, $ferret);

        if($params['debug'] === true) self::$debugMessages = $extractor->debug_messages;

        return $result;
    }

    public static function magic($query, $params) {
        $params['lang'] = App::getLocale();
        $params['metadata'] = true;

        $query = GraphQLPreprocessor::generate($query);
        return self::extract($query, $params, 'array', true);
    }
}
