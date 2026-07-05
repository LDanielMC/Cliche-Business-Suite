<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Storage;

trait GeneraImagenesDemo
{
    /**
     * Genera una imagen JPEG de muestra (rectángulo de color + etiqueta)
     * y la guarda en el disco "public", devolviendo la ruta relativa.
     */
    private function generarImagenDemo(string $carpeta, string $etiqueta, array $colorRgb): string
    {
        $ancho = 640;
        $alto = 480;

        $imagen = imagecreatetruecolor($ancho, $alto);
        $color = imagecolorallocate($imagen, ...$colorRgb);
        imagefilledrectangle($imagen, 0, 0, $ancho, $alto, $color);

        $blanco = imagecolorallocate($imagen, 255, 255, 255);
        $lineas = explode("\n", wordwrap($etiqueta, 22, "\n"));
        $y = (int) ($alto / 2) - (count($lineas) * 8);
        foreach ($lineas as $linea) {
            $x = (int) ($ancho / 2) - (strlen($linea) * 4);
            imagestring($imagen, 5, max($x, 10), $y, $linea, $blanco);
            $y += 20;
        }

        ob_start();
        imagejpeg($imagen, null, 85);
        $contenido = ob_get_clean();
        imagedestroy($imagen);

        $ruta = $carpeta . '/' . \Illuminate\Support\Str::random(20) . '.jpg';
        Storage::disk('public')->put($ruta, $contenido);

        return $ruta;
    }
}
