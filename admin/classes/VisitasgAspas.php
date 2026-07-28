<?php

/**
 * Compatibilidad ASPAS: opera sobre tbl_gestora con tipo_actividad = aspas.
 * Tras migrate_gestora_tipo_actividad.php la fuente de verdad es tbl_gestora.
 */
require_once __DIR__ . '/Visitasg.php';

class VisitasgAspas
{
    public function __construct() {}

    public static function getAll($rqst)
    {
        if (!is_array($rqst)) {
            $rqst = [];
        }
        $rqst['tipo_actividad'] = Visitasg::TIPO_ASPAS;
        return Visitasg::getAll($rqst);
    }

    public static function save($rqst)
    {
        if (!is_array($rqst)) {
            $rqst = [];
        }
        $rqst['tipo_actividad'] = Visitasg::TIPO_ASPAS;
        return Visitasg::save($rqst);
    }
}
