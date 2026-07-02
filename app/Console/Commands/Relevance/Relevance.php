<?php

namespace App\Console\Commands\Relevance;

abstract class Relevance
{
    /**
     * 
     */
    abstract public function getData($from);
    
    /**
     * 
     */
    abstract public function getTable();
    
    /**
     * 
     */
    public function getItemId()
    {
        return "item_id";
    }
    
    /**
     * 
     */
    public function getTableId()
    {
        return "id";
    }
    
    /**
     * 
     */
    public function getField()
    {
        return "relevance";
    }
    
    /**
     * 
     */
    abstract public function getName();
}