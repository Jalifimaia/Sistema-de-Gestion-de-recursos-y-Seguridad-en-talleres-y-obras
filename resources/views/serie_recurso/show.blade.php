@extends('layouts.app')

@section('template_title')
    {{ $serieRecurso->name ?? __('Show') . " " . __('Serie Recurso') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Serie Recurso</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('serie_recursos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Recurso:</strong>
                                    {{ $serieRecurso->id_recurso }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Incidente Detalle:</strong>
                                    {{ $serieRecurso->id_incidente_detalle }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Nro Serie:</strong>
                                    {{ $serieRecurso->nro_serie }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Talle:</strong>
                                    {{ $serieRecurso->talle }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Adquisicion:</strong>
                                    {{ $serieRecurso->fecha_adquisicion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Vencimiento:</strong>
                                    {{ $serieRecurso->fecha_vencimiento }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
