<!-- Matriculas -->
<div class="row">
    <div class="col-md-12">
        <h3>Cursos Matriculados</h3>

        @if ($aluno->matriculas->count())

            @foreach ($aluno->matriculas as $matricula)
                <div class="box box-primary collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{$matricula->turma->ofertacurso->curso->crs_nome}}</h3>
                        @if($matricula->mat_situacao == 'cursando')
                            <span class="label label-info">Cursando</span>
                        @elseif($matricula->mat_situacao == 'reprovado')
                            <span class="label label-danger">Reprovado</span>
                        @elseif($matricula->mat_situacao == 'concluido')
                            <span class="label label-success">Concluído</span>
                        @else
                            <span class="label label-warning">{{ucfirst($matricula->mat_situacao)}}</span>
                        @endif
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if (!empty($gradesCurriculares[$matricula->mat_id]['modulos']))
                            <div class="box-group" id="accordion">
                                @foreach ($gradesCurriculares[$matricula->mat_id]['modulos'] as $modulo)
                                    <div class="panel box box-default">
                                        <div class="box-header with-border">
                                            <h4 class="box-title">
                                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{$modulo['mdo_id']}}">
                                                    {{$modulo['mdo_nome']}}
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="collapse{{$modulo['mdo_id']}}" class="panel-collapse collapse">
                                            <div class="box-body">
                                                @if (!empty($modulo['ofertas_disciplinas']))
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th width="1%">#</th>
                                                            <th>Disciplina</th>
                                                            <th>Tipo</th>
                                                            <th>Nota 1</th>
                                                            <th>Nota 2</th>
                                                            <th>Nota 3</th>
                                                            <th>Conceito</th>
                                                            <th>Recuperação</th>
                                                            <th>Final</th>
                                                            <th>Média Final</th>
                                                            <th>Situação de Matrícula</th>
                                                        </tr>
                                                        @foreach ($modulo['ofertas_disciplinas'] as $disciplina)
                                                            <tr>
                                                                <td>{{$disciplina->mdc_id}}</td>
                                                                <td>{{$disciplina->dis_nome}}</td>
                                                                <td>{{ucfirst($disciplina->mdc_tipo_avaliacao)}}</td>
                                                                <td>{{$disciplina->mof_nota1}}</td>
                                                                <td>{{$disciplina->mof_nota2}}</td>
                                                                <td>{{$disciplina->mof_nota3}}</td>
                                                                <td>{{$disciplina->mof_conceito}}</td>
                                                                <td>{{$disciplina->mof_recuperacao}}</td>
                                                                <td>{{$disciplina->mof_final}}</td>
                                                                <td>{{$disciplina->mof_mediafinal}}</td>
                                                                <td>
                                                                    @if($disciplina->mof_situacao_matricula == 'cursando')
                                                                        <span class="label label-primary">Cursando</span>
                                                                    @elseif($disciplina->mof_situacao_matricula == 'cancelado')
                                                                        <span class="label label-warning">Cancelado</span>
                                                                    @elseif($disciplina->mof_situacao_matricula == 'aprovado_media')
                                                                        <span class="label label-success">Aprovado por Média</span>
                                                                    @elseif($disciplina->mof_situacao_matricula == 'aprovado_final')
                                                                        <span class="label label-success">Aprovado por Final</span>
                                                                    @elseif($disciplina->mof_situacao_matricula == 'reprovado_media')
                                                                        <span class="label label-danger">Reprovado por Média</span>
                                                                    @elseif($disciplina->mof_situacao_matricula == 'reprovado_final')
                                                                        <span class="label label-danger">Reprovado por Final</span>
                                                                    @else
                                                                        <span class="label label-warning">Não Matriculado</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                @else
                                                    <p>Módulo não possui disciplinas ofertadas</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="box-footer">
                                <a href="#" class="btn btn-primary pull-right">
                                    <i class="fa fa-file-pdf-o"></i> Imprimir Histórico
                                </a>
                            </div>
                        @else
                            <p>Curso não possui módulos cadastrados</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="box box-default collapsed-box">
                <div class="box-body">
                    <p>Aluno não possui matriculas em cursos</p>
                </div>
            </div>
        @endif
    </div>
</div>