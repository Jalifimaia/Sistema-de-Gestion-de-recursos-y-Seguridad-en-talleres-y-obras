@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Serie Recurso
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Serie Recurso</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('serie_recurso.update', $serieRecurso->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('serie_recurso.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
