<?php

/**
 * Description of ApiDoctrine
 *
 * @author doggetto
 */
class ApiDoctrine
{
    public static function createObject($model_name, $data)
    {
        $object = new $model_name;
        if (!($object instanceof sfDoctrineRecord))
            throw new sfException('Trying to hydrate a non-sfDoctrineRecord object!');
        $object->importFrom('array', $data);
        return $object;
    }
    
    
}