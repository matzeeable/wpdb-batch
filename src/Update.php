<?php

namespace MatthiasWeb\WpdbBatch;

/**
 * Batch update implementation.
 */
class Update extends AbstractBatch {
    
    /**
     * The index name for the uniqueness of the row
     */
    private $index;
    
    public function __construct($table_name, $index, $format_values = null) {
        $this->table_name = $table_name;
        $this->index = $index;
        $this->format_values = $format_values;
        
        if (!isset($index) AND empty($index)) {
            throw new \Exception('Index may not be empty');
        }
    }
    
    /**
     * Add new update row
     */
    public function add($indexValue, $updates) {
        $updates[$this->index] = $indexValue;
        $this->values[] = $updates;
        return $this;
    }
    
    public function sql($chunkSize = 0, $chunkValues = null) {
        $values = $chunkValues === null ? $this->values : $chunkValues;
        if ($chunkSize > 0) {
            $chunks = array_chunk($this->values, $chunkSize);
            $queries = array();
            foreach ($chunks as $part) {
                $queries[] = $this->sql(0, $part);
            }
            
            return $queries;
        }else{
            $final = array();
            $ids = array();
            $table_name = $this->table_name;
            $index = $this->index;
            
            if(!count($values))
                return false;
            
            // Create WHEN statement
            foreach ($values as $key => $val) {
                $valIndex = $this->escape($index, $val[$index]);
                $ids[] = $valIndex;
                
                foreach (array_keys($val) as $field) {
                    if ($field !== $index) {
                        if (is_null($val[$field])) {
                            $value = 'NULL';
                        }else{
                            $value = $this->escape($field, $val[$field]);
                        }
                        $final[$field][] = 'WHEN `'. $this->columnEscape($index) .'` = ' . $valIndex . ' THEN ' . $value . ' ';
                    }
                }
            }
            
            // Create CASE statement with WHEN's
            $cases = '';
            foreach ($final as $k => $v) {
                $column = $this->columnEscape($k);
                $cases .=  '`'. $column.'` = (CASE '. implode(" ", $v) . ' ELSE `'.$column.'` END), ';
            }
            
            // Create UPDATE statement
            $query = "UPDATE `$table_name` SET " . substr($cases, 0, -2) . " WHERE `" . $this->columnEscape($index) . "` IN (" . implode(', ', $ids) . ");";
            return $query;
        }
    }
    
    public function execute($chunkSize = 0) {
        global $wpdb;
        
        $sqls = $this->sqlArray($chunkSize);
        $result = array(
            'failure' => array(),
            'updated' => 0
        );
        
        foreach ($sqls as $sql) {
            $update = $wpdb->query($sql);
            if ($update === false) {
                $result['failure'][] = $sql;
            }else{
                $result['updated'] += $update;
            }
        }
        
        $result['success'] = count($result['failure']) === 0;
        return $result;
    }
}