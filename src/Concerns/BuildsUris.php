<?php

namespace Dash\Concerns;

trait BuildsUris
{
    /**
     * Build the URI for an index request.
     *
     * @param string $resourceType
     * @param array $filters
     * @param array $includes
     * @param string|null $sort
     * @return string
     */
    public static function buildIndexRequestUri($resourceType, $filters = [], $includes = [], $sort = null)
    {
        return $resourceType . static::buildQueryParameters($filters, $includes, $sort);
    }

    /**
     * Build the URI for a resource request.
     *
     * @param string $resourceType
     * @param string $id
     * @param array $filters
     * @param array $includes
     * @param string|null $sort
     * @return string
     */
    public static function buildResourceRequestUri($resourceType, $id, $filters = [], $includes = [], $sort = null)
    {
        return $resourceType . '/' . $id . static::buildQueryParameters($filters, $includes, $sort);
    }

    /**
     * Build the URI for a related resource request.
     *
     * @param string $resourceType
     * @param string $id
     * @param string $relationName
     * @param array $filters
     * @param array $includes
     * @param string|null $sort
     * @return string
     */
    public static function buildRelatedResourceRequestUri($resourceType, $id, $relationName, $filters = [], $includes = [], $sort = null)
    {
        return $resourceType . '/' . $id . '/' . $relationName . static::buildQueryParameters($filters, $includes, $sort);
    }

    /**
     * Build the URI for a relationship request.
     *
     * @param string $resourceType
     * @param string $id
     * @param string $relationName
     * @param array $filters
     * @param array $includes
     * @param string|null $sort
     * @return string
     */
    public static function buildRelationshipRequestUri($resourceType, $id, $relationName, $filters = [], $includes = [], $sort = null)
    {
        return $resourceType . '/' . $id . '/relationships/' . $relationName . static::buildQueryParameters($filters, $includes, $sort);
    }

    /**
     * Build the query parameters for a request.
     *
     * @param array $filters
     * @param array $includes
     * @param null $sort
     * @return string
     */
    protected static function buildQueryParameters($filters = [], $includes = [], $sort = null)
    {
        $query = '';

        if (!empty($filters)) {
            $filtersStr = '';

            foreach ($filters as $key => $value) {
                $filtersStr = static::addParameterSeparator($filtersStr) . "filter[{$key}]={$value}";
            }

            $query = "?{$filtersStr}";
        }

        if (!empty($includes)) {
            $includeStr = '';

            foreach ($includes as $include) {
                $includeStr = static::addParameterSeparator($includeStr, 'include=', ',') . $include;
            }

            $query = static::addParameterSeparator($query, '?') . $includeStr;
        }

        if (isset($sort)) {
            $query = static::addParameterSeparator($query, '?') . $sort;
        }

        return $query;
    }

    /**
     * @param string $str
     * @param string $empty
     * @param string $else
     * @return string
     */
    protected static function addParameterSeparator($str, $empty = '', $else = '&')
    {
        if ($str === '') {
            $str .= $empty;
        } else {
            $str .= $else;
        }

        return $str;
    }
}