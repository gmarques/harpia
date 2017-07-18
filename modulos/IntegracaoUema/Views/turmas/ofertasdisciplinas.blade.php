@extends('layouts.modulos.integracaouema')

@section('title')
    Disciplinas oferecidas
@stop

@section('subtitle')
    Integração dos aluno :: {{$turma->ofertacurso->curso->crs_nome}} :: {{$turma->trm_nome}}
@stop

@section('content')
    @if(!is_null($ofertasperiodos))
        @foreach($ofertasperiodos as $periodo)
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Período letivo: {{$periodo['per_nome']}}</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            @if(!empty($periodo['disciplinas']))
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nome</th>
                                            <th width="100px">COD. PROG</th>
                                            <th>DISC. PROG</th>
                                            <th width="100px" class="text-center">Matrículas Matraca/UEMA</th>
                                            <th>Ações</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($periodo['disciplinas'] as $disciplina)
                                            <tr>
                                                <td>{{$disciplina['ofd_id']}}</td>
                                                <td>{{$disciplina['dis_nome']}}</td>
                                                <td><input type="text" class="form-control fc-codigo-prog" value="{{$disciplina['ito_codigo_prog']}}"></td>
                                                <td><input type="text" disabled="disabled" class="form-control disabled fc-disciplina-prog" value="{{$disciplina['ito_disciplina_prog']}}"></td>
                                                <td class="text-center"><span data-toggle="tooltip" class="badge @if($disciplina['qtd_matriculas'] == $disciplina['qtd_matriculas_uema']) bg-green @else bg-red @endif">{{$disciplina['qtd_matriculas']}}/{{$disciplina['qtd_matriculas_uema']}}</span></td>
                                                <td><a href="#" disabled="disabled" class="btn btn-primary disabled btn-mapear-disciplina"><i class="fa fa-floppy-o"></i> Mapear</a></td>
                                                <td><a href="#" class="btn btn-warning btn-mapear-disciplina"><i class="fa fa-exchange"></i> Migrar notas</a></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div><!-- /.box-body -->

                    </div><!-- /.box -->
                </div><!-- /.col -->
            </div>
        @endforeach
    @else
        <div class="box box-primary">
            <div class="box-body">Sem registros para apresentar</div>
        </div>
    @endif
@stop
