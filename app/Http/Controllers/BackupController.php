<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    private const PATRON_ARCHIVO = '/^backup_\d{4}-\d{2}-\d{2}_\d{6}\.sql\.gz$/';
    private const CARPETA = 'backups';

    public function index()
    {
        $archivos = collect(Storage::disk('local')->files(self::CARPETA))
            ->filter(fn ($ruta) => preg_match(self::PATRON_ARCHIVO, basename($ruta)))
            ->map(fn ($ruta) => [
                'nombre' => basename($ruta),
                'tamano' => Storage::disk('local')->size($ruta),
                'fecha' => Storage::disk('local')->lastModified($ruta),
            ])
            ->sortByDesc('fecha')
            ->values();

        return view('backups.index', compact('archivos'));
    }

    public function generar()
    {
        $conexion = config('database.connections.mysql');
        $nombreArchivo = 'backup_' . now()->format('Y-m-d_His') . '.sql.gz';

        $process = new Process([
            config('backup.mysqldump_path'),
            '--host=' . $conexion['host'],
            '--port=' . $conexion['port'],
            '--user=' . $conexion['username'],
            '--databases',
            $conexion['database'],
        ]);
        $process->setEnv($this->procesoEnv($conexion['password']));
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            report(new ProcessFailedException($process));

            return redirect()->route('backups.index')
                ->with('error', 'No se pudo generar el respaldo. Verifica la configuración de MYSQLDUMP_PATH en el .env.');
        }

        Storage::disk('local')->put(self::CARPETA . '/' . $nombreArchivo, gzencode($process->getOutput(), 9));

        return redirect()->route('backups.index')
            ->with('success', "Respaldo {$nombreArchivo} generado correctamente.");
    }

    public function descargar(string $filename)
    {
        $filename = basename($filename);
        abort_unless(preg_match(self::PATRON_ARCHIVO, $filename), 404);

        $ruta = self::CARPETA . '/' . $filename;
        abort_unless(Storage::disk('local')->exists($ruta), 404);

        return Storage::disk('local')->download($ruta);
    }

    public function eliminar(string $filename)
    {
        $filename = basename($filename);
        abort_unless(preg_match(self::PATRON_ARCHIVO, $filename), 404);

        Storage::disk('local')->delete(self::CARPETA . '/' . $filename);

        return redirect()->route('backups.index')
            ->with('success', "Respaldo {$filename} eliminado correctamente.");
    }

    public function restaurar(string $filename)
    {
        $filename = basename($filename);
        abort_unless(preg_match(self::PATRON_ARCHIVO, $filename), 404);

        $ruta = self::CARPETA . '/' . $filename;
        abort_unless(Storage::disk('local')->exists($ruta), 404);

        $sql = gzdecode(Storage::disk('local')->get($ruta));
        $conexion = config('database.connections.mysql');

        $process = new Process([
            config('backup.mysql_path'),
            '--host=' . $conexion['host'],
            '--port=' . $conexion['port'],
            '--user=' . $conexion['username'],
        ]);
        $process->setEnv($this->procesoEnv($conexion['password']));
        $process->setInput($sql);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            report(new ProcessFailedException($process));

            return redirect()->route('backups.index')
                ->with('error', 'No se pudo restaurar el respaldo. Revisa los logs para más detalles.');
        }

        return redirect()->route('backups.index')
            ->with('success', "Base de datos restaurada correctamente desde {$filename}.");
    }

    /**
     * En Windows, mysqldump/mysql necesitan SystemRoot para inicializar Winsock.
     * Cuando este proceso PHP se lanza desde un shell que no la propaga
     * (p. ej. Git Bash), hay que inyectarla explícitamente al proceso hijo.
     */
    private function procesoEnv(string $password): array
    {
        return [
            'MYSQL_PWD' => $password,
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
        ];
    }
}
