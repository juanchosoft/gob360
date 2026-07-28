
UPDATE tbl_visitas
SET provincia = CASE 
    WHEN provincia = 'Soto_Norte' THEN 'Soto Norte'
    WHEN provincia = 'Garcia_Rovira' THEN 'García Rovira'
    WHEN provincia = 'Guanenta' THEN 'Guanentá'
    ELSE provincia
END
WHERE provincia IN ('Soto_Norte', 'Garcia_Rovira', 'Guanenta');
