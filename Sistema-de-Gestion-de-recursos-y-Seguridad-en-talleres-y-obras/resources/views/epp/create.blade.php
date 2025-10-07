@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Epp
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Create') }} Epp</span>
                    </div>
                    <div class="card-body bg-white">
                        <form action="{{ route('recursos.store') }}" method="POST">
                            @csrf
                            <input type="text" name="nombre" placeholder="Nombre" required>
                            <textarea name="descripcion" placeholder="Descripción" required></textarea>
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
