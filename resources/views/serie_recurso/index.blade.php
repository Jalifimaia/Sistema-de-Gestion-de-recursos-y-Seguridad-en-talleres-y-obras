@extends('layouts.app')

@section('template_title')
    Serie Recursos
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Serie Recursos') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('serie_recursos.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Create New') }}
                                </a>
                              </div>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>
                                        
									<th >Id Recurso</th>
									<th >Id Incidente Detalle</th>
									<th >Nro Serie</th>
									<th >Talle</th>
									<th >Fecha Adquisicion</th>
									<th >Fecha Vencimiento</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($serieRecursos as $serieRecurso)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
										<td >{{ $serieRecurso->id_recurso }}</td>
										<td >{{ $serieRecurso->id_incidente_detalle }}</td>
										<td >{{ $serieRecurso->nro_serie }}</td>
										<td >{{ $serieRecurso->talle }}</td>
										<td >{{ $serieRecurso->fecha_adquisicion }}</td>
										<td >{{ $serieRecurso->fecha_vencimiento }}</td>

                                            <td>
                                                <form action="{{ route('serie_recursos.destroy', $serieRecurso->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('serie_recursos.show', $serieRecurso->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('serie_recursos.edit', $serieRecurso->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {!! $serieRecursos->withQueryString()->links() !!}
            </div>
        </div>
    </div>
@endsection
