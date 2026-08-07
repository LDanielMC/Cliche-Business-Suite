<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivation;
use App\Mail\PasswordResetCode as PasswordResetCodeMail;
use App\Models\CalendarioFoto;
use App\Models\GastoOperativo;
use App\Models\PaqueteAprobacion;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Support\FotosPendientesDecision;
use App\Support\PaquetesEnRiesgo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Bloquear cuentas dadas de baja o inactivas — suspendidos pueden entrar
            // porque el middleware CheckRole los restringe solo a las rutas de renovación.
            $estatusPermitidos = [User::ESTATUS_ACTIVO, User::ESTATUS_SUSPENDIDO];
            if (!in_array($user->estatus, $estatusPermitidos)) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Tu cuenta ha sido dada de baja. Contacta al administrador.',
                ]);
            }

            $request->session()->regenerate();
            $request->session()->put('last_activity', now()->timestamp);

            // Suspended clients can only access renovation routes — send them there directly.
            if ($user->role === User::ROLE_CLIENTE && $user->estatus === User::ESTATUS_SUSPENDIDO) {
                return redirect()->route('renovaciones.cliente.index');
            }

            return match ($user->role) {
                User::ROLE_ADMIN => redirect()->intended('/admin/dashboard'),
                User::ROLE_OPERADOR => redirect()->intended('/operador/dashboard'),
                User::ROLE_CLIENTE => redirect()->intended('/cliente/dashboard'),
                default => redirect()->intended('/'),
            };
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function adminDashboard()
    {
        try {
            // Get data for modern dashboard with proper error handling
            $totalClientes = \App\Models\Cliente::count();
            
            // Fix: Use 'monto' instead of 'cantidad' for PagoCliente
            $ingresosMes = \App\Models\PagoCliente::whereMonth('fecha_pago', now()->month)
                ->whereYear('fecha_pago', now()->year)
                ->where('estatus', 'pagado')
                ->sum('monto');
                
            // Fix: Get count of expenses instead of using non-existent field
            $gastosMes = \App\Models\GastoOperativo::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
                
            // Fix: Check if PaqueteAprobacion exists and has proper field
            $aprobacionesPendientes = 0;
            if (class_exists('\App\Models\PaqueteAprobacion')) {
                $aprobacionesPendientes = \App\Models\PaqueteAprobacion::where('estado', 'pendiente')->count();
            }
            
            // Get recent activity with error handling
            $recentActivity = [];
            try {
                $recentClients = \App\Models\Cliente::with('user')
                    ->latest()
                    ->take(5)
                    ->get();
                    
                foreach ($recentClients as $client) {
                    $recentActivity[] = [
                        'type' => 'client',
                        'title' => 'Nuevo cliente registrado',
                        'description' => $client->nombre_negocio,
                        'time' => $client->created_at->diffForHumans(),
                        'action_url' => route('clientes.show', $client)
                    ];
                }
            } catch (\Exception $e) {
                // Skip if there's an error
            }
            
            // Get top clients with error handling
            $topClients = [];
            try {
                $topClients = \App\Models\Cliente::with('user')
                    ->whereHas('user', fn($q) => $q->where('estatus', 'activo'))
                    ->orderBy('precio_mensual', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function ($client) {
                        return [
                            'id' => $client->id,
                            'name' => $client->nombre_negocio,
                            'service' => $client->servicio_contratado ?? 'N/A',
                            'revenue' => $client->precio_mensual ?? 0
                        ];
                    })
                    ->toArray();
            } catch (\Exception $e) {
                // Skip if there's an error
            }
            
            // Get upcoming tasks with error handling
            $upcomingTasks = [];
            try {
                // Mock data for now since we don't have a tasks table
                $upcomingTasks = [
                    [
                        'id' => 1,
                        'title' => 'Revisar aprobaciones pendientes',
                        'date' => now()->addDays(1)->format('d/m/Y'),
                        'client' => 'Varios',
                        'priority' => 'high'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Generar reporte mensual',
                        'date' => now()->addDays(3)->format('d/m/Y'),
                        'client' => 'Sistema',
                        'priority' => 'medium'
                    ]
                ];
            } catch (\Exception $e) {
                // Skip if there's an error
            }
            
            return view('dashboards.modern', [
                'user' => Auth::user(),
                'totalClientes' => $totalClientes,
                'ingresosMes' => $ingresosMes,
                'gastosMes' => $gastosMes,
                'aprobacionesPendientes' => $aprobacionesPendientes,
                'recentActivity' => $recentActivity,
                'topClients' => $topClients,
                'upcomingTasks' => $upcomingTasks
            ]);
            
        } catch (\Exception $e) {
            // Fallback to basic dashboard if there are errors
            return view('dashboards.modern', [
                'user' => Auth::user(),
                'totalClientes' => 0,
                'ingresosMes' => 0,
                'gastosMes' => 0,
                'aprobacionesPendientes' => 0,
                'recentActivity' => [],
                'topClients' => [],
                'upcomingTasks' => []
            ]);
        }
    }

    public function operadorDashboard()
    {
        $user = Auth::user();

        $enRiesgo = PaquetesEnRiesgo::detectar();

        $pendientesRevisionCliente = PaqueteAprobacion::where('estatus', PaqueteAprobacion::ESTATUS_PENDIENTE)->count();

        // Se agrupa por cliente + fecha: el calendario permite varias fotos el
        // mismo día, y sin agrupar cada una generaría su propia fila repetida.
        $proximasFotos = CalendarioFoto::with('cliente')
            ->where('estatus', CalendarioFoto::ESTATUS_PROGRAMADA)
            ->whereBetween('fecha_publicacion_programada', [now(), now()->addDays(7)])
            ->orderBy('fecha_publicacion_programada')
            ->get()
            ->groupBy(fn (CalendarioFoto $foto) => $foto->cliente_id . '|' . $foto->fecha_publicacion_programada->toDateString())
            ->map(fn ($grupo) => (object) [
                'cliente'                      => $grupo->first()->cliente,
                'fecha_publicacion_programada' => $grupo->first()->fecha_publicacion_programada,
                'cantidad_fotos'                => $grupo->count(),
            ])
            ->sortBy('fecha_publicacion_programada')
            ->take(8)
            ->values();

        $gastosMes = GastoOperativo::where('registrado_por', $user->id)
            ->whereMonth('fecha_gasto', now()->month)
            ->whereYear('fecha_gasto', now()->year)
            ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as total')
            ->first();

        // Cálculo en vivo (no depende de que el cron haya corrido) de fotos
        // que requieren una decisión manual: reserva vencida o aprobadas que
        // nunca se agendaron. Agrupadas por cliente para no repetir fila por
        // cada foto individual.
        $reservaPorExpirar = FotosPendientesDecision::reservaPorExpirar()
            ->groupBy('cliente_id')
            ->map(fn ($grupo) => ['cliente' => $grupo->first()->cliente, 'cantidad' => $grupo->count()]);

        $vencidasSinAgendar = FotosPendientesDecision::vencidasSinAgendar()
            ->groupBy(fn ($foto) => $foto->paquete->cliente_id)
            ->map(fn ($grupo) => ['cliente' => $grupo->first()->paquete->cliente, 'cantidad' => $grupo->count()]);

        // Un solo feed de "qué hacer hoy": riesgo primero (ya viene ordenado
        // por urgencia), luego lo próximo a publicar. Tope de 8 para que el
        // dashboard no se alargue — el resto se ve en Aprobaciones/Calendario.
        $feed = collect();

        foreach ($reservaPorExpirar as $item) {
            $feed->push([
                'grupo'        => 'urgente',
                'urgente'      => true,
                'cliente'      => $item['cliente']->nombre_negocio,
                'descripcion'  => ($item['cantidad'] > 1 ? $item['cantidad'] . ' fotos' : '1 foto') . ' en reserva por expirar — decidir si se conservan',
                'accion_url'   => route('aprobaciones.index'),
                'accion_texto' => 'Revisar',
                'icono'        => 'riesgo',
            ]);
        }

        foreach ($vencidasSinAgendar as $item) {
            $feed->push([
                'grupo'        => 'urgente',
                'urgente'      => true,
                'cliente'      => $item['cliente']->nombre_negocio,
                'descripcion'  => ($item['cantidad'] > 1 ? $item['cantidad'] . ' fotos' : '1 foto') . ' aprobadas sin agendar hace meses',
                'accion_url'   => route('calendario.index'),
                'accion_texto' => 'Agendar',
                'icono'        => 'riesgo',
            ]);
        }

        foreach ($enRiesgo as $item) {
            $feed->push([
                'grupo'        => $item['nivel'] === 'critico' ? 'urgente' : 'proximo',
                'urgente'      => $item['nivel'] === 'critico',
                'cliente'      => $item['cliente']->nombre_negocio,
                'descripcion'  => 'Vence ' . $item['etiqueta'] . ' — ' . ($item['paquete'] ? 'borrador sin enviar' : 'sin paquete creado'),
                'accion_url'   => $item['paquete']
                    ? route('aprobaciones.show', $item['paquete'])
                    : route('aprobaciones.create', ['cliente_id' => $item['cliente']->id]),
                'accion_texto' => $item['paquete'] ? 'Subir fotos' : 'Crear paquete',
                'icono'        => 'riesgo',
            ]);
        }

        foreach ($proximasFotos as $foto) {
            $feed->push([
                'grupo'        => 'proximo',
                'urgente'      => false,
                'cliente'      => $foto->cliente->nombre_negocio,
                'descripcion'  => ($foto->cantidad_fotos > 1 ? $foto->cantidad_fotos . ' fotos' : '1 foto') . ' — publicar el ' . $foto->fecha_publicacion_programada->format('d/m'),
                'accion_url'   => route('calendario.cliente-view', $foto->cliente),
                'accion_texto' => 'Ver',
                'icono'        => 'publicar',
            ]);
        }

        $feed = $feed->take(8);

        return view('dashboards.operador', compact(
            'user', 'enRiesgo', 'pendientesRevisionCliente', 'proximasFotos', 'gastosMes', 'feed'
        ));
    }

    public function clienteDashboard()
    {
        $user = Auth::user();

        $paquetesPendientes = PaqueteAprobacion::whereHas('cliente', fn ($q) => $q->where('user_id', $user->id))
            ->where('estatus', PaqueteAprobacion::ESTATUS_PENDIENTE)
            ->with('cliente')
            ->latest('fecha_limite')
            ->get();

        return view('dashboards.cliente', compact('user', 'paquetesPendientes'));
    }

    // Recuperación de contraseña por código temporal
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Respuesta genérica siempre: no revelar si el correo existe
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            PasswordResetCode::where('email', $request->email)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            PasswordResetCode::create([
                'email' => $request->email,
                'code' => $code,
                'expires_at' => now()->addMinutes(15),
            ]);

            Mail::to($request->email)->send(new PasswordResetCodeMail($code));
        }

        // Guardamos el email en sesión para pre-rellenar el campo en verify-code
        session(['reset_email' => $request->email]);

        return redirect()->route('password.verify')
            ->with('success', 'Si el correo está registrado, recibirás un código de 6 dígitos en breve.');
    }

    public function showVerifyCode()
    {
        return view('auth.verify-code', ['email' => session('reset_email', '')]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetCode) {
            throw ValidationException::withMessages([
                'code' => 'El código es inválido o ha expirado.',
            ]);
        }

        // Guardar en sesión: el código NO viaja en la URL (logs, historial del navegador)
        session([
            'reset_verified_email' => $request->email,
            'reset_verified_code'  => $request->code,
        ]);

        return redirect()->route('password.reset');
    }

    public function showNewPassword()
    {
        if (!session('reset_verified_email')) {
            return redirect()->route('password.request')
                ->with('error', 'Primero verifica tu código de recuperación.');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $email = session('reset_verified_email');
        $code  = session('reset_verified_code');

        if (!$email || !$code) {
            return redirect()->route('password.request')
                ->with('error', 'La sesión de recuperación ha expirado. Inicia el proceso nuevamente.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $resetCode = PasswordResetCode::where('email', $email)
            ->where('code', $code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetCode) {
            session()->forget(['reset_verified_email', 'reset_verified_code']);
            return redirect()->route('password.request')
                ->with('error', 'El código ha expirado. Solicita uno nuevo.');
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        $resetCode->used_at = now();
        $resetCode->save();

        session()->forget(['reset_verified_email', 'reset_verified_code', 'reset_email']);

        return redirect()->route('login')
            ->with('success', 'Tu contraseña ha sido actualizada. Inicia sesión con tu nueva contraseña.');
    }

    // Activación de cuenta para clientes nuevos
    public function showActivationForm($token)
    {
        $user = User::where('activation_token', $token)
            ->where('activation_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'El enlace de activación es inválido o ha expirado.');
        }

        return view('auth.set-password', ['token' => $token]);
    }

    public function setPassword(Request $request, $token)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::where('activation_token', $token)
            ->where('activation_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'El enlace de activación es inválido o ha expirado.');
        }

        $user->password = Hash::make($request->password);
        $user->activation_token = null;
        $user->activation_expires_at = null;
        $user->save();

        return redirect()->route('login')
            ->with('success', 'Cuenta activada correctamente. Inicia sesión con tu nueva contraseña.');
    }
}
