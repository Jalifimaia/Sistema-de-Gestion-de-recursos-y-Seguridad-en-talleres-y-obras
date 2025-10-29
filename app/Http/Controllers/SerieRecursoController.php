<?php

namespace App\Http\Controllers;

use App\Models\SerieRecurso;
use App\Models\Estado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\SerieRecursoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Http\Controllers\RecursoController;
use App\Models\Recurso;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Spatie\Browsershot\Browsershot;
use App\Models\Color;
use App\Services\SerieGeneratorService;


class SerieRecursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $serieRecursos = SerieRecurso::paginate();

        return view('serie_recurso.index', compact('serieRecursos'))
            ->with('i', ($request->input('page', 1) - 1) * $serieRecursos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */


public function storeMultiple(SerieRecursoRequest $request, SerieGeneratorService $generator)
{
    $data = $request->validated();

    $recurso = Recurso::with('subcategoria')->findOrFail($data['id_recurso']);
    $combinaciones = json_decode($data['combinaciones'], true) ?? [];

    $requiereTalle = in_array(strtolower($recurso->subcategoria->nombre ?? ''), ['chaleco', 'botas']);

    foreach ($combinaciones as $combo) {
        if (empty($combo['color_nombre'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'combinaciones' => 'Falta color en una combinación.'
            ]);
        }

        if ($requiereTalle && (empty($combo['talle']) || empty($combo['tipo_talle']))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'combinaciones' => 'Falta talle o tipo en una combinación.'
            ]);
        }

        if (empty($combo['cantidad']) || $combo['cantidad'] < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'combinaciones' => 'Cantidad inválida en una combinación.'
            ]);
        }
    }

    foreach ($combinaciones as $combo) {
        $generator->createForCombination(
            $recurso,
            $data['version'],
            $data['anio'],
            $data['lote'],
            $combo['color_nombre'],
            $requiereTalle ? $combo['talle'] : null,
            (int) $combo['cantidad'],
            [
                'fecha_adquisicion' => $data['fecha_adquisicion'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'id_estado' => $data['id_estado'],
            ]
        );
    }

    return redirect()->route('inventario')->with('success', 'Series creadas correctamente.');
}



public function createConRecurso($id)
{
    $recurso = Recurso::findOrFail($id);
    $estados = Estado::all();
    $colores = Color::all();

    // ✅ Agrupar talles por tipo
    $talles = \App\Models\Talle::all()
        ->groupBy('tipo')
        ->map(fn($group) => $group->pluck('nombre')->values());

    return view('serie_recurso.create', compact('recurso', 'estados', 'colores', 'talles'));
}

    /**
     * Display the specified resource.
     */
    public function show($id): View
{
    $serieRecurso = SerieRecurso::findOrFail($id);

    return view('serie_recurso.show', compact('serieRecurso'));
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $serieRecurso = SerieRecurso::findOrFail($id);


        return view('serie_recurso.edit', compact('serieRecurso'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SerieRecursoRequest $request, SerieRecurso $serieRecurso): RedirectResponse
    {
        $serieRecurso->update($request->validated());

        return Redirect::route('serie_recurso.index')
            ->with('success', 'SerieRecurso updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        $serie = SerieRecurso::findOrFail($id);
        $serie->delete();


        return Redirect::route('serie_recurso.index')
            ->with('success', 'SerieRecurso deleted successfully');
    }

    public function qrIndex(): View
{
    $series = SerieRecurso::with('recurso')->orderByDesc('id')->get();
    return view('serie_recurso.qrindex', compact('series'));
}

public function exportQrPdf($id)
{
    $serie = SerieRecurso::with('recurso')->findOrFail($id);

    // ✅ Generar QR como imagen PNG en base64 (sin Imagick)
    $qrBase64 = base64_encode(\QrCode::format('png')->size(200)->generate($serie->codigo_qr));

    return \Pdf::loadView('serie_recurso.qrpdf', [
        'serie' => $serie,
        'qrBase64' => $qrBase64
    ])
    ->setPaper('a6')
    ->download('QR_' . $serie->nro_serie . '.pdf');
}


public function qrLote()
{
    $query = SerieRecurso::with('recurso');

    if (request('desde')) {
        $query->whereDate('created_at', '>=', request('desde'));
    }

    if (request('hasta')) {
        $query->whereDate('created_at', '<=', request('hasta'));
    }

    if (request('recurso_id')) {
        $query->where('id_recurso', request('recurso_id'));
    }

    if (request('subcategoria_id')) {
        $query->whereHas('recurso', function ($q) {
            $q->where('id_subcategoria', request('subcategoria_id'));
        });
    }

    $series = $query->orderByDesc('id')->get();

    return view('serie_recurso.qrlote', compact('series'));
}



public function exportQrLotePdf()
{
    $query = SerieRecurso::with('recurso');

    // Filtros opcionales
    if (request('desde')) {
        $query->whereDate('created_at', '>=', request('desde'));
    }

    if (request('hasta')) {
        $query->whereDate('created_at', '<=', request('hasta'));
    }

    if (request('recurso_id')) {
        $query->where('id_recurso', request('recurso_id'));
    }

    if (request('subcategoria_id')) {
        $query->whereHas('recurso', function ($q) {
            $q->where('id_subcategoria', request('subcategoria_id'));
        });
    }

    // Si no hay filtros, usar las últimas 30
    if (!request()->hasAny(['desde', 'hasta', 'recurso_id', 'subcategoria_id'])) {
        $query->orderByDesc('id')->take(30);
    }

    $series = $query->orderBy('id')->get();

    $qrBase64s = [];
    foreach ($series as $serie) {
        if (!empty($serie->codigo_qr)) {
            $qrBase64s[$serie->id] = base64_encode(
                \QrCode::format('png')->size(100)->generate((string) $serie->codigo_qr)
            );
        } else {
            $qrBase64s[$serie->id] = null;
        }
    }

    $html = view('serie_recurso.qrlote_pdf', compact('series', 'qrBase64s'))->render();

    $pdf = \Spatie\Browsershot\Browsershot::html($html)
        ->format('A4')
        ->margins(10, 10, 10, 10)
        ->pdf();

    return response($pdf)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="QR_Lote.pdf"');
}


public function showQr($id)
{
    $serie = SerieRecurso::with('recurso')->findOrFail($id);

    return view('serie_recurso.qrshow', compact('serie'));
}


public function qrSnippet($id)
{
    $serie = SerieRecurso::findOrFail($id);

    if (!$serie->codigo_qr) {
        return '<span class="text-muted">Sin QR</span>';
    }

    return \QrCode::size(100)->generate($serie->codigo_qr);
}


}
