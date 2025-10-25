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


public function storeMultiple(Request $request): RedirectResponse
{
    \Log::info('QR generado: ' . 'QR-' . Str::uuid());

    $request->validate([
        'id_recurso' => 'required|exists:recurso,id',
        'cantidad' => 'required|integer|min:1|max:100',
        'nro_serie' => 'required|string',
        'talle' => 'nullable|string|max:50',
        'fecha_adquisicion' => 'required|date',
        'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_adquisicion',
        'id_estado' => 'required|exists:estado,id',
    ]);

for ($i = 1; $i <= $request->cantidad; $i++) {
    $serie = SerieRecurso::create([
        'id_recurso'        => $request->id_recurso,
        'nro_serie'         => $request->nro_serie . ' - ' . str_pad($i, 3, '0', STR_PAD_LEFT),
        'talle'             => $request->talle,
        'fecha_adquisicion' => $request->fecha_adquisicion,
        'fecha_vencimiento' => $request->fecha_vencimiento,
        'id_estado'         => $request->id_estado,
        'codigo_qr'         => 'QR-' . Str::uuid(), // 👈 Generación automática
    ]);

    \Log::info('Serie creada con QR: ' . $serie->codigo_qr);
}


    // redirigir a la misma vista de creación con mensaje en sesión para mostrar modal
    return redirect()->route('serie_recurso.createConRecurso', $request->id_recurso)
        ->with('success', 'Serie(s) guardada(s) correctamente.');
}



public function createConRecurso($id)
{
    $recurso = Recurso::findOrFail($id);
    $estados = Estado::all(); 

    return view('serie_recurso.create', compact('recurso', 'estados'));
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
