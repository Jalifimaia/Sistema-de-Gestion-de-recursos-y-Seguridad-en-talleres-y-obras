<?php

namespace App\Services;

use App\Models\Prestamo;
use App\Models\DetallePrestamo;
use App\Models\SerieRecurso;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrestamoService
{
    /**
     * Crear un préstamo desde admin o terminal
     *
     * @param int $usuarioId -> trabajador
     * @param array $series -> ids de series seleccionadas
     * @param string $modo -> 'admin' o 'terminal'
     * @param int|null $adminId -> id del admin logueado (solo en modo admin)
     */
    public function crearPrestamo(int $usuarioId, array $series, string $modo = 'admin', ?int $adminId = null): Prestamo
    {
        return DB::transaction(function () use ($usuarioId, $series, $modo, $adminId) {
            // 👇 Diferencia clave entre admin y terminal
            $idUsuarioCreacion = ($modo === 'terminal')
                ? $usuarioId   // el mismo trabajador
                : ($adminId ?? auth()->id()); // admin logueado

            $prestamo = Prestamo::create([
                'id_usuario'              => $usuarioId,          // trabajador
                'id_usuario_creacion'     => $idUsuarioCreacion,  // admin o trabajador
                'id_usuario_modificacion' => $idUsuarioCreacion,
                'fecha_prestamo'          => Carbon::now(),
                'fecha_devolucion'        => Carbon::now()->addDay(), // 24h
                'estado'                  => 2, // Activo
                'fecha_creacion'          => Carbon::now(),
                'fecha_modificacion'      => Carbon::now(),
            ]);

            foreach ($series as $idSerie) {
                $serie = SerieRecurso::findOrFail($idSerie);

                if ($serie->id_estado != 1) {
                    throw new \Exception("La serie {$serie->nro_serie} no está disponible.");
                }

                DetallePrestamo::create([
                    'id_prestamo'        => $prestamo->id,
                    'id_serie'           => $idSerie,
                    'id_recurso'         => $serie->id_recurso,
                    'id_estado_prestamo' => 2, // Asignado
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                $serie->update(['id_estado' => 3]);

                DB::table('stock')->updateOrInsert(
                    ['id_serie_recurso' => $idSerie],
                    [
                        'id_recurso'        => $serie->id_recurso,
                        'id_estado_recurso' => 3,
                        'id_usuario'        => $usuarioId,
                    ]
                );
            }

            return $prestamo;
        });
    }
}
