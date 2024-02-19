<?php

namespace lsst\cantodamassets\lib\laravel;

use Craft;
use Illuminate\Support\Collection as LaravelCollection;

class Collection extends LaravelCollection
{


    public function whereContainsIn($keys, $value, $strict = false)
    {
        Craft::info("Got inside of whereContainsIn()!", "lofi!");
        $keys = $this->getArrayableItems($keys);

        return $this->filter(function ($item) use ($keys, $value, $strict) {
            // Handle the case where the data is an array of items
            for ($i = 0; $i < count($keys); $i++) {
                $item = data_get($item, $keys[$i]);

                if (is_array($item)) {
                    $item = implode(', ', $item);
                    Craft::info("Logging: $list", "lofi!");
                } else {
                    Craft::info("Logging: $item", "lofi!");
                }

                if (str_contains($item, $value)) {
                    return true;
                }
            }

            return false;
        });
    }
    /**
     * Filter items by the given key value pair.
     *
     * @param string $key
     * @param \Illuminate\Contracts\Support\Arrayable|iterable $values
     * @param bool $strict
     * @return static
     */
    public function whereIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);

        return $this->filter(function ($item) use ($key, $values, $strict) {
            $item = data_get($item, $key);
            // Handle the case where the data is an array of items
            if (is_array($item)) {
                return count(array_intersect($item, $values)) > 0;
            }
            return in_array($item, $values, $strict);
        });
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param string $key
     * @param \Illuminate\Contracts\Support\Arrayable|iterable $values
     * @param bool $strict
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);

        return $this->reject(function ($item) use ($key, $values, $strict) {
            $item = data_get($item, $key);
            // Handle the case where the data is an array of items
            if (is_array($item)) {
                return count(array_intersect($item, $values)) > 0;
            }
            return in_array($item, $values, $strict);
        });
    }

    /**
     * Get an operator checker callback.
     *
     * @param callable|string $key
     * @param string|null $operator
     * @param mixed $value
     * @return \Closure
     */
    protected function operatorForWhere($key, $operator = null, $value = null)
    {
        if ($this->useAsCallable($key)) {
            return $key;
        }

        if (func_num_args() === 1) {
            $value = true;

            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;

            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);

            $strings = array_filter([$retrieved, $value], function ($value) {
                return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
            });

            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }

            // Handle the case where the data is an array of items
            if (is_array($retrieved)) {
                switch ($operator) {
                    default:
                    case '=':
                    case '==':
                        return in_array($value, $retrieved, false);
                    case '!=':
                    case '<>':
                        return !in_array($value, $retrieved, false);
                    case '===':
                        return in_array($value, $retrieved, true);
                    case '!==':
                        return !in_array($value, $retrieved, true);
                }
            }

            switch ($operator) {
                default:
                case '=':
                case '==':
                    return $retrieved == $value;
                case '!=':
                case '<>':
                    return $retrieved != $value;
                case '<':
                    return $retrieved < $value;
                case '>':
                    return $retrieved > $value;
                case '<=':
                    return $retrieved <= $value;
                case '>=':
                    return $retrieved >= $value;
                case '===':
                    return $retrieved === $value;
                case '!==':
                    return $retrieved !== $value;
                case '<=>':
                    return $retrieved <=> $value;
            }
        };
    }
}
