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
                            <span data-toggle="tooltip" class="badge @if($polos[$i]['qtd_matriculas'] == $polos[$i]['qtd_matriculas_integradas']) bg-green @else bg-red @endif">{{$polos[$i]['qtd_matriculas']}}/{{$polos[$i]['qtd_matriculas_integradas']}}</span>
                        </a>
                    </li>
                @endfor
            </ul>
            <div class="tab-content">
                @for ($i = 0; $i < count($polos); $i++)
                    <div class="tab-pane @if($i === 0) active @endif" id="{{$polos[$i]['pol_id']}}">
                        @if(!empty($polos[$i]['matriculas']))
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nome</th>
                                            <th>COD. PROG</th>
                                            <th>POLO</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($polos[$i]['matriculas'] as $matricula)
                                            <tr>
                                                <td>{{$matricula['mat_id']}}</td>
                                                <td>{{$matricula['pes_nome']}}</td>
                                                <td><input type="text" class="form-control fc-codigo-prog" value="{{$matricula['itm_codigo_prog']}}"></td>
                                                <td><input type="text" disabled="disabled" class="disabled form-control fc-polo" value="{{$matricula['itm_polo']}}"></td>
                                                <td><a href="#" disabled="disabled" class="btn btn-primary disabled btn-mapear-aluno"><i class="fa fa-floppy-o"></i> Mapear aluno</a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
