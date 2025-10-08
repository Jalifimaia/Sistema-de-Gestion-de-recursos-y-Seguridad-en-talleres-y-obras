@extends('layouts.app')

@section('template_title')
    {{ $detallePrestamo->name ?? __('Show') . " " . __('Detalle Prestamo') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Detalle Prestamo</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('detalle-prestamos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Prestamo:</strong>
                                    {{ $detallePrestamo->id_prestamo }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Serie:</strong>
                                    {{ $detallePrestamo->id_serie }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Recurso:</strong>
                                    {{ $detallePrestamo->id_recurso }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
