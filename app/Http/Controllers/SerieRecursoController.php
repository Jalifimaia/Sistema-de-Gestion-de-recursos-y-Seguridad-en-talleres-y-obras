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
use App\Models\SerieRecursoCodigo;


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


public function storeMultiple(Request $request, SerieGeneratorService $generator)
{
    $validated = $request->validate([
        
        'id_recurso' => 'required|exists:recurso,id',
        'version' => 'required|integer|min:1',
        'anio' => 'required|integer|min:2000',
        'lote' => 'required|integer|min:1',
        'fecha_adquisicion' => 'required|date|before_or_equal:today',
        'fecha_vencimiento' => 'nullable|date|after:fecha_adquisicion',
        'id_estado' => 'required|exists:estado,id',
        'combinaciones' => 'required|json',
    ]);

    $combinaciones = json_decode($request->combinaciones, true);
    if (empty($combinaciones)) {
        return response()->json(['message' => 'No hay combinaciones válidas'], 422);
    }

    $recurso = Recurso::findOrFail($request->id_recurso);

    foreach ($combinaciones as $comb) {
        $generator->createForCombination(
            $recurso,
            (int) $request->version,
            (int) $request->anio,
            (int) $request->lote,
            (int) $comb['color_id'],              // 👈 ahora pasás el ID
            $comb['talle'] ?? null,
            (int) $comb['cantidad'],
            [
                'fecha_adquisicion' => $request->fecha_adquisicion,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'id_estado' => $request->id_estado,
            ]
        );
    }

    return response()->json([
        'message' => 'Series creadas exitosamente',
        'total' => array_sum(array_column($combinaciones, 'cantidad'))
    ]);
}




public function createConRecurso($id)
{
    $recurso = Recurso::findOrFail($id);
    $colores = Color::select('id', 'nombre')
    ->whereRaw("nombre REGEXP '^[^0-9]+$'") // excluye nombres numéricos
    ->orderBy('nombre')
    ->get();


    // ✅ Agrupar talles por tipo
    $talles = \App\Models\Talle::all()
        ->groupBy('tipo')
        ->map(fn($group) => $group->pluck('nombre')->values());

    // ✅ Obtener estado "Disponible"
    $estadoDisponible = Estado::where('nombre_estado', 'Disponible')->firstOrFail();

    return view('serie_recurso.create', compact('recurso', 'colores', 'talles', 'estadoDisponible'));
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
    $query = request('search');

    $series = SerieRecurso::with(['recurso.subcategoria', 'recurso.categoria'])
        ->when($query, function ($q) use ($query) {
            $q->where(function ($sub) use ($query) {
                // 🔍 nro_serie: coincidencias en cualquier parte
                $sub->where('nro_serie', 'like', "%{$query}%")
                    // 🔍 nombre del recurso
                    ->orWhereHas('recurso', function ($qr) use ($query) {
                        $qr->where('nombre', 'like', "%{$query}%")
                           // 🔍 subcategoría
                           ->orWhereHas('subcategoria', function ($qs) use ($query) {
                               $qs->where('nombre', 'like', "%{$query}%");
                           })
                           // 🔍 categoría
                           ->orWhereHas('categoria', function ($qc) use ($query) {
                               $qc->where('nombre_categoria', 'like', "%{$query}%");
                           });
                    });
            });
        })
        ->orderByDesc('id')
        ->paginate(18) // 🔄 Laravel calcula cuántas páginas según resultados filtrados
        ->onEachSide(1)
        ->withQueryString(); // mantiene ?search en los links de paginación

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

    $series = $query->orderByDesc('id')->paginate(18)->onEachSide(1)->withQueryString();


    return view('serie_recurso.qrlote', compact('series'));
}



public function exportQrLotePdf()
{
    $page = request()->input('page', 1);
    $perPage = 18;

    $seriesPaginator = SerieRecurso::with('recurso')
        ->when(request('desde'), fn($q) => $q->whereDate('created_at', '>=', request('desde')))
        ->when(request('hasta'), fn($q) => $q->whereDate('created_at', '<=', request('hasta')))
        ->when(request('recurso_id'), fn($q) => $q->where('id_recurso', request('recurso_id')))
        ->when(request('subcategoria_id'), fn($q) => $q->whereHas('recurso', fn($q2) => $q2->where('id_subcategoria', request('subcategoria_id'))))
        ->orderByDesc('id')
        ->paginate($perPage, ['*'], 'page', $page);

    $series = $seriesPaginator->items(); // ← solo los 18 de la página actual

    $qrBase64s = [];
    foreach ($series as $serie) {
        $qrBase64s[$serie->id] = !empty($serie->codigo_qr)
            ? base64_encode(QrCode::format('png')->size(100)->generate((string) $serie->codigo_qr))
            : null;
    }

    $html = view('serie_recurso.qrlote_pdf', compact('series', 'qrBase64s'))->render();

    $pdf = Browsershot::html($html)
        ->format('A4')
        ->margins(10, 10, 10, 10)
        ->pdf();

    return response($pdf)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="QR_Lote_Pagina_' . $page . '.pdf"');
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
