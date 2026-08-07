<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioPaquetesPendientes as RecordatorioPaquetesPendientesMail;
use App\Models\User;
use App\Notifications\PaquetesEnRiesgoDigest;
use App\Support\PaquetesEnRiesgo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('aprobaciones:recordatorio-pendientes')]
#[Description('Avisa a administradores y operadores de clientes cuyo período está por vencer sin un paquete de aprobación enviado.')]
class RecordatorioPaquetesPendientes extends Command
{
    public function handle(): int
    {
        $enRiesgo = PaquetesEnRiesgo::detectar();

        if ($enRiesgo->isEmpty()) {
            $this->info('No hay paquetes de aprobación en riesgo hoy.');
            return self::SUCCESS;
        }

        $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERADOR])
            ->where('estatus', User::ESTATUS_ACTIVO)
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new RecordatorioPaquetesPendientesMail($enRiesgo));
            $admin->notify(new PaquetesEnRiesgoDigest($enRiesgo));
        }

        $criticos = $enRiesgo->where('nivel', 'critico')->count();
        $this->warn("{$enRiesgo->count()} cliente(s) en riesgo ({$criticos} urgente(s)). {$admins->count()} destinatario(s) notificado(s).");

        return self::SUCCESS;
    }
}
