<?php

namespace App\Http\Controllers;

use App\Mail\CodigoVerificacionBoveda;
use App\Models\BovedaAcceso;
use App\Models\BovedaCodigoVerificacion;
use App\Models\BovedaContrasena;
use App\Models\Cliente;
use App\Support\Ordenable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class BovedaContrasenaController extends Controller
{
    use Ordenable;

    public function index(Request $request)
    {
        $query = BovedaContrasena::with('cliente.user')
            ->join('clientes', 'clientes.id', '=', 'boveda_contrasenas.cliente_id')
            ->select('boveda_contrasenas.*');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('boveda_contrasenas.nombre_plataforma', 'like', "%{$q}%")
                    ->orWhere('boveda_contrasenas.usuario', 'like', "%{$q}%")
                    ->orWhere('clientes.nombre_negocio', 'like', "%{$q}%");
            });
        }

        $this->aplicarOrden($query, [
            'plataforma' => 'boveda_contrasenas.nombre_plataforma',
            'usuario'    => 'boveda_contrasenas.usuario',
        ], 'boveda_contrasenas.nombre_plataforma', 'asc');

        $credenciales = $query->paginate(10)->withQueryString();

        return view('boveda.index', compact('credenciales'));
    }

    public function create()
    {
        // Sin filtrar por estatus: la bóveda debe seguir accesible aunque el
        // cliente esté suspendido, inactivo o dado de baja.
        $clientes = Cliente::with('user')->orderBy('nombre_negocio')->get();

        return view('boveda.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'nombre_plataforma' => ['required', 'string', 'max:100'],
            'url_acceso' => ['nullable', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:255', 'confirmed'],
            'correo_asociado' => ['nullable', 'email', 'max:150'],
            'observaciones' => ['nullable', 'string'],
        ]);

        BovedaContrasena::create($validated + ['creado_por' => Auth::id()]);

        return redirect()->route('boveda.index')
            ->with('success', 'Credencial guardada correctamente.');
    }

    public function edit(BovedaContrasena $credencial)
    {
        $clientes = Cliente::with('user')->orderBy('nombre_negocio')->get();

        return view('boveda.edit', compact('credencial', 'clientes'));
    }

    public function update(Request $request, BovedaContrasena $credencial)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'nombre_plataforma' => ['required', 'string', 'max:100'],
            'url_acceso' => ['nullable', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'max:255', 'confirmed'],
            'correo_asociado' => ['nullable', 'email', 'max:150'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } elseif (!$this->sesionVerificada($request)) {
            // Cambiar la contraseña es tan sensible como verla: si alguien
            // secuestrara la sesión, podría reemplazarla sin que el dueño
            // real se entere. Se exige la misma verificación por código.
            // No se flashea la contraseña nueva de vuelta — ni siquiera
            // temporalmente en la sesión.
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', 'Verifica tu identidad con el botón "Mostrar" antes de guardar una contraseña nueva.');
        }

        $credencial->update($validated);

        return redirect()->route('boveda.index')
            ->with('success', 'Credencial actualizada correctamente.');
    }

    public function destroy(BovedaContrasena $credencial)
    {
        $credencial->delete();

        return redirect()->route('boveda.index')
            ->with('success', 'Credencial eliminada correctamente.');
    }

    /**
     * Minutos que dura una verificación por código antes de volver a
     * pedirla — evita tener que pedir código en cada clic dentro de la
     * misma sesión de trabajo, sin dejar la bóveda abierta para siempre.
     */
    private const MINUTOS_VERIFICACION = 10;

    /**
     * Revela la contraseña bajo demanda (nunca se manda en el HTML de la
     * página). Exige una verificación por código de correo antes de la
     * primera revelación de la sesión (o si ya expiró la ventana) — estar
     * con la sesión abierta ya no basta por sí solo. Cada revelado exitoso
     * queda auditado — quién y cuándo, nunca el valor — y la respuesta se
     * marca como no cacheable.
     */
    public function revelar(Request $request, BovedaContrasena $credencial)
    {
        if (!$this->sesionVerificada($request)) {
            return response()->json(['requiere_verificacion' => true], 428);
        }

        BovedaAcceso::create([
            'boveda_contrasena_id' => $credencial->id,
            'user_id'              => Auth::id(),
            'created_at'           => now(),
        ]);

        return response()
            ->json(['password' => $credencial->password])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    }

    /**
     * Genera y manda por correo un código de 6 dígitos al usuario actual.
     * Invalida cualquier código anterior sin usar, para que solo el más
     * reciente sea válido.
     */
    public function enviarCodigo(Request $request)
    {
        $user = Auth::user();

        BovedaCodigoVerificacion::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        BovedaCodigoVerificacion::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => now()->addMinutes(self::MINUTOS_VERIFICACION),
        ]);

        Mail::to($user->email)->send(new CodigoVerificacionBoveda($code));

        return response()->json(['enviado' => true]);
    }

    /**
     * Confirma el código recibido por correo. Si es válido, marca la
     * sesión como verificada por MINUTOS_VERIFICACION minutos.
     */
    public function confirmarCodigo(Request $request)
    {
        $request->validate(['codigo' => ['required', 'string']]);

        $user = Auth::user();

        $codigo = BovedaCodigoVerificacion::where('user_id', $user->id)
            ->where('code', $request->input('codigo'))
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if (!$codigo || !$codigo->isValid()) {
            return response()->json(['error' => 'Código inválido o expirado.'], 422);
        }

        $codigo->update(['used_at' => now()]);
        $request->session()->put('boveda_verificado_at', now());

        return response()->json(['verificado' => true]);
    }

    private function sesionVerificada(Request $request): bool
    {
        $verificadoAt = $request->session()->get('boveda_verificado_at');

        return $verificadoAt && now()->diffInMinutes($verificadoAt) < self::MINUTOS_VERIFICACION;
    }
}
