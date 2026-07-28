SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  ORDER BY visitas.date desc;




SELECT

'SANTA-BARBARA'

SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'SANTA-BARBARA' ORDER BY visitas.date desc;


UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='García Rovira'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'SANTA-BARBARA' ORDER BY visitas.date desc;



'SAN-BENITO'
SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'SAN-BENITO' ORDER BY visitas.date desc;


UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Vélez'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'SAN-BENITO' ORDER BY visitas.date desc;



'VELEZ'
SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'VELEZ' ORDER BY visitas.date desc;

UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Vélez'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'VELEZ' ORDER BY visitas.date desc;


'EL-PENON' 

SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'EL-PENON' ORDER BY visitas.date desc;

UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Vélez'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'EL-PENON' ORDER BY visitas.date desc;



'CERRITO'
SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'CERRITO' ORDER BY visitas.date desc;

UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='García Rovira'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'CERRITO'  ORDER BY visitas.date desc;


'CONCEPCION'
SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'CONCEPCION' ORDER BY visitas.date desc;



UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='García Rovira'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'CONCEPCION'  ORDER BY visitas.date desc;



'GUEPSA'
SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'GUEPSA' ORDER BY visitas.date desc;


UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Vélez'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'GUEPSA'  ORDER BY visitas.date desc;

'BOLIVAR'
SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'BOLIVAR' ORDER BY visitas.date desc;


UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Vélez'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'BOLIVAR'  ORDER BY visitas.date desc;


'SABANA-DE-TORRES'
SELECT visitas.id, visitas.DATE, ciudad.municipio, visitas.compromisos, visitas.provincia, visitas.respuesta, secretaria.secretaria, visitas.img, visitas.id, visitas.estado, visitas.consecuencia, visitas.compromisopac, visitas.componente, visitas.tipo_ejecucion FROM u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'SABANA-DE-TORRES'ORDER BY visitas.date desc;


UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Yariguíes'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'SABANA-DE-TORRES' ORDER BY visitas.date desc;

'ENCINO'
UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Guanentá'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'ENCINO' ORDER BY visitas.date desc;

'SAN-JOAQUIN'
UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Guanentá'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'SAN-JOAQUIN' ORDER BY visitas.date desc;


'ENCISO'
UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='García Rovira'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'ENCISO' ORDER BY visitas.date desc;

'SAN-MIGUEL'
UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='García Rovira'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'SAN-MIGUEL' ORDER BY visitas.date desc;

'CIMITARRA'
UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Vélez'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'CIMITARRA' ORDER BY visitas.date desc;

'EL-CARMEN-DE-CHUCURI'
UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Yariguíes'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'EL-CARMEN-DE-CHUCURI' ORDER BY visitas.date desc;

'HATO'
UPDATE u552917860_spiunifisanta.tbl_visitas AS visitas INNER JOIN u552917860_spiunifisanta.tbl_secretarias AS secretaria ON visitas.tbl_secretarias_id = secretaria.id INNER JOIN u552917860_spiunifisanta.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio 
set provincia ='Comunera'
WHERE visitas.tipo_registro = 'Compromiso' and provincia ='null'  and 
municipio = 'HATO' ORDER BY visitas.date desc;





UPDATE `tbl_visitas` SET `componente` = 'INFRAESTRUCTURA ESCOLAR' WHERE `tbl_visitas`.`id` = 1646; 





UPDATE u552917860_santaNewspi.tbl_visitas AS visitas
INNER JOIN u552917860_santaNewspi.tbl_ciudades AS ciudad ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
SET visitas.provincia = CASE ciudad.municipio
    -- GUANENTÁ
    WHEN 'ARATOCA'               THEN 'Guanentá'
    WHEN 'BARICHARA'             THEN 'Guanentá'
    WHEN 'CABRERA'               THEN 'Guanentá'
    WHEN 'CEPITA'                THEN 'Guanentá'
    WHEN 'CHARALA'               THEN 'Guanentá'
    WHEN 'MOGOTES'               THEN 'Guanentá'
    WHEN 'OCAMONTE'              THEN 'Guanentá'
    WHEN 'ONZAGA'                THEN 'Guanentá'
    WHEN 'PINCHOTE'              THEN 'Guanentá'
    WHEN 'SAN-GIL'               THEN 'Guanentá'
    WHEN 'SAN-JOAQUIN'           THEN 'Guanentá'
    WHEN 'VILLANUEVA'            THEN 'Guanentá'
    -- VELEZ
    WHEN 'BOLIVAR'               THEN 'Velez'
    WHEN 'CHIPATA'               THEN 'Velez'
    WHEN 'CIMITARRA'             THEN 'Velez'
    WHEN 'EL-PENON'              THEN 'Velez'
    WHEN 'GUEPSA'                THEN 'Velez'
    WHEN 'LA-BELLEZA'            THEN 'Velez'
    WHEN 'LA-PAZ'                THEN 'Velez'
    WHEN 'SAN-BENITO'            THEN 'Velez'
    WHEN 'SUCRE'                 THEN 'Velez'
    WHEN 'VELEZ'                 THEN 'Velez'
    -- COMUNERA
    WHEN 'HATO'                  THEN 'Comunera'
    -- GARCÍA ROVIRA
    WHEN 'CARCASI'               THEN 'García Rovira'
    WHEN 'CERRITO'               THEN 'García Rovira'
    WHEN 'CONCEPCION'            THEN 'García Rovira'
    WHEN 'ENCISO'                THEN 'García Rovira'
    WHEN 'MACARAVITA'            THEN 'García Rovira'
    WHEN 'MALAGA'                THEN 'García Rovira'
    WHEN 'SAN-MIGUEL'            THEN 'García Rovira'
    WHEN 'SANTA-BARBARA'         THEN 'García Rovira'
    -- METROPOLITANA
    WHEN 'BUCARAMANGA'           THEN 'Metropolitana'
    -- YARIGUÍES
    WHEN 'BARRANCABERMEJA'       THEN 'Yariguíes'
    WHEN 'BETULIA'               THEN 'Yariguíes'
    WHEN 'EL-CARMEN-DE-CHUCURI'  THEN 'Yariguíes'
    WHEN 'SABANA-DE-TORRES'      THEN 'Yariguíes'
    ELSE visitas.provincia
END
WHERE visitas.tipo_registro = 'Compromiso'
  AND (visitas.provincia IS NULL OR visitas.provincia = '' OR visitas.provincia = 'null');