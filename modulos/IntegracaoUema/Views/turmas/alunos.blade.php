@extends('layouts.modulos.integracaouema')

@section('title')
    Alunos
@stop

@section('subtitle')
    Integração dos aluno :: {{$turma->ofertacurso->curso->crs_nome}} :: {{$turma->trm_nome}}
@stop

@section('content')
    @if(!is_null($polos))
        <div class="row margin-bottom">
            <div class="col-md-12 text-right">
                <a href="#" class="btn btn-lg btn-success btn-sincronizar-matriculas" data-trm_id="{{$turma->trm_id}}"><i class="fa fa-refresh"></i> Sincronizar matrículas</a>
            </div>
        </div>
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
                                            <th width="140px">COD. PROG</th>
                                            <th>NOME PROG</th>
                                            <th width="80px">POLO</th>
                                            <th width="140px">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($polos[$i]['matriculas'] as $matricula)
                                            <tr data-mat_id="{{$matricula['mat_id']}}">
                                                <td>{{$matricula['mat_id']}}</td>
                                                <td class="fc-pes-nome"><p>{{$matricula['pes_nome']}}</p></td>
                                                <td><input type="text" class="form-control fc-cod-prog" value="{{$matricula['itm_codigo_prog']}}"></td>
                                                <td><input type="text" disabled="disabled" class="disabled form-control fc-nome-prog" value="{{$matricula['itm_nome_prog']}}"></td>
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

@section('scripts')
    <script type="text/javascript">
        var token = "{{ csrf_token() }}";

        $(".fc-cod-prog").blur(function(e){
            var linhaSelecionada = $(e.currentTarget).closest('tr');
            var codProg = e.currentTarget.value;

            $.harpia.httpget('{{url("/")}}/integracaouema/async/matriculas/' + codProg).done(function (response) {
                if(!$.isEmptyObject(response)) {
                    linhaSelecionada.find('.fc-nome-prog').val(response.nome);
                    linhaSelecionada.find('.fc-polo').val(response.polo);

                    linhaSelecionada.find('.btn-mapear-aluno').removeAttr('disabled').removeClass('disabled');
                } else {
                    toastr.error('Matrícula não localizada', '', {timeOut: 5000, progressBar: true});

                    linhaSelecionada.find('.fc-nome-prog').val('');
                    linhaSelecionada.find('.fc-polo').val('');
                    linhaSelecionada.find('.btn-mapear-aluno').attr('disabled', 'disabled').addClass('disabled');
                }
            });
        });

        $(".btn-mapear-aluno").click(function(e) {
            e.preventDefault();

            var linhaSelecionada = $(e.currentTarget).closest('tr');
            var currentTarget = $(e.currentTarget);

            var mat_id = linhaSelecionada.data('mat_id');
            var codigo_prog = linhaSelecionada.find('.fc-cod-prog').val();
            var nome_prog = linhaSelecionada.find('.fc-nome-prog').val();
            var polo = linhaSelecionada.find('.fc-polo').val();

            if (!mat_id || !codigo_prog || !nome_prog || !polo) {
                toastr.error('As informações da matrícula são obrigatórias', '', {timeOut: 5000, progressBar: true});

                return;
            }

            var data = {
                mat_id: mat_id,
                codigo_prog: codigo_prog,
                nome_prog: nome_prog,
                polo: polo,
                _token: token
            };

            $.harpia.httppost('{{url("/")}}/integracaouema/async/matriculas/integrar', data).done(function (response) {
                if(!$.isEmptyObject(response)) {
                    toastr.success('Informações integradas com sucesso', '', {timeOut: 5000, progressBar: true});

                    currentTarget.attr('disabled', 'disabled').addClass('disabled');
                } else {
                    toastr.error('Erro ao tentar integrar as informações da matrícula', '', {timeOut: 5000, progressBar: true});
                }
            });
        });

        $(".btn-sincronizar-matriculas").click(function(e) {
            e.preventDefault();
            var currentTarget = $(e.currentTarget);

            currentTarget.attr('disabled', 'disabled').addClass('disabled');

            var data = {
                trm_id: currentTarget.data('trm_id'),
                _token: token
            };

            $.harpia.httppost('{{url("/")}}/integracaouema/async/matriculas/integrarmatriculasturma', data).done(function (response) {
                if(!$.isEmptyObject(response)) {
                    toastr.success('Informações integradas com sucesso', '', {timeOut: 3000, progressBar: true});

                    setTimeout(function(){ location.reload(); }, 3000);
                } else {
                    toastr.error('Erro ao tentar integrar as informações da matrícula', '', {timeOut: 5000, progressBar: true});
                }
            });
        });
    </script>
@endsection
