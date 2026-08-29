<?php
session_start();
require '../vendor/autoload.php';
require_once '../config/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['usuario_id'])) { die("Acceso denegado"); }

try {
    $stmt = $pdo->query("
        SELECT p.nombre_completo, p.correo, p.institucion, p.telefono, p.edad, c.curso_nombre, c.terminado, p.fecha_registro, p.ip_registro 
        FROM participantes p
        LEFT JOIN certificaciones c ON p.correo = c.correo_participante
        ORDER BY p.fecha_registro DESC
    ");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Inscripciones');

    $encabezados = ['Nombre', 'Correo', 'Institución', 'Teléfono', 'Edad', 'Curso', 'Estatus', 'Fecha de Registro', 'IP'];
    $sheet->fromArray($encabezados, NULL, 'A1');
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);

    $fila = 2;
    foreach ($registros as $row) {
        $estatus = $row['terminado'] ? 'Terminado' : 'En progreso';
        $sheet->setCellValue('A' . $fila, $row['nombre_completo']);
        $sheet->setCellValue('B' . $fila, $row['correo']);
        $sheet->setCellValue('C' . $fila, $row['institucion']);
        $sheet->setCellValue('D' . $fila, $row['telefono']);
        $sheet->setCellValue('E' . $fila, $row['edad']);
        $sheet->setCellValue('F' . $fila, $row['curso_nombre']);
        $sheet->setCellValue('G' . $fila, $estatus);
        $sheet->setCellValue('H' . $fila, $row['fecha_registro']);
        $sheet->setCellValue('I' . $fila, $row['ip_registro']); // Aquí se imprime la IP
        $fila++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Reporte_Inscripciones_CertiDash.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    die("Error al generar el Excel: " . $e->getMessage());
}
?>