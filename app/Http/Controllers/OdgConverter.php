<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OdgConverter extends Controller
{
    /**
     * Convert DOCX to ODG using LibreOffice
     */
    public static function convertDocxToOdg($docxPath, $odgPath)
    {
        try {
            // Verificar que LibreOffice esté instalado
            $command = "which libreoffice";
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception("LibreOffice no está instalado");
            }
            
            // Convertir DOCX a ODG
            $command = "libreoffice --headless --convert-to odg --outdir " . dirname($odgPath) . " " . $docxPath;
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception("Error al convertir DOCX a ODG: " . implode("\n", $output));
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Error en conversión ODG: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si un archivo es ODG
     */
    public static function isOdgFile($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $extension === 'odg';
    }
}
