<?php

namespace MongovcTests\Model\Object;

/**
 * Class EnhancebleObject
 * @package MongovcTests\Model\Object
 */
class EnhancebleObject extends SerializableObject
{
    /**
     * @param array $data
     */
    public function enhance(array $data)
    {
        foreach ($data as $key => $val) {
            if (in_array($key, array('a', 'b'))) {
                $this->{$key} = $val;
            }
        }
    }
}