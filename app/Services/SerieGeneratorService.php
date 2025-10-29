<?php

namespace App\Services;

use App\Models\SerieRecurso;
use App\Models\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class SerieGeneratorService
{
    public function createForCombination(
    $recurso,
    int $version,
    int $anio,
    int $lote,
    string $colorNombre,
    ?string $talleNombre,
    int $cantidad,
    array $extra = []
): Collection {
    // 🔸 Normalizar nombre del color (capitalizado, sin espacios extra)
    $colorNombreNormalizado = ucfirst(strtolower(trim($colorNombre)));

    // 🔸 Buscar o crear el color sin duplicar
    $color = Color::firstOrCreate(
        ['nombre' => $colorNombreNormalizado],
        ['nombre' => $colorNombreNormalizado]
    );

    $fechaAdq = $extra['fecha_adquisicion'] ?? now();
    $fechaVenc = $extra['fecha_vencimiento'] ?? null;
    $idEstado = $extra['id_estado'] ?? 1;

    return DB::transaction(function () use (
        $recurso, $version, $anio, $lote, $color, $talleNombre, $cantidad, $fechaAdq, $fechaVenc, $idEstado
    ) {
        // 🔸 Generar código base una sola vez
        $codigoBase = sprintf('%s-V%s-%s-%s-%s',
            $this->iniciales($recurso->nombre),
            $version,
            $this->iniciales($recurso->descripcion ?? ''),
            str_pad(substr((string)$anio, -2), 2, '0', STR_PAD_LEFT),
            str_pad((string)$lote, 2, '0', STR_PAD_LEFT)
        );

        // 🔸 Buscar o crear serie_recurso_codigo
        $codigoRow = DB::table('serie_recurso_codigo')
            ->where('id_recurso', $recurso->id)
            ->where('version', $version)
            ->where('anio', $anio)
            ->where('lote', $lote)
            ->first();

        $idCodigo = $codigoRow->id ?? DB::table('serie_recurso_codigo')->insertGetId([
            'id_recurso' => $recurso->id,
            'version' => $version,
            'anio' => $anio,
            'lote' => $lote,
            'codigo_base' => $codigoBase,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 🔸 Buscar último correlativo por color + talle dentro del grupo
        $query = DB::table('serie_recurso')
            ->where('id_serie_recurso_codigo', $idCodigo)
            ->where('id_color', $color->id);

        if (!empty($talleNombre)) {
            $query->where('talle', $talleNombre);
        } else {
            $query->whereNull('talle')->orWhere('talle', '');
        }

        $ultimoCorrel = $query->lockForUpdate()->max('correlativo');
        $inicio = $ultimoCorrel ? $ultimoCorrel + 1 : 1;

        $created = collect();

        for ($i = 0; $i < $cantidad; $i++) {
            $correl = $inicio + $i;
            $corr2d = str_pad((string)$correl, 2, '0', STR_PAD_LEFT);
            $nroSerie = $codigoBase . '-' . $corr2d;

            $id = DB::table('serie_recurso')->insertGetId([
                'id_recurso' => $recurso->id,
                'id_serie_recurso_codigo' => $idCodigo,
                'id_incidente_detalle' => null,
                'nro_serie' => $nroSerie,
                'talle' => $talleNombre,
                'id_color' => $color->id,
                'fecha_adquisicion' => $fechaAdq,
                'fecha_vencimiento' => $fechaVenc,
                'id_estado' => $idEstado,
                'codigo_qr' => 'QR-' . Str::uuid(),
                'correlativo' => $correl,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created->push(SerieRecurso::find($id));
        }

        return $created;
    });
}


protected function iniciales(?string $texto): string
{
    if (empty($texto)) return '';
    return strtoupper(collect(explode(' ', trim($texto)))
        ->map(fn($p) => mb_substr($p, 0, 1))
        ->join(''));
}

}
