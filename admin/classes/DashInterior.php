<?php
// admin/classes/DashInterior.php
// Backend Dashboard Interior (Boletín + Factores)
// - Usa DbConection NO estática
// - Tablas fully-qualified con getTable() => DB.tabla (evita "no seleccionó DB")
// - Soporta:
//   1) Payload comparativo (año_1 vs año_2) para dashboard
//   2) Payload por año (form)
//   3) Guardado de valores + (NUEVO) factor de atención (texto largo)

class DashInterior
{
  /* =========================================================
     Helpers DB
  ========================================================== */
  private static function db(): DbConection {
    return new DbConection();
  }

  private static function pdo(): PDO {
    $db = self::db();
    return $db->openConect();
  }

  private static function t(string $table): string {
    $db = self::db();
    return $db->getTable($table); // dbName.tabla
  }

  private static function s($v): string {
    return trim((string)($v ?? ''));
  }

  /* =========================================================
     Payload comparativo (Dashboard: año1 vs año2)
  ========================================================== */
  public static function getPayload(): array
  {
    $pdo = self::pdo();

    // Meta (años, fecha, fuente, tasa, municipios sin homicidios, nota)
    $metaRow = $pdo->query("
      SELECT anio_1, anio_2, boletin_no, fecha_cierre, fuente, tasa_homicidios, municipios_sin_homicidios, nota_html
      FROM " . self::t('tbl_dash_interior_meta') . "
      WHERE id=1
      LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$metaRow) {
      $metaRow = [
        'anio_1' => 2025,
        'anio_2' => 2026,
        'boletin_no' => null,
        'fecha_cierre' => null,
        'fuente' => '',
        'tasa_homicidios' => '',
        'municipios_sin_homicidios' => 0,
        'nota_html' => ''
      ];
    }

    $anio1 = (int)$metaRow['anio_1'];
    $anio2 = (int)$metaRow['anio_2'];

    // Cards
    $cards = $pdo->query("
      SELECT id, card_key, card_num, titulo, subtitulo
      FROM " . self::t('tbl_dash_boletin') . "
      WHERE activo=1
      ORDER BY card_num ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $datasets = [];

    foreach ($cards as $card) {
      $boletinId = (int)$card['id'];
      $key       = (string)$card['card_key'];

      // Categorías
      $catsStmt = $pdo->prepare("
        SELECT id, nombre
        FROM " . self::t('tbl_dash_boletin_categoria') . "
        WHERE boletin_id=?
        ORDER BY orden ASC, id ASC
      ");
      $catsStmt->execute([$boletinId]);
      $catRows = $catsStmt->fetchAll(PDO::FETCH_ASSOC);

      $catIds   = array_map(fn($c) => (int)$c['id'], $catRows);
      $catNames = array_map(fn($c) => (string)$c['nombre'], $catRows);

      $serie1 = array_fill(0, count($catIds), 0);
      $serie2 = array_fill(0, count($catIds), 0);

      // 🔥 (NUEVO) factor de atención por año (texto largo)
      // Lo tomamos de una tabla dedicada por (boletin_id, anio)
      $factorAtencionA1 = self::getFactorAtencion($boletinId, $anio1);
      $factorAtencionA2 = self::getFactorAtencion($boletinId, $anio2);

      if (!empty($catIds)) {
        $in = implode(',', array_fill(0, count($catIds), '?'));

        // Año 1
        $q1 = $pdo->prepare("
          SELECT categoria_id, valor
          FROM " . self::t('tbl_dash_boletin_valor') . "
          WHERE boletin_id=? AND anio=? AND categoria_id IN ($in)
        ");
        $q1->execute(array_merge([$boletinId, $anio1], $catIds));
        $map1 = [];
        foreach ($q1->fetchAll(PDO::FETCH_ASSOC) as $r) {
          $map1[(int)$r['categoria_id']] = (int)$r['valor'];
        }
        foreach ($catIds as $i => $cid) { $serie1[$i] = $map1[$cid] ?? 0; }

        // Año 2
        $q2 = $pdo->prepare("
          SELECT categoria_id, valor
          FROM " . self::t('tbl_dash_boletin_valor') . "
          WHERE boletin_id=? AND anio=? AND categoria_id IN ($in)
        ");
        $q2->execute(array_merge([$boletinId, $anio2], $catIds));
        $map2 = [];
        foreach ($q2->fetchAll(PDO::FETCH_ASSOC) as $r) {
          $map2[(int)$r['categoria_id']] = (int)$r['valor'];
        }
        foreach ($catIds as $i => $cid) { $serie2[$i] = $map2[$cid] ?? 0; }
      }

      $datasets[$key] = [
        'card' => [
          'card_num'  => (int)$card['card_num'],
          'titulo'    => (string)$card['titulo'],
          'subtitulo' => (string)$card['subtitulo'],
        ],
        'cats' => $catNames,
        'serie_anio_1' => $serie1,
        'serie_anio_2' => $serie2,

        // 🔥 (NUEVO) factor de atención por año
        'factor_atencion_anio_1' => $factorAtencionA1,
        'factor_atencion_anio_2' => $factorAtencionA2,
      ];
    }

    // Factores (gauges) => por defecto año2
    $fStmt = $pdo->prepare("
      SELECT factor_key, anio, orden, valor, titulo_html, texto_html
      FROM " . self::t('tbl_dash_factor') . "
      WHERE anio=?
      ORDER BY orden ASC
    ");
    $fStmt->execute([$anio2]);

    $factors = [];
    foreach ($fStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
      $factors[$f['factor_key']] = [
        'anio'       => (int)$f['anio'],
        'orden'      => (int)$f['orden'],
        'valor'      => (int)$f['valor'],
        'titulo_html'=> (string)$f['titulo_html'],
        'texto_html' => (string)$f['texto_html'],
      ];
    }

    // Sin homicidios: el valor proviene de tbl_dash_interior_meta
    $munSH = (int)($metaRow['municipios_sin_homicidios'] ?? 0);
    if (!isset($factors['sin_homicidios']) || !is_array($factors['sin_homicidios'])) {
      $factors['sin_homicidios'] = [];
    }
    $factors['sin_homicidios']['valor'] = $munSH;

    $result = [
      'ok' => true,
      'meta' => [
        'anio_1'                    => $anio1,
        'anio_2'                    => $anio2,
        'boletin_no'                => isset($metaRow['boletin_no']) && $metaRow['boletin_no'] !== null ? (int)$metaRow['boletin_no'] : null,
        'fecha_cierre'              => $metaRow['fecha_cierre'],
        'fuente'                    => $metaRow['fuente'],
        'tasa_homicidios'           => $metaRow['tasa_homicidios'],
        'municipios_sin_homicidios' => $munSH,
        'nota_html'                 => $metaRow['nota_html'],
      ],
      'datasets' => $datasets,
      'factors'  => $factors,
    ];

    // Recalcular sicariato e intolerancia desde los datasets para que
    // el PDF y el dashboard siempre muestren los mismos valores
    return self::buildFactorsFromDatasets($result);
  }

  /* =========================================================
     Payload de 1 año (FORM)
     - Devuelve cats + serie + factor_atencion (texto largo)
  ========================================================== */
  public static function getPayloadForYear(int $anio): array
  {
    $pdo = self::pdo();

    $cards = $pdo->query("
      SELECT id, card_key, card_num, titulo, subtitulo
      FROM " . self::t('tbl_dash_boletin') . "
      WHERE activo=1
      ORDER BY card_num ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $datasets = [];

    foreach ($cards as $card) {
      $boletinId = (int)$card['id'];
      $key       = (string)$card['card_key'];

      $catsStmt = $pdo->prepare("
        SELECT id, nombre
        FROM " . self::t('tbl_dash_boletin_categoria') . "
        WHERE boletin_id=?
        ORDER BY orden ASC, id ASC
      ");
      $catsStmt->execute([$boletinId]);
      $catRows = $catsStmt->fetchAll(PDO::FETCH_ASSOC);

      $catIds   = array_map(fn($c) => (int)$c['id'], $catRows);
      $catNames = array_map(fn($c) => (string)$c['nombre'], $catRows);

      $serie = array_fill(0, count($catIds), 0);

      if (!empty($catIds)) {
        $in = implode(',', array_fill(0, count($catIds), '?'));

        $q = $pdo->prepare("
          SELECT categoria_id, valor
          FROM " . self::t('tbl_dash_boletin_valor') . "
          WHERE boletin_id=? AND anio=? AND categoria_id IN ($in)
        ");
        $q->execute(array_merge([$boletinId, $anio], $catIds));

        $map = [];
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
          $map[(int)$r['categoria_id']] = (int)$r['valor'];
        }
        foreach ($catIds as $i => $cid) { $serie[$i] = $map[$cid] ?? 0; }
      }

      // 🔥 factor de atención (texto largo) por (boletin_id, anio)
      $factorAtencion = self::getFactorAtencion($boletinId, $anio);

      $datasets[$key] = [
        'card' => [
          'card_num'  => (int)$card['card_num'],
          'titulo'    => (string)$card['titulo'],
          'subtitulo' => (string)$card['subtitulo'],
        ],
        'cats' => $catNames,
        'serie' => $serie,

        // 🔥 (NUEVO)
        'factor_atencion' => $factorAtencion,
      ];
    }

    return [
      'ok' => true,
      'anio' => $anio,
      'datasets' => $datasets
    ];
  }

  /* =========================================================
     Guardar valores (FORM)
     - Guarda números en tbl_dash_boletin_valor
     - Guarda factor_atencion (texto largo) en tbl_dash_boletin_factor_atencion
  ========================================================== */
  public static function saveBoletinValues(
    string $cardKey,
    int $anio,
    array $valuesByCategoryName,
    string $factorAtencion = ''
  ): array
  {
    $pdo = self::pdo();

    // Get boletin
    $stmt = $pdo->prepare("
      SELECT id
      FROM " . self::t('tbl_dash_boletin') . "
      WHERE card_key=?
      LIMIT 1
    ");
    $stmt->execute([$cardKey]);
    $boletinId = (int)($stmt->fetchColumn() ?: 0);
    if ($boletinId <= 0) return ['ok'=>false,'msg'=>'Gráfico no encontrado'];

    // Categorías
    $cats = $pdo->prepare("
      SELECT id, nombre
      FROM " . self::t('tbl_dash_boletin_categoria') . "
      WHERE boletin_id=?
    ");
    $cats->execute([$boletinId]);
    $catRows = $cats->fetchAll(PDO::FETCH_ASSOC);

    $pdo->beginTransaction();

    try {
      // 1) valores numéricos
      foreach ($catRows as $c) {
        $catId = (int)$c['id'];
        $name  = (string)$c['nombre'];
        $val   = (int)($valuesByCategoryName[$name] ?? 0);

        $up = $pdo->prepare("
          INSERT INTO " . self::t('tbl_dash_boletin_valor') . " (boletin_id, categoria_id, anio, valor)
          VALUES (?,?,?,?)
          ON DUPLICATE KEY UPDATE valor=VALUES(valor)
        ");
        $up->execute([$boletinId, $catId, $anio, $val]);
      }

      // 2) factor de atención (texto largo)
      self::saveFactorAtencion($boletinId, $anio, $factorAtencion);

      $pdo->commit();
      return ['ok'=>true,'msg'=>'Guardado'];
    } catch (Throwable $e) {
      $pdo->rollBack();
      return ['ok'=>false,'msg'=>$e->getMessage()];
    }
  }

  /* =========================================================
     Factor de atención (texto largo)
     Tabla dedicada (recomendada):
       tbl_dash_boletin_factor_atencion
       - boletin_id (FK)
       - anio
       - texto (LONGTEXT)
       UNIQUE(boletin_id, anio)
  ========================================================== */
  private static function getFactorAtencion(int $boletinId, int $anio): string
  {
    $pdo = self::pdo();

    // Si la tabla aún no existe, no romper:
    try {
      $st = $pdo->prepare("
        SELECT texto
        FROM " . self::t('tbl_dash_boletin_factor_atencion') . "
        WHERE boletin_id=? AND anio=?
        LIMIT 1
      ");
      $st->execute([$boletinId, $anio]);
      $txt = $st->fetchColumn();
      return $txt ? (string)$txt : '';
    } catch (Throwable $e) {
      return '';
    }
  }

  private static function saveFactorAtencion(int $boletinId, int $anio, string $texto): void
  {
    $pdo = self::pdo();
    $texto = self::s($texto);

    // Si la tabla aún no existe, no romper:
    try {
      $st = $pdo->prepare("
        INSERT INTO " . self::t('tbl_dash_boletin_factor_atencion') . " (boletin_id, anio, texto)
        VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE texto=VALUES(texto)
      ");
      $st->execute([$boletinId, $anio, $texto]);
    } catch (Throwable $e) {
      // silencioso (para no romper guardado numérico)
    }
  }
  /* =========================================================
     Meta (tbl_dash_interior_meta)
     - getMeta: lee la fila id=1
     - saveMeta: UPSERT sobre id=1
  ========================================================== */
  public static function getMeta(): array
  {
    $pdo = self::pdo();
    try {
      $row = $pdo->query("
        SELECT anio_1, anio_2, boletin_no, fecha_cierre, fuente, tasa_homicidios, municipios_sin_homicidios, nota_html
        FROM " . self::t('tbl_dash_interior_meta') . "
        WHERE id=1 LIMIT 1
      ")->fetch(PDO::FETCH_ASSOC);

      if (!$row) {
        return ['ok'=>true,'meta'=>[
          'anio_1'=>2025,'anio_2'=>2026,'boletin_no'=>null,
          'fecha_cierre'=>'','fuente'=>'',
          'tasa_homicidios'=>'','municipios_sin_homicidios'=>0,'nota_html'=>''
        ]];
      }
      return ['ok'=>true,'meta'=>$row];
    } catch (Throwable $e) {
      return ['ok'=>false,'msg'=>$e->getMessage()];
    }
  }

  public static function saveMeta(array $data): array
  {
    $pdo = self::pdo();
    try {
      $anio1       = (int)($data['anio_1']                    ?? 2025);
      $anio2       = (int)($data['anio_2']                    ?? 2026);
      $boletinNo   = isset($data['boletin_no']) && $data['boletin_no'] !== '' ? (int)$data['boletin_no'] : null;
      $fechaCierre = self::s($data['fecha_cierre']             ?? '');
      $fuente      = self::s($data['fuente']                   ?? '');
      $tasa        = self::s($data['tasa_homicidios']          ?? '');
      $munSH       = (int)($data['municipios_sin_homicidios']  ?? 0);
      $nota        = self::s($data['nota_html']                ?? '');

      $st = $pdo->prepare("
        INSERT INTO " . self::t('tbl_dash_interior_meta') . "
          (id, anio_1, anio_2, boletin_no, fecha_cierre, fuente, tasa_homicidios, municipios_sin_homicidios, nota_html)
        VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          anio_1=VALUES(anio_1),
          anio_2=VALUES(anio_2),
          boletin_no=VALUES(boletin_no),
          fecha_cierre=VALUES(fecha_cierre),
          fuente=VALUES(fuente),
          tasa_homicidios=VALUES(tasa_homicidios),
          municipios_sin_homicidios=VALUES(municipios_sin_homicidios),
          nota_html=VALUES(nota_html)
      ");
      $st->execute([$anio1, $anio2, $boletinNo, $fechaCierre ?: null, $fuente, $tasa, $munSH, $nota]);
      return ['ok'=>true,'msg'=>'Meta guardada'];
    } catch (Throwable $e) {
      return ['ok'=>false,'msg'=>$e->getMessage()];
    }
  }

  private static function normKey($s): string {
  $s = (string)$s;
  $s = mb_strtolower(trim($s), 'UTF-8');
  $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
  $s = preg_replace('/\s+/', ' ', $s);
  return $s;
}

private static function findCatIndex(array $cats, string $needle): int {
  $needleN = self::normKey($needle);

  foreach ($cats as $i => $c) {
    if (self::normKey($c) === $needleN) return (int)$i;
  }
  foreach ($cats as $i => $c) {
    if (strpos(self::normKey($c), $needleN) !== false) return (int)$i;
  }
  return -1;
}

private static function serieVal(array $serie, int $idx): int {
  if ($idx < 0) return 0;
  return (int)($serie[$idx] ?? 0);
}

/**
 * Retorna la serie del año 2 si existe (payload comparativo),
 * si no, retorna la serie normal (payload por año).
 */
private static function getSerieForFactor(array $ds): array {
  if (isset($ds['serie_anio_2']) && is_array($ds['serie_anio_2'])) return $ds['serie_anio_2'];
  if (isset($ds['serie']) && is_array($ds['serie'])) return $ds['serie'];
  return [];
}
private static function buildFactorsFromDatasets(array $payload): array {

  $meta     = (isset($payload['meta']) && is_array($payload['meta'])) ? $payload['meta'] : [];
  $datasets = (isset($payload['datasets']) && is_array($payload['datasets'])) ? $payload['datasets'] : [];
  $anio2    = (int)($meta['anio_2'] ?? 2026);

  $factors = (isset($payload['factors']) && is_array($payload['factors'])) ? $payload['factors'] : [];

  // =====================================================
  // 1) Sicariato = S/DER POLITICO + DESAN (año2 o año)
  // =====================================================
  if (isset($datasets['sicariato']) && is_array($datasets['sicariato'])) {

    $ds   = $datasets['sicariato'];
    $cats = isset($ds['cats']) && is_array($ds['cats']) ? $ds['cats'] : [];
    $s2   = self::getSerieForFactor($ds);

    $iPol   = self::findCatIndex($cats, 'S/DER POLITICO');
    $valTot = self::serieVal($s2, $iPol);

    if (!isset($factors['sicariato']) || !is_array($factors['sicariato'])) $factors['sicariato'] = [];

    $factors['sicariato']['valor'] = $valTot;

    if (empty($factors['sicariato']['titulo_html'])) {
      $factors['sicariato']['titulo_html'] =
        '”Homicidio por Sicariato”<br><span style=”color:#666”>S/DER POLITICO</span> <span style=”color:#d32f2f”>'.$anio2.'</span>';
    }

    if (empty($factors['sicariato']['texto_html'])) {
      $factors['sicariato']['texto_html'] =
        'Cálculo '.$anio2.': <b>S/DER POLITICO</b> = <b>'.$valTot.'</b>.';
    }
  }

  // =====================================================
  // 2) Intolerancia = S/DER POLITICO (año2 o año)
  // =====================================================
  if (isset($datasets['intolerancia']) && is_array($datasets['intolerancia'])) {

    $ds   = $datasets['intolerancia'];
    $cats = isset($ds['cats']) && is_array($ds['cats']) ? $ds['cats'] : [];
    $s2   = self::getSerieForFactor($ds);

    $iPol = self::findCatIndex($cats, 'S/DER POLITICO');
    $val  = self::serieVal($s2, $iPol);

    if (!isset($factors['intolerancia']) || !is_array($factors['intolerancia'])) $factors['intolerancia'] = [];

    $factors['intolerancia']['valor'] = $val;

    if (empty($factors['intolerancia']['titulo_html'])) {
      $factors['intolerancia']['titulo_html'] =
        '“Homicidios por<br><span style="color:#d32f2f">Intolerancia</span>”<br><span style="color:#666">S/DER POLITICO</span> <span style="color:#d32f2f">'.$anio2.'</span>';
    }

    if (empty($factors['intolerancia']['texto_html'])) {
      $factors['intolerancia']['texto_html'] =
        'Cálculo '.$anio2.': <b>S/DER POLITICO</b> = <b>'.$val.'</b>.';
    }
  }

  $payload['factors'] = $factors;
  return $payload;
}

  /* =========================================================
     BOLETINES DIARIOS (tbl_boletin)
     - createBulletin: crea un nuevo boletín con fecha y número secuencial
     - getBulletins: lista los boletines existentes
     - getActiveBulletin: obtiene el boletín activo más reciente
     - getPayloadForBulletin: payload para un boletín específico
     - saveBulletinDailyValues: guarda valores en tablas de boletín
  ========================================================== */

  /**
   * Crea un nuevo boletín diario
   */
  public static function createBulletin(string $fecha): array
  {
    $pdo = self::pdo();

    try {
      // Validar fecha
      $dt = DateTime::createFromFormat('Y-m-d', $fecha);
      if (!$dt || $dt->format('Y-m-d') !== $fecha) {
        return ['ok' => false, 'msg' => 'Fecha inválida. Use YYYY-MM-DD.'];
      }

      // No permitir dos boletines el mismo día
      $dupStmt = $pdo->prepare("SELECT id FROM " . self::t('tbl_boletin') . " WHERE fecha=? LIMIT 1");
      $dupStmt->execute([$fecha]);
      if ($dupStmt->fetch()) {
        return ['ok' => false, 'msg' => 'Ya existe un boletín para el ' . $fecha];
      }

      // Obtener el número secuencial siguiente
      $maxStmt = $pdo->query("
        SELECT COALESCE(MAX(numero), 0) + 1
        FROM " . self::t('tbl_boletin')
      );
      $nextNum = (int)$maxStmt->fetchColumn();

      // Obtener meta global para heredar valores
      $metaRow = self::getMeta();
      $m = $metaRow['meta'] ?? [];
      $anio1      = (int)($m['anio_1']   ?? 2025);
      $anio2      = (int)($m['anio_2']   ?? 2026);
      $fechaCierre= self::s($m['fecha_cierre']            ?? '');
      $fuente     = self::s($m['fuente']                  ?? '');
      $tasa       = self::s($m['tasa_homicidios']         ?? '');
      $munSH      = (int)($m['municipios_sin_homicidios'] ?? 0);
      $nota       = self::s($m['nota_html']               ?? '');

      $stmt = $pdo->prepare("
        INSERT INTO " . self::t('tbl_boletin') . "
          (numero, fecha, anio_1, anio_2, fecha_cierre, fuente, tasa_homicidios, municipios_sin_homicidios, nota_html)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
      ");
      $stmt->execute([$nextNum, $fecha, $anio1, $anio2, $fechaCierre, $fuente, $tasa, $munSH, $nota]);

      $id = (int)$pdo->lastInsertId();

      return [
        'ok' => true,
        'boletin' => [
          'id' => $id,
          'numero' => $nextNum,
          'fecha' => $fecha,
          'anio_1' => $anio1,
          'anio_2' => $anio2,
          'fecha_cierre' => $fechaCierre,
          'fuente' => $fuente,
          'tasa_homicidios' => $tasa,
          'municipios_sin_homicidios' => $munSH,
          'nota_html' => $nota,
        ]
      ];
    } catch (Throwable $e) {
      return ['ok' => false, 'msg' => $e->getMessage()];
    }
  }

  /**
   * Lista los boletines existentes
   */
  public static function getBulletins(int $limit = 500): array
  {
    $pdo = self::pdo();
    try {
      $stmt = $pdo->prepare("
        SELECT id, numero, fecha, activo, anio_1, anio_2,
               fecha_cierre, fuente, tasa_homicidios,
               municipios_sin_homicidios, nota_html, created_at
        FROM " . self::t('tbl_boletin') . "
        ORDER BY fecha DESC, numero DESC
        LIMIT ?
      ");
      $stmt->bindValue(1, $limit, PDO::PARAM_INT);
      $stmt->execute();
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return ['ok' => true,  'boletines' => $rows];
    } catch (Throwable $e) {
      return ['ok' => false, 'msg' => $e->getMessage()];
    }
  }

  /**
   * Obtiene el boletín activo más reciente
   */
  public static function getActiveBulletin(): ?array
  {
    $pdo = self::pdo();
    try {
      $stmt = $pdo->prepare("
        SELECT id, numero, fecha, activo, anio_1, anio_2,
               fecha_cierre, fuente, tasa_homicidios,
               municipios_sin_homicidios, nota_html, created_at
        FROM " . self::t('tbl_boletin') . "
        WHERE activo = 1
        ORDER BY fecha DESC, numero DESC
        LIMIT 1
      ");
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $row ?: null;
    } catch (Throwable $e) {
      return null;
    }
  }

  /**
   * Obtiene la meta de un boletín específico (fecha_cierre, fuente, etc.)
   */
  public static function getBulletinMeta(int $id): array
  {
    $pdo = self::pdo();
    try {
      $stmt = $pdo->prepare("
        SELECT id, numero, fecha, anio_1, anio_2, fecha_cierre, fuente,
               tasa_homicidios, municipios_sin_homicidios, nota_html, activo
        FROM " . self::t('tbl_boletin') . "
        WHERE id=?
        LIMIT 1
      ");
      $stmt->execute([$id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$row) return ['ok' => false, 'msg' => 'Boletín no encontrado'];
      $row['boletin_no'] = $row['numero']; // el modal espera boletin_no
      return ['ok' => true, 'meta' => $row];
    } catch (Throwable $e) {
      return ['ok' => false, 'msg' => $e->getMessage()];
    }
  }

  /**
   * Guarda la meta de un boletín específico
   */
  public static function saveBulletinMeta(int $id, array $data): array
  {
    $pdo = self::pdo();
    try {
      $anio1   = (int)($data['anio_1']   ?? 2025);
      $anio2   = (int)($data['anio_2']   ?? 2026);
      $fechaC  = self::s($data['fecha_cierre']            ?? '');
      $fechaC  = $fechaC ?: null;
      $fechaB  = self::s($data['fecha']                   ?? '');
      // Si viene fecha, validar y actualizar; si no, mantener la actual
      if ($fechaB !== '') {
        $dupStmt = $pdo->prepare("SELECT id FROM " . self::t('tbl_boletin') . " WHERE fecha=? AND id!=? LIMIT 1");
        $dupStmt->execute([$fechaB, $id]);
        if ($dupStmt->fetch()) {
          return ['ok' => false, 'msg' => 'Ya existe otro boletín con la fecha ' . $fechaB];
        }
        $fechaCol = 'fecha=?,';
        $fechaVal = $fechaB;
      } else {
        $fechaCol = '';
        $fechaVal = null; // no se usa
      }
      $num     = isset($data['boletin_no']) && $data['boletin_no'] !== '' ? (int)$data['boletin_no'] : 0;
      $fuente  = self::s($data['fuente']                  ?? '');
      $tasa    = self::s($data['tasa_homicidios']         ?? '');
      $munSH   = (int)($data['municipios_sin_homicidios'] ?? 0);
      $nota    = self::s($data['nota_html']               ?? '');
      // Si se cambió el número, verificar que no exista otro boletín con ese número
      if ($num > 0) {
        $dupStmt = $pdo->prepare("SELECT id FROM " . self::t('tbl_boletin') . " WHERE numero=? AND id!=? LIMIT 1");
        $dupStmt->execute([$num, $id]);
        if ($dupStmt->fetch()) {
          return ['ok' => false, 'msg' => 'Ya existe otro boletín con el número ' . $num];
        }
        $numCol = 'numero=?,';
        $numVal = $num;
      } else {
        $numCol = '';
        $numVal = null;
      }
      $stmt = $pdo->prepare("
        UPDATE " . self::t('tbl_boletin') . "
        SET {$fechaCol} {$numCol} anio_1=?, anio_2=?, fecha_cierre=?, fuente=?,
            tasa_homicidios=?, municipios_sin_homicidios=?, nota_html=?
        WHERE id=?
      ");
      $baseParams = [$anio1, $anio2, $fechaC, $fuente, $tasa, $munSH, $nota, $id];
      $prefix = [];
      if ($fechaB !== '') $prefix[] = $fechaVal;
      if ($num > 0)       $prefix[] = $numVal;
      $stmt->execute(array_merge($prefix, $baseParams));
      return ['ok' => true];
    } catch (Throwable $e) {
      return ['ok' => false, 'msg' => $e->getMessage()];
    }
  }

  /**
   * Activa un boletín (desactiva los demás)
   */
  public static function setActiveBulletin(int $id): array
  {
    $pdo = self::pdo();
    try {
      $pdo->beginTransaction();
      $pdo->exec("UPDATE " . self::t('tbl_boletin') . " SET activo=0 WHERE activo=1");
      $stmt = $pdo->prepare("UPDATE " . self::t('tbl_boletin') . " SET activo=1 WHERE id=?");
      $stmt->execute([$id]);
      $pdo->commit();
      return ['ok' => true];
    } catch (Throwable $e) {
      $pdo->rollBack();
      return ['ok' => false, 'msg' => $e->getMessage()];
    }
  }

  /**
   * Payload para un boletín específico.
   * Devuelve la misma estructura que getPayloadForYear pero con
   * los valores del boletín. Si no hay valores en el boletín para
   * alguna categoría, usa el valor global del año.
   */
  public static function getPayloadForBulletin(int $boletinId): array
  {
    $pdo = self::pdo();

    // Obtener el boletín
    $stmt = $pdo->prepare("
      SELECT id, numero, fecha, anio_1, anio_2,
             fecha_cierre, fuente, tasa_homicidios,
             municipios_sin_homicidios, nota_html
      FROM " . self::t('tbl_boletin') . "
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->execute([$boletinId]);
    $boletin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$boletin) {
      return ['ok' => false, 'msg' => 'Boletín no encontrado'];
    }

    $anio = (int)$boletin['anio_2'];
    $anio1 = (int)$boletin['anio_1'];

    // Cards
    $cards = $pdo->query("
      SELECT id, card_key, card_num, titulo, subtitulo
      FROM " . self::t('tbl_dash_boletin') . "
      WHERE activo=1
      ORDER BY card_num ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $datasets = [];

    foreach ($cards as $card) {
      $cardId    = (int)$card['id'];
      $key       = (string)$card['card_key'];

      $catsStmt = $pdo->prepare("
        SELECT id, nombre
        FROM " . self::t('tbl_dash_boletin_categoria') . "
        WHERE boletin_id=?
        ORDER BY orden ASC, id ASC
      ");
      $catsStmt->execute([$cardId]);
      $catRows = $catsStmt->fetchAll(PDO::FETCH_ASSOC);

      $catIds   = array_map(fn($c) => (int)$c['id'], $catRows);
      $catNames = array_map(fn($c) => (string)$c['nombre'], $catRows);

      $serie = array_fill(0, count($catIds), 0);

      if (!empty($catIds)) {
        $in = implode(',', array_fill(0, count($catIds), '?'));

        // Intentar cargar desde el boletín diario
        $bulValStmt = $pdo->prepare("
          SELECT categoria, valor
          FROM " . self::t('tbl_boletin_valor') . "
          WHERE boletin_id=? AND card_key=?
        ");
        $bulValStmt->execute([$boletinId, $key]);
        $bulVals = [];
        foreach ($bulValStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
          $bulVals[(string)$r['categoria']] = (int)$r['valor'];
        }

        foreach ($catNames as $i => $catName) {
          if (isset($bulVals[$catName])) {
            $serie[$i] = $bulVals[$catName];
          } else {
            // Fallback al valor global del año
            $q = $pdo->prepare("
              SELECT valor
              FROM " . self::t('tbl_dash_boletin_valor') . "
              WHERE boletin_id=? AND anio=? AND categoria_id=?
              LIMIT 1
            ");
            $q->execute([$cardId, $anio, $catIds[$i]]);
            $serie[$i] = (int)($q->fetchColumn() ?: 0);
          }
        }
      }

      // Factor de atención desde boletín (solo texto guardado explícitamente)
      $factorAtencion = '';
      $faStmt = $pdo->prepare("
        SELECT texto
        FROM " . self::t('tbl_boletin_factor') . "
        WHERE boletin_id=? AND card_key=?
        LIMIT 1
      ");
      $faStmt->execute([$boletinId, $key]);
      $faRow = $faStmt->fetchColumn();
      $factorAtencion = $faRow ? (string)$faRow : '';
      // NOTA: no se usa fallback global para evitar duplicados en la vista

      $datasets[$key] = [
        'card' => [
          'card_num'  => (int)$card['card_num'],
          'titulo'    => (string)$card['titulo'],
          'subtitulo' => (string)$card['subtitulo'],
        ],
        'cats' => $catNames,
        'serie' => $serie,
        'factor_atencion' => $factorAtencion,
      ];
    }

    return [
      'ok' => true,
      'anio' => $anio,
      'boletin' => [
        'id' => (int)$boletin['id'],
        'numero' => (int)$boletin['numero'],
        'fecha' => $boletin['fecha'],
        'anio_1' => $anio1,
        'anio_2' => $anio,
        'fecha_cierre' => $boletin['fecha_cierre'],
        'fuente' => $boletin['fuente'],
        'tasa_homicidios' => $boletin['tasa_homicidios'],
        'municipios_sin_homicidios' => (int)$boletin['municipios_sin_homicidios'],
        'nota_html' => $boletin['nota_html'],
      ],
      'datasets' => $datasets,
    ];
  }

  /**
   * Guarda valores en un boletín diario
   */
  public static function saveBulletinDailyValues(
    int $boletinId,
    string $cardKey,
    array $valuesByCategoryName,
    string $factorAtencion = ''
  ): array
  {
    $pdo = self::pdo();

    try {
      $pdo->beginTransaction();

      // 1) Guardar valores numéricos
      foreach ($valuesByCategoryName as $catName => $val) {
        $catName = trim((string)$catName);
        $valNum  = (int)($val ?? 0);
        if ($catName === '') continue;

        $up = $pdo->prepare("
          INSERT INTO " . self::t('tbl_boletin_valor') . "
            (boletin_id, card_key, categoria, valor)
          VALUES (?, ?, ?, ?)
          ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ");
        $up->execute([$boletinId, $cardKey, $catName, $valNum]);
      }

      // 2) Guardar factor de atención
      $factorAtencion = self::s($factorAtencion);
      if ($factorAtencion !== '') {
        $fa = $pdo->prepare("
          INSERT INTO " . self::t('tbl_boletin_factor') . "
            (boletin_id, card_key, texto)
          VALUES (?, ?, ?)
          ON DUPLICATE KEY UPDATE texto = VALUES(texto)
        ");
        $fa->execute([$boletinId, $cardKey, $factorAtencion]);
      }

      $pdo->commit();
      return ['ok' => true, 'msg' => 'Guardado en boletín'];
    } catch (Throwable $e) {
      $pdo->rollBack();
      return ['ok' => false, 'msg' => $e->getMessage()];
    }
  }

  /**
   * Obtiene el payload comparativo para el dashboard
   * Usa el boletín activo si existe, si no usa datos globales por año
   */
  public static function getPayloadWithBulletin(?int $boletinId = null): array
  {
    // Si hay boletin_id específico, usar payload con fallback al boletín
    if ($boletinId !== null) {
      $bulPayload = self::getPayloadForBulletin($boletinId);
      if (!$bulPayload['ok']) return $bulPayload;

      // Obtener también los datos del año 1 (si el boletín no los tiene)
      // El payload comparativo necesita anio_1 y anio_2
      $anio1 = (int)$bulPayload['boletin']['anio_1'];
      $anio2 = (int)$bulPayload['boletin']['anio_2'];

      // Cargar payload completo comparativo
      $fullPayload = self::getPayload();

      // Reemplazar serie_anio_2 con los datos del boletín
      $bulDatasets = $bulPayload['datasets'];
      if (isset($fullPayload['datasets'])) {
        foreach ($fullPayload['datasets'] as $key => &$ds) {
          if (isset($bulDatasets[$key])) {
            $ds['serie_anio_2'] = $bulDatasets[$key]['serie'];
            $ds['factor_atencion_anio_2'] = $bulDatasets[$key]['factor_atencion'];
          }
        }
      }

      // Reemplazar meta con la del boletín
      if (isset($fullPayload['meta'])) {
        $fullPayload['meta']['boletin_no'] = $bulPayload['boletin']['numero'];
        $fullPayload['meta']['fecha_cierre'] = $bulPayload['boletin']['fecha_cierre'];
        $fullPayload['meta']['fuente'] = $bulPayload['boletin']['fuente'];
        $fullPayload['meta']['tasa_homicidios'] = $bulPayload['boletin']['tasa_homicidios'];
        $fullPayload['meta']['municipios_sin_homicidios'] = $bulPayload['boletin']['municipios_sin_homicidios'];
        $fullPayload['meta']['nota_html'] = $bulPayload['boletin']['nota_html'];
        $fullPayload['meta']['boletin_fecha'] = $bulPayload['boletin']['fecha'];
        $fullPayload['meta']['boletin_id'] = $bulPayload['boletin']['id'];
      }

      // Recalcular factores con los nuevos datos
      $fullPayload = self::buildFactorsFromDatasets($fullPayload);

      return $fullPayload;
    }

    // Sin boletín: usar datos globales por año
    return self::getPayload();
  }

  /**
   * Obtiene todos los valores de un boletín (todos los gráficos)
   */
  public static function getBulletinAllValues(int $boletinId): array
  {
    $pdo = self::pdo();
    try {
      $stmt = $pdo->prepare("
        SELECT card_key, categoria, valor
        FROM " . self::t('tbl_boletin_valor') . "
        WHERE boletin_id = ?
      ");
      $stmt->execute([$boletinId]);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $grouped = [];
      foreach ($rows as $r) {
        $key = $r['card_key'];
        if (!isset($grouped[$key])) $grouped[$key] = [];
        $grouped[$key][$r['categoria']] = (int)$r['valor'];
      }

      return ['ok' => true, 'values' => $grouped];
    } catch (Throwable $e) {
      return ['ok' => false, 'msg' => $e->getMessage()];
    }
  }
}
