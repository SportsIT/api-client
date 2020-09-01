<?php

namespace Dash;

class PageObject
{
    protected $pageNumber;

    protected $pageSize;

    /**
     * PageObject constructor.
     * @param int|null $pageNumber
     * @param int|null $pageSize
     */
    public function __construct($pageNumber, $pageSize)
    {
        $this->pageNumber = $pageNumber;
        $this->pageSize = $pageSize;
    }

    /**
     * @return int|null
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * @return int|null
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param int|null $pageNumber
     */
    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }

    /**
     * @param int|null $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }
}