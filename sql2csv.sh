SELECT 
    c.TABLE_NAME AS 'Tabla',
    t.TABLE_COMMENT AS 'Comentario de Tabla',
    c.COLUMN_NAME AS 'Columna',
    c.COLUMN_TYPE AS 'Tipo',
    c.IS_NULLABLE AS 'Nulo',
    c.COLUMN_KEY AS 'Llave',
    c.COLUMN_DEFAULT AS 'Por defecto',
    c.EXTRA AS 'Extra',
    c.COLUMN_COMMENT AS 'Comentario de Columna'
FROM information_schema.COLUMNS c
JOIN information_schema.TABLES t 
    ON c.TABLE_NAME = t.TABLE_NAME AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
WHERE c.TABLE_SCHEMA = 'santaok'
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION
INTO OUTFILE '/var/lib/mysql-files/data-dictionary.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\n';
