<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsuarioToUsersSeeder extends Seeder
{
    public function run()
    {
        $usuarios = User::with('rol')->get();

        foreach ($usuarios as $usuario) {
            DB::table('users')->insert([
                'name' => $usuario->nombre_usuario,
                'email' => $usuario->email_usuario,
                'password' => Hash::make($usuario->password_usuario ?? 'password123'),
                'id_rol' => $usuario->id_rol,
                'created_at' => $usuario->fecha_creacion ?? now(),
                'updated_at' => $usuario->fecha_modificacion ?? now(),
                // Si agregaste estos campos en users:
                'usuario_creacion' => $usuario->usuario_creacion ?? null,
                'usuario_modificacion' => $usuario->usuario_modificacion ?? null,
            ]);
        }
    }
}
