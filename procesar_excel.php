<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();



require 'vendor/PhpSpreadsheet/Shared/File.php'; 
require 'vendor/PhpSpreadsheet/Shared/StringHelper.php'; 
require 'vendor/PhpSpreadsheet/Collection/CellsFactory.php';
require 'vendor/PhpSpreadsheet/Collection/Cells.php';
require 'vendor/PhpSpreadsheet/Psr/SimpleCache/CacheInterface.php';
require 'vendor/PhpSpreadsheet/Collection/Memory/SimpleCache3.php';
require 'vendor/PhpSpreadsheet/Shared/IntOrFloat.php';
require 'vendor/PhpSpreadsheet/IComparable.php';


require 'vendor/PhpSpreadsheet/Cell/AddressRange.php';
require 'vendor/PhpSpreadsheet/Cell/Coordinate.php';
require 'vendor/PhpSpreadsheet/Cell/DataType.php';
require 'vendor/PhpSpreadsheet/Cell/IValueBinder.php';
require 'vendor/PhpSpreadsheet/Cell/Cell.php';
require 'vendor/PhpSpreadsheet/Cell/DefaultValueBinder.php';
require 'vendor/PhpSpreadsheet/Cell/IgnoredErrors.php';
require 'vendor/PhpSpreadsheet/Calculation/Functions.php';



require 'vendor/PhpSpreadsheet/Reader/IReader.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/Namespaces.php';
require 'vendor/PhpSpreadsheet/Reader/IReadFilter.php';
require 'vendor/PhpSpreadsheet/Reader/DefaultReadFilter.php';
require 'vendor/PhpSpreadsheet/Reader/BaseReader.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx.php';
require 'vendor/PhpSpreadsheet/ReferenceHelper.php';
require 'vendor/PhpSpreadsheet/Reader/Security/XmlScanner.php';
require 'vendor/PhpSpreadsheet/Reader/Csv.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/BaseParserClass.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/Theme.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/PageSetup.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/Hyperlinks.php';    
require 'vendor/PhpSpreadsheet/Reader/Xlsx/Styles.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/SheetViews.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/SheetViewOptions.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/ColumnAndRowAttributes.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/Properties.php';
require 'vendor/PhpSpreadsheet/Reader/Xlsx/WorkbookView.php';


require 'vendor/PhpSpreadsheet/Shared/Date.php';
require 'vendor/PhpSpreadsheet/Shared/Font.php';
require 'vendor/PhpSpreadsheet/Calculation/ArrayEnabled.php';
require 'vendor/PhpSpreadsheet/Calculation/DateTimeExcel/Date.php';
require 'vendor/PhpSpreadsheet/Calculation/CalculationBase.php';
require 'vendor/PhpSpreadsheet/Calculation/CalculationLocale.php';
require 'vendor/PhpSpreadsheet/Calculation/Calculation.php';
require 'vendor/PhpSpreadsheet/Calculation/Engine/Logger.php';
require 'vendor/PhpSpreadsheet/Calculation/Engine/BranchPruner.php';
require 'vendor/PhpSpreadsheet/Calculation/Engine/CyclicReferenceStack.php';
require 'vendor/PhpSpreadsheet/WorkSheet/Worksheet.php';
require 'vendor/PhpSpreadsheet/Document/Properties.php';
require 'vendor/PhpSpreadsheet/Document/Security.php';

require 'vendor/PhpSpreadsheet/Style/Supervisor.php';
require 'vendor/PhpSpreadsheet/Style/Style.php';
require 'vendor/PhpSpreadsheet/Style/Font.php';
require 'vendor/PhpSpreadsheet/Style/Color.php';
require 'vendor/PhpSpreadsheet/Style/Fill.php';
require 'vendor/PhpSpreadsheet/Style/Borders.php';
require 'vendor/PhpSpreadsheet/Style/Border.php';
require 'vendor/PhpSpreadsheet/Style/Alignment.php';
require 'vendor/PhpSpreadsheet/Style/NumberFormat.php';
require 'vendor/PhpSpreadsheet/Style/Protection.php';
require 'vendor/PhpSpreadsheet/Style/RgbTint.php';
require 'vendor/PhpSpreadsheet/Style/NumberFormat/BaseFormatter.php';
require 'vendor/PhpSpreadsheet/Style/NumberFormat/Formatter.php';



require 'vendor/PhpSpreadsheet/Settings.php';
require 'vendor/PhpSpreadsheet/Theme.php';
require 'vendor/PhpSpreadsheet/IOFactory.php';
require 'vendor/PhpSpreadsheet/Spreadsheet.php';
require 'vendor/PhpSpreadsheet/Exception.php';


require 'vendor/PhpSpreadsheet/Reader/Exception.php';
require 'vendor/PhpSpreadsheet/Worksheet/PageSetup.php';
require 'vendor/PhpSpreadsheet/Worksheet/PageMargins.php';
require 'vendor/PhpSpreadsheet/Worksheet/HeaderFooter.php';
require 'vendor/PhpSpreadsheet/Worksheet/SheetView.php';
require 'vendor/PhpSpreadsheet/Worksheet/Validations.php';
require 'vendor/PhpSpreadsheet/Worksheet/Protection.php';
require 'vendor/PhpSpreadsheet/Worksheet/Dimension.php';
require 'vendor/PhpSpreadsheet/Worksheet/RowDimension.php';
require 'vendor/PhpSpreadsheet/Worksheet/ColumnDimension.php';
require 'vendor/PhpSpreadsheet/Worksheet/AutoFilter.php';
require 'vendor/PhpSpreadsheet/Worksheet/AutoFilter/Column/Rule.php';



require 'vendor/PhpSpreadsheet/Composer/Preg.php';
require './admin/include/generic_classes.php';
include './admin/classes/Usuario.php';
include './admin/classes/Desarrollo.php';
include './admin/classes/Secretarias.php';

// include './admin/include/head.php';


// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
}
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador() || $userType === Util::Alcalde());
if(!$isAdmin){
    require 'permiso_denegado.php';
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    
    // Mapeo de las columnas de la BD al Excel.
    $dbColumnMapping = [
        'eje_estrategico' => 'Eje Estratégico',
        'sector_pdd' => 'Sector PDD',
        'sector_cat_prod' => 'Sector Cat. Prod.',
        'producto_servicio_pdd' => 'Producto, Bien o Servicio PDD',
        'tbl_secretaria_id' => 'Nombre de Secretaria',
        'direccion_resp' => 'Dirección Responsable',
        'ps2024' => '2024',
        'avance_2024' => 'Avance 2024',
        'avance_2025' => 'Avance 2025',
        'ps2025' => '2025',
        'ps2026' => '2026',
        'ps2027' => '2027'
    ];
    
    // Asigna los IDs de sesión
    $userId = $_SESSION['session_user']['id'] ?? null;
    $municipioId = $_SESSION['session_user']['tbl_municipio_id'] ?? null;
    $userRol = $_SESSION['session_user']['tipo'] ?? ''; //obtener el rol


    if ($userRol === 'Gobernador' || $userRol === 'Secretario_Gobernacion') {
        $targetTable = 'tbl_plandesarrollo_gobernacion';
    } else {
        // otros
        $targetTable = 'tbl_plandesarrollo'; 
    }


    // Obtiene el tipo de archivo subido
    $fileMimeType = $_FILES['excelFile']['type'];
    $allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']; // Solo .xlsx

    // Si el tipo de archivo no está permitido detiene el script
    if (!in_array($fileMimeType, $allowedTypes)) {
        $_SESSION['message'] = "Error: Solo se permiten archivos de Excel (.xlsx).";
        header('Location: plan_desarrollo.php');
        exit();
    }    

    try {
        $dbConnection = new DbConection();
        $pdo = $dbConnection->openConect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $uploadedFilePath = $_FILES['excelFile']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFilePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray(null, true, true, true);
        
        // Obteniendo los encabezados del Excel 
        $excelHeaders = array_map('trim', array_shift($data));
        
        //mapeo de nombres de secretarías a IDs
        $secretarias = Secretarias::getAll(null);
        $secretariasMap = [];
        if ($secretarias['output']['valid']) {
            foreach ($secretarias['output']['response'] as $sec) {
                $secretariasMap[strtolower(trim($sec['secretaria']))] = $sec['id'];
            }
        }
        
        $preparedData = [];
        foreach ($data as $row) {
            // Ignorar filas vacías
            if (count(array_filter($row)) == 0) {
                continue;
            }

            $insertRow = [];
            
            // mapeo de la BD para asegurar el orden correcto
            foreach ($dbColumnMapping as $dbColumnName => $excelHeader) {
                $colIndex = array_search($excelHeader, $excelHeaders);
                
                if ($colIndex === false) {
                    $insertRow[] = null;
                } else {
                    $value = trim($row[$colIndex]);

                    
                    if ($dbColumnName === 'tbl_secretaria_id') {
                        $secretariaId = $secretariasMap[strtolower($value)] ?? null;
                        if ($secretariaId === null) {
                            $_SESSION['message'] = "Error: La secretaría '$value' no existe, revisa que esté creada en tu alcaldía.";
                            //die($_SESSION['message']);        
                            header('Location: plan_desarrollo.php');
                            exit();
                        }
                        $insertRow[] = $secretariaId;
                    } else {
                        $insertRow[] = $value;
                    }
                }
            }

            // Agrega campos de sesión
            $insertRow[] = $userId;
            $insertRow[] = $municipioId;

            $preparedData[] = $insertRow;
        }

        // Si vacío, detiene la ejecución
        if (empty($preparedData)) {
            $_SESSION['message'] = "El archivo Excel no contiene datos válidos para subir.";
            header('Location: plan_desarrollo.php');
            exit();

        }
        //die(print_r($preparedData, true));//////DEBUGG

        // Llama al método de la clase
        $result = Desarrollo::saveMany($preparedData, $targetTable);

        if ($result['output']['valid']) {
            $_SESSION['message'] = "¡Éxito! Se cargaron " . $result['output']['count'] . " metas del plan de desarrollo correctamente.";
        } else {
            $_SESSION['message'] = "Error al insertar los datos: " . $result['output']['message'];
        }
        //die(print_r($result, true));
        header('Location: plan_desarrollo.php');
        exit();

    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        $_SESSION['message'] = "Error al leer el archivo de Excel: " . $e->getMessage();
        header('Location: plan_desarrollo.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['message'] = "Error inesperado al procesar el archivo: " . $e->getMessage();
        header('Location: plan_desarrollo.php');
        exit();
    }
}

?>