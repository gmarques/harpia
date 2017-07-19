@extends('layouts.modulos.integracaouema')

@section('title')
    Migrar notas
@stop

@section('subtitle')
    {{$oferta['trm_nome']}} :: {{$oferta['per_nome']}} @if(isset($oferta['ito_codigo_prog'])) :: {{$oferta['ito_codigo_prog']}} :: @endif {{$oferta['dis_nome']}}
@stop

@section('content')
    @if(!is_null($matriculados))
        <div class="row margin-bottom">
            <div class="col-md-12 text-right">
                <a href="#" class="btn btn-lg btn-success"><i class="fa fa-refresh"></i> Sincronizar todas as notas</a>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th width="40px">#</th>
                            <th width="100px">COD. PROG</th>
                            <th width="40px">POL</th>
                            <th>Nome</th>
                            <th>Nota</th>
                            <th>Final</th>
                            <th>Media</th>
                            <th>Situação</th>
                            <th width="100px">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($matriculados as $matricula)
                            <tr>
                                <td>{{$matricula['mof_id']}}</td>
                                <td>{{$matricula['itm_codigo_prog']}}</td>
                                <td>{{$matricula['itm_polo']}}</td>
                                <td>{{$matricula['pes_nome']}}</td>
                                <td>{{$matricula['mof_nota1']}}</td>
                                <td>{{$matricula['mof_final']}}</td>
                                <td>{{$matricula['mof_mediafinal']}}</td>
                                <td>
                                    <span
                                        data-toggle="tooltip"
                                        class="badge @if(in_array($matricula['mof_situacao_matricula'], ['aprovado_media', 'aprovado_final'])) bg-green @else bg-red @endif">
                                            {{$matricula['mof_situacao_matricula']}}
                                    </span>
                                </td>
                                <td>@if(isset($matricula['itm_codigo_prog']))<a href="#"class="btn btn-warning btn-migrar-nota"><i class="fa fa-exchange"></i> Migrar</a>@endif</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="box box-primary">
            <div class="box-body">Sem registros para apresentar</div>
        </div>
    @endif
@stop
