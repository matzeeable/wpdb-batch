# wpdb-batch
WordPress `$wpdb` batch `UPDATE` (for `INSERT` PR welcome). Never run multiple SQL queries - run one batch SQL query and increase your plugin performance.

## Installation

```
composer require MatthiasWeb\wpdb-batch:dev-master
```

## Batch `UPDATE`

Construct new instance with the following arguments:

Argument|Type|Description
-|-|-
`$table_name`|`string`|The table name
`$index`|`string`|The index column name which should be `CASE`'d
`[$format_values]`|`array<string,string>`|An optional array with column name as key and format value. If none is given all format values are strings

```php
global $wpdb;
$wpbu = new MatthiasWeb\WpdbBatch\Update($wpdb->terms, 'term_id', array(
    'term_id' => '%d',
    'term_order' => '%d'
));
```

### `add($indexValue, $updates)`

Add a new update row. Returns: `this` instance for method chaining.

Argument|Type|Description
-|-|-
`$indexValue`|`mixed`|The index value for the `$index` column
`$updates`|`array<string,mixed>`|An array with column name as key and new value

```php
$wpbu->add(1, array(
    'term_order' => 10
));

$wpbu->add(2, array(
    'term_order' => 11,
    'another_col' => 'test'
));
```

### `sql($chunkSize = 0)`

Get the SQL query as string. Returns: `array<string>` if `$chunkSize` > 0, otherwise `string`.

Argument|Type|Description
-|-|-
`[$chunkSize=0]`|`int`|The chunk size

Example result:

```sql
UPDATE `wp_terms` 
SET    `term_order` = ( CASE 
                          WHEN `term_id` = 1 THEN 10 
                          WHEN `term_id` = 2 THEN 11 
                          ELSE `term_order` 
                        end ), 
       `another_col` = ( CASE 
                           WHEN `term_id` = 2 THEN 'test' 
                           ELSE `another_col` 
                         end ) 
WHERE  `term_id` IN ( 1, 2 ); 
```

### `sqlArray($chunkSize = 0)`

Get the SQL queries as array. Returns: `array<string>`.

Argument|Type|Description
-|-|-
`[$chunkSize=0]`|`int`|The chunk size

### `execute($chunkSize = 0)`

Execute the batch update. Result: `array` with `failures` (`array` of SQL queries which failed), `updated` (`int`) and `success` (`boolean`).

## Batch `INSERT`

The `INSERT` is not yet implemented because I didn't need this yet. Pull requests welcome.

## License

This repository is licensed under MIT license. The mechanism itself is inspired by [mavinoo/laravelBatch](https://github.com/mavinoo/laravelBatch).
