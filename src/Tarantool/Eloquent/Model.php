<?php

namespace Chocofamily\Tarantool\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    /**
     * @var string
     */
    protected $connection = 'tarantool';

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && strtolower($value) === 'null') {
            return null;
        }

        return $value;
    }
}
