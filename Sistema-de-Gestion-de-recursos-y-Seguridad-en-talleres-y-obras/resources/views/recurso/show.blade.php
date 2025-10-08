@extends('layouts.app')

@section('template_title')
    {{ $recurso->name ?? __('Show') . " " . __('Recurso') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Recurso</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('recursos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Categoria:</strong>
                                    {{ $recurso->id_categoria }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Estado:</strong>
                                    {{ $recurso->id_estado }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Incidente Detalle:</strong>
                                    {{ $recurso->id_incidente_detalle }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Usuario Creacion:</strong>
                                    {{ $recurso->id_usuario_creacion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Usuario Modificacion:</strong>
                                    {{ $recurso->id_usuario_modificacion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Nombre:</strong>
                                    {{ $recurso->nombre }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Descripcion:</strong>
                                    {{ $recurso->descripcion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Costo Unitario:</strong>
                                    {{ $recurso->costo_unitario }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Creacion:</strong>
                                    {{ $recurso->fecha_creacion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Modificacion:</strong>
                                    {{ $recurso->fecha_modificacion }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
