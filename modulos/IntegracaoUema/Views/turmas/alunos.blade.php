@extends('layouts.modulos.integracaouema')

@section('title')
    Alunos
@stop

@section('subtitle')
    Integração dos aluno :: {{$turma->ofertacurso->curso->crs_nome}} :: {{$turma->trm_nome}}
@stop

@section('content')
    @if(!is_null($polos))
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                @for ($i = 0; $i < count($polos); $i++)
                    <li @if($i === 0) class="active" @endif>
                        <a href="#{{$polos[$i]['pol_id']}}" data-toggle="tab">
                            {{$polos[$i]['pol_nome']}}
                            <span data-toggle="tooltip" class="badge bg-blue">{{count($polos[$i]['matriculas'])}}</span>
                        </a>
                    </li>
                @endfor
            </ul>
            <div class="tab-content">
                @for ($i = 0; $i < count($polos); $i++)
                    <div class="tab-pane @if($i === 0) active @endif" id="{{$polos[$i]['pol_id']}}">
                        @if(!empty($polos[$i]['matriculas']))
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>COD. PROG</th>
                                        <th>POLO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($polos[$i]['matriculas'] as $matricula)
                                        <tr>
                                            <td>{{$matricula['mat_id']}}</td>
                                            <td>{{$matricula['pes_nome']}}</td>
                                            <td>{{$matricula['itm_codigo_prog']}}</td>
                                            <td>{{$matricula['itm_polo']}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endfor
            </div>
            <!-- /.tab-content -->
        </div>
    @else
        <div class="box box-primary">
            <div class="box-body">Sem registros para apresentar</div>
        </div>
    @endif
@stop
