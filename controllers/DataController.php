<?php
session_start();
require '../vendor/autoload.php';
require_once '../config/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class Nodo {
    public $dato;
    public $siguiente;
    public function __construct($dato) {
        $this->dato = $dato;
        $this->siguiente = null;
    }
}

class ListaEnlazada {
    public $cabeza = null;
    public $cola = null;

    public function insertar($dato) {
        $nuevoNodo = new Nodo($dato);
        if ($this->cabeza == null) {
            $this->cabeza = $nuevoNodo;
            $this->cola = $nuevoNodo;
        } else {
            $this->cola->siguiente = $nuevoNodo;
            $this->cola = $nuevoNodo;
        }
    }

    public function vaciar() {
        $this->cabeza = null;
        $this->cola = null;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_dual_data'])) {
    if (!isset($_SESSION['usuario_id'])) {
        die("Acceso denegado.");
    }

    $usuario_id = $_SESSION['usuario_id'];
    
    $archivo_part = $_FILES['excel_participantes'];
    $archivo_cert = $_FILES['excel_certificaciones'];
    
    $ruta_part = '../uploads/part_' . time() . '_' . basename($archivo_part['name']);
    $ruta_cert = '../uploads/cert_' . time() . '_' . basename($archivo_cert['name']);

    if (move_uploaded_file($archivo_part['tmp_name'], $ruta_part) && move_uploaded_file($archivo_cert['tmp_name'], $ruta_cert)) {
        try {
            $pdo->beginTransaction();

            $docPart = IOFactory::load($ruta_part);
            $filasPart = $docPart->getActiveSheet()->toArray();
            $listaParticipantes = new ListaEnlazada();

            for ($i = 1; $i < count($filasPart); $i++) {
                $fila = $filasPart[$i];
                if (!empty($fila[0]) && !empty($fila[1])) {
                    $listaParticipantes->insertar([
                        'nombre' => $fila[0],
                        'correo' => $fila[1],
                        'telefono' => $fila[2] ?? null,
                        'edad' => !empty($fila[3]) ? (int)$fila[3] : null,
                        'medio' => $fila[4] ?? null,
                        'ip' => $fila[5] ?? null
                    ]);
                }
            }

            $stmtPart = $pdo->prepare("INSERT IGNORE INTO participantes (usuario_id, nombre_completo, correo, telefono, edad, medio_contacto, ip_registro) VALUES (:uid, :nombre, :correo, :tel, :edad, :medio, :ip)");
            
            $actual = $listaParticipantes->cabeza;
            while ($actual != null) {
                $stmtPart->execute([
                    'uid' => $usuario_id,
                    'nombre' => $actual->dato['nombre'],
                    'correo' => $actual->dato['correo'],
                    'tel' => $actual->dato['telefono'],
                    'edad' => $actual->dato['edad'],
                    'medio' => $actual->dato['medio'],
                    'ip' => $actual->dato['ip']
                ]);
                $actual = $actual->siguiente;
            }
            $listaParticipantes->vaciar(); 

       
            $docCert = IOFactory::load($ruta_cert);
            $filasCert = $docCert->getActiveSheet()->toArray();
            $listaCertificados = new ListaEnlazada();

            for ($i = 1; $i < count($filasCert); $i++) {
                $fila = $filasCert[$i];
                if (!empty($fila[0]) && !empty($fila[1])) {
                    $terminado = 0;
                    $valorCelda = strtolower(trim($fila[2] ?? ''));
                    if (in_array($valorCelda, ['si', 'sí', '1', 'terminado', 'true'])) {
                        $terminado = 1;
                    }

                    $listaCertificados->insertar([
                        'correo' => $fila[0],
                        'curso' => $fila[1],
                        'terminado' => $terminado
                    ]);
                }
            }

            $stmtCert = $pdo->prepare("INSERT INTO certificaciones (correo_participante, curso_nombre, terminado) VALUES (:correo, :curso, :terminado)");
            
            $actualCert = $listaCertificados->cabeza;
            while ($actualCert != null) {
                try {
                    $stmtCert->execute([
                        'correo' => $actualCert->dato['correo'],
                        'curso' => $actualCert->dato['curso'],
                        'terminado' => $actualCert->dato['terminado']
                    ]);
                } catch (PDOException $e) {
                
                }
                $actualCert = $actualCert->siguiente;
            }
            $listaCertificados->vaciar(); 

            $pdo->commit();
            
            unlink($ruta_part);
            unlink($ruta_cert);

            echo "<script>alert('¡Datos cruzados y guardados exitosamente!'); window.location.href='../views/dashboard.php';</script>";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            echo "Error crítico durante el procesamiento: " . $e->getMessage();
        }
    } else {
        echo "Error al subir los archivos.";
    }
}
?>