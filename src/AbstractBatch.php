<?php
namespace MatthiasWeb\WpdbBatch;

/**
 * Abstract implementation for batch processes (UPDATE, INSERT).
 */
abstract class AbstractBatch {
    
    /**
     * The database table name
     */
    protected $table_name;
    
    /**
     * Format values as key value map (column name => %d/%s)
     */
    protected $format_values;
    
    /**
     * Values (can be different for INSERT / UPDATE)
     */
    protected $values = array();
    
    /**
     * Get the SQL queries.
     * 
     * @returns string
     */
    abstract public function sql($chunkSize = 0, $chunkValues = null);
    
    /**
     * Execute the SQL query.
     * 
     * @returns array
     */
    abstract public function execute($chunkSize = 0);
    
    /**
     * Get the SQL queries as array.
     * 
     * @returns array<string>
     */
    public function sqlArray($chunkSize = 0) {
        $sqls = $this->sql($chunkSize);
        if (!is_array($sqls)) {
            $sqls = array($sqls);
        }
        return $sqls;
    }
    
    /**
     * Escape a column value.
     * 
     * @returns string
     */
    protected function escape($column, $value) {
        global $wpdb;
        return $wpdb->prepare(isset($this->format_values[$column]) ? $this->format_values[$column] : '%s', $value);
    }
    
    /**
     * Escape a column name.
     * 
     * @returns string
     */
    protected function columnEscape($inp) {
        if (is_array($inp))
            return array_map(__METHOD__, $inp);
            
        if (!empty($inp) && is_string($inp)) {
            return str_replace(
                ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
                ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
                $inp);
        }
        return $inp;
    }
    
}
