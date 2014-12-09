<?php namespace Lukzgois\Repository;

/**
 * Class AbstractEloquentRepository
 * @package Integrador\Repository\Eloquent
 */
abstract class AbstractEloquentRepository
{
    abstract public function getModel();

    /**
     * Creates a new resource in the database
     *
     * @param array $input
     * @return mixed
     */
    public function store(Array $input)
    {
        return $this->getModel()->create($input);
    }

    /**
     * Updates an existing resource finding it by pk
     *
     * @param int|array $condition
     * @param array $input
     * @return mixed
     */
    public function update($condition, Array $input)
    {
        $resource = $this->find($condition);
        $resource->update($input);
        return $resource;
    }

    public function updateAll($condition, Array $input)
    {
        $resources = $this->findAll($condition);
        foreach($resources as $resource)
            $resource->update($input);
    }


    public function deleteAll($condition)
    {
        $resources = $this->findAll($condition);
        foreach($resources->all() as $resource)
            $resource->delete();
    }

    /**
     * It deletes a resource by an condition
     *
     * @param int|array $condition
     * @return boolean if the resource was successful deleted
     */
    public function delete($condition)
    {
        $resource = $this->find($condition);
        return $resource->delete();
    }


    /**
     * Find all the resources by te condition specified
     *
     * @param array $fields
     * @return mixed
     */
    public function findAll($fields = [])
    {
        $model = $this->getModel();
        foreach ( $fields as $field => $value) {
            $model = $this->createCondition($model, $field, $value);
        }
        return $model->get();
    }

    /**
     * Find one resource by te condition specified
     *
     * @param array $condition
     * @return mixed
     */
    public function find($condition)
    {
        $model = $this->getModel();

        if ( is_int($condition))
            return $this->getModel()->findOrFail($condition);

        foreach ( $condition as $field => $value) {
            $model = $this->createCondition($model, $field, $value);
        }

        return $model->firstOrFail();
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if ( strpos($method,'findAllBy') === 0) {
            $field = $this->sanitizeField('findAllBy', $method);
            return $this->findAll([$field => $args[0]]);
        }

        if ( strpos($method,'findBy') === 0) {
            $field = $this->sanitizeField('findBy', $method);
            return $this->find([$field => $args[0]]);
        }

    }

    /**
     * Isolate the field from a string and return it on camel_case
     *
     * @param $exclude
     * @param $string
     * @return string
     */
    private function sanitizeField($exclude, $string)
    {
        $rawField = str_replace($exclude, '', $string);
        return $this->toSnakeCase($rawField);
    }

    /**
     * Converts a string to camel_case
     *
     * @param $string
     * @return string
     */
    private function toSnakeCase($string)
    {
        $string = preg_replace('/(?<=\\w)(?=[A-Z])/',"_$1", $string);
        return strtolower($string);
    }

    /**
     * Creates a new condition
     *
     * @param $model
     * @param $field
     * @param $value
     * @return mixed
     */
    private function createCondition($model, $field, $value)
    {
        if( is_int($field))
            return $model->where($value[0], $value[1], $value[2]);

        if( is_array($value))
            return $model->whereIn($field, $value);

        return $model->where($field, '=', $value);
    }
} 