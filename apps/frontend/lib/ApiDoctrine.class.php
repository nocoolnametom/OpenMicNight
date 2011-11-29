<?php

/**
 * Description of ApiDoctrine
 *
 * @author doggetto
 */
class ApiDoctrine
{

    /**
     *
     * @param string $model_name
     * @param array  $data
     * @return sfDoctrineRecord 
     */
    public static function createObject($model_name, $data)
    {
        if (is_null($data) || empty($data))
            return null;
        $object = new $model_name;
        if (!($object instanceof sfDoctrineRecord))
            throw new sfException('Trying to hydrate a non-sfDoctrineRecord object!');
        $object->fromArray($data);
        if (array_key_exists('id', $data))
            $object->setIncremented($data['id']);
        return $object;
    }

    /**
     *
     * @param string $model_name
     * @param array  $data   
     * @return Doctrine_Collection 
     */
    public static function createCollection($model_name, $data)
    {

        $collection = new Doctrine_Collection($model_name . 'Table');
        if (!($collection instanceof Doctrine_Collection))
            throw new sfException('Trying to hydrate a non-Doctrine_collection object!');
        $collection->setData($data);
        return $collection;
    }

    /**
     *
     * @param string $model_name
     * @param array  $data   
     * @return Doctrine_Collection 
     */
    public static function createObjectArray($model_name, $data)
    {
        $collection = array();
        foreach ($data as $entry) {
            $collection[] = self::createObject($model_name, $entry);
        }
        return $collection;
    }

}