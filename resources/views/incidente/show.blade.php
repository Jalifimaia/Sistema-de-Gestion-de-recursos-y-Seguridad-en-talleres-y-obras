@extends('layouts.app')

@section('template_title')
    {{ $incidente->name ?? __('Show') . " " . __('Incidente') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Incidente</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('incidentes.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Recurso:</strong>
                                    {{ $incidente->id_recurso }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Supervisor:</strong>
                                    {{ $incidente->id_supervisor }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Incidente Detalle:</strong>
                                    {{ $incidente->id_incidente_detalle }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Usuario Creacion:</strong>
                                    {{ $incidente->id_usuario_creacion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Usuario Modificacion:</strong>
                                    {{ $incidente->id_usuario_modificacion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Descripcion:</strong>
                                    {{ $incidente->descripcion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Incidente:</strong>
                                    {{ $incidente->fecha_incidente }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Creacion:</strong>
                                    {{ $incidente->fecha_creacion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Modificacion:</strong>
                                    {{ $incidente->fecha_modificacion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Cierre Incidente:</strong>
                                    {{ $incidente->fecha_cierre_incidente }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Resolucion:</strong>
                                    {{ $incidente->resolucion }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
