<?php
namespace App\Traits;

trait HasMeta
{
    /**
     * Retrieve a meta value from the model's meta attribute.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getMeta($key = null, $default = null): mixed
    {
        return data_get($this->meta ?? [], $key, $default);
    }
}
