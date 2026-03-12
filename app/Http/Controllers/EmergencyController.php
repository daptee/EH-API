<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityLogger;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmergencyController extends Controller
{
    /**
     * Reset masivo de contraseñas de todos los usuarios.
     *
     * Requiere:
     *  - Token JWT válido de super admin (middleware jwt.verify)
     *  - Body: { "confirm": true, "admin_password": "contraseña actual del admin" }
     *
     * Devuelve la lista de emails + nuevas contraseñas (una sola vez, no se vuelve a mostrar).
     */
    public function resetAllPasswords(Request $request)
    {
        $request->validate([
            'confirm'        => 'required|boolean|accepted',
            'admin_password' => 'required|string',
        ]);

        $admin = Auth::user();

        // Verificar que sea específicamente SUPERADMIN
        if ($admin->user_type_id !== UserType::SUPERADMIN) {
            SecurityLogger::adminAction(
                'emergency_reset_attempt_unauthorized_role',
                $request->ip(),
                $admin->email
            );
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Verificar que la contraseña del admin sea correcta
        if (!Hash::check($request->admin_password, $admin->password)) {
            SecurityLogger::adminAction(
                'emergency_reset_attempt_wrong_password',
                $request->ip(),
                $admin->email
            );
            return response()->json(['message' => 'Contraseña de administrador incorrecta.'], 403);
        }

        $users   = User::all();
        $results = [];

        foreach ($users as $user) {
            $newPassword = Str::random(12);

            $user->password          = Hash::make($newPassword);
            $user->password_expired  = true;
            $user->save();

            $results[] = [
                'id'           => $user->id,
                'email'        => $user->email,
                'new_password' => $newPassword,
            ];
        }

        SecurityLogger::adminAction(
            'emergency_password_reset',
            $request->ip(),
            $admin->email,
            'total_users:' . count($results)
        );

        return response()->json([
            'message' => 'Contraseñas reseteadas exitosamente. Esta lista no se volverá a mostrar.',
            'total'   => count($results),
            'users'   => $results,
        ]);
    }
}
