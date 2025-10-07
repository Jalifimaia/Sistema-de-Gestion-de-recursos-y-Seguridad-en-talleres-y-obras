@extends('layouts.app')

@section('template_title')
    {{ $incidenteDetalle->name ?? __('Show') . " " . __('Incidente Detalle') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Incidente Detalle</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('incidente-detalles.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Incidente:</strong>
                                    {{ $incidenteDetalle->id_incidente }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Serie:</strong>
                                    {{ $incidenteDetalle->id_serie }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Descripcion:</strong>
                                    {{ $incidenteDetalle->descripcion }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
