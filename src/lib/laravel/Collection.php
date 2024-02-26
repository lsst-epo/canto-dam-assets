<?php

namespace lsst\cantodamassets\lib\laravel;

use Craft;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection as LaravelCollection;

class Collection extends LaravelCollection
{

    /**
     * Fuzzy search across multiple keys 
     * 
     * @param $keys
     * @param $value
     * @return Collection
     */
    public function whereContainsIn($keys, $value): Collection
    {
        $keys = $this->getArrayableItems($keys);
        $value2 = preg_split('/\s+/', $value);
        $values = array_change_key_case($value2, CASE_LOWER);
        $matched_records = [];

        foreach($keys as $key) {
            $recs = $this->filter(function ($item) use ($key, $values) {
                $data = data_get($item, $key);
                if (is_array($data)) {
                    $data = implode(', ', $data);
                }

                $found = false;
                foreach($values as $value) {
                    if (str_contains(strtolower($data), strtolower($value))) {
                        $found = true;
                        break;
                    }
                }

                return $found;
            });

            if($recs->count() > 0) {
                if($matched_records == []) {
                    $count = $recs->count();
                    $type_rec = gettype($recs);
                    $matched_records = $recs;
                } else {
                    $count = $recs->count();
                    $matched_records->merge($recs);
                    $count2 = $matched_records->count();
                }
            }

        }

        if($matched_records == []) {
            // To-do: Come up with a more elegant solution, rather than calling only()
            return $this->only([""]);
        } else {
            return $matched_records->unique();
        }

    }

    /**
     * Filter items by the given key value pair.
     *
     * @param string $key
     * @param Arrayable|iterable $values
     * @param bool $strict
     * @return static
     */
    public function whereIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);

        return $this->filter(function($item) use ($key, $values, $strict) {
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
     * @param Arrayable|iterable $values
     * @param bool $strict
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);

        return $this->reject(function($item) use ($key, $values, $strict) {
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
     * @return Closure
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

        return function($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);

            $strings = array_filter([$retrieved, $value], function($value) {
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
