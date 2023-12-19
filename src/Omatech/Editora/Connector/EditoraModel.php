<?php

namespace Omatech\Editora\Connector;

use App;
use Omatech\Editora\Extractor\Editora as Extractor;
use Omatech\Editora\Extractor\GraphQLPreprocessor;

class EditoraModel
{
    public static $debugMessages = "";

    public static function extract($query, $params, $object, $ferret) {
        $extractor = App::make('Extractor');
        $result = $extractor->extract($query, $params, $object, $ferret);

        if($params['debug'] === true) self::$debugMessages = $extractor->debug_messages;

        return $result;
    }

    public static function magic($query, $params) {
        $params['lang'] = App::getLocale();
        if (!isset($params['metadata'])){
            $params['metadata'] = true;
        }
        if (isset($params['id'])){
            $params['id'] = self::real_escape_string($params['id']);
        }
        $query = GraphQLPreprocessor::generate($query, config('editora.extractNullValues', false));
        return self::extract($query, $params, 'array', true);
    }

    private static function real_escape_string($input) {
        if(is_array($input))
            return array_map(__METHOD__, $input);
        if(!empty($input) && is_string($input)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $input);
        }
        return $input;
    }
}
