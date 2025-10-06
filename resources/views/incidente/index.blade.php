@extends('layouts.app')

@section('template_title')
    Incidentes
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Incidentes') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('incidentes.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
									<th >Id Supervisor</th>
									<th >Id Incidente Detalle</th>
									<th >Id Usuario Creacion</th>
									<th >Id Usuario Modificacion</th>
									<th >Descripcion</th>
									<th >Fecha Incidente</th>
									<th >Fecha Creacion</th>
									<th >Fecha Modificacion</th>
									<th >Fecha Cierre Incidente</th>
									<th >Resolucion</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($incidentes as $incidente)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
										<td >{{ $incidente->id_recurso }}</td>
										<td >{{ $incidente->id_supervisor }}</td>
										<td >{{ $incidente->id_incidente_detalle }}</td>
										<td >{{ $incidente->id_usuario_creacion }}</td>
										<td >{{ $incidente->id_usuario_modificacion }}</td>
										<td >{{ $incidente->descripcion }}</td>
										<td >{{ $incidente->fecha_incidente }}</td>
										<td >{{ $incidente->fecha_creacion }}</td>
										<td >{{ $incidente->fecha_modificacion }}</td>
										<td >{{ $incidente->fecha_cierre_incidente }}</td>
										<td >{{ $incidente->resolucion }}</td>

                                            <td>
                                                <form action="{{ route('incidentes.destroy', $incidente->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('incidentes.show', $incidente->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('incidentes.edit', $incidente->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $incidentes->withQueryString()->links() !!}
            </div>
        </div>
    </div>
@endsection
