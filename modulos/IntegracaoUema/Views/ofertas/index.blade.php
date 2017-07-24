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
                                            <tr data-ofd_id="{{$disciplina['ofd_id']}}" data-periodo="{{substr($periodo['per_nome'], -1)}}" data-ano="{{substr($periodo['per_nome'], -4, 2)}}" data-qtd_matriculas="{{$disciplina['qtd_matriculas']}}">
                                                <td>{{$disciplina['ofd_id']}}</td>
                                                <td>{{$disciplina['dis_nome']}}</td>
                                                <td><input type="text" class="form-control fc-codigo-prog" value="{{$disciplina['ito_codigo_prog']}}"></td>
                                                <td><input type="text" disabled="disabled" class="form-control disabled fc-disciplina-prog" value="{{$disciplina['ito_disciplina_prog']}}"></td>
                                                <td class="text-center span-matriculas"><span data-toggle="tooltip" class="badge @if($disciplina['qtd_matriculas'] == $disciplina['qtd_matriculas_uema']) bg-green @else bg-red @endif">{{$disciplina['qtd_matriculas']}}/{{$disciplina['qtd_matriculas_uema']}}</span></td>
                                                <td><a href="#" disabled="disabled" class="btn btn-primary disabled btn-mapear-disciplina"><i class="fa fa-floppy-o"></i> Mapear</a></td>
                                                <td><a href="{{route('integracaouema.ofertas.migrar', $disciplina['ofd_id'])}}" @if(empty($disciplina['ito_codigo_prog'])) disabled="disabled" @endif class="@if(empty($disciplina['ito_codigo_prog'])) disabled @endif btn btn-warning btn-migrar-notas"><i class="fa fa-exchange"></i> Migrar notas</a></td>
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

@section('scripts')
    <script type="text/javascript">
        $(".fc-codigo-prog").blur(function(e){
            var linhaSelecionada = $(e.currentTarget).closest('tr');
            var coddisciplina = e.currentTarget.value;
            var periodo = linhaSelecionada.data('periodo');
            var ano = linhaSelecionada.data('ano');

            $.harpia.httpget('{{url("/")}}/integracaouema/async/ofertas/' + coddisciplina + '/' + periodo + '/' + ano).done(function (response) {
                if(!$.isEmptyObject(response)) {
                    linhaSelecionada.find('.fc-disciplina-prog').val(response.nome);
                    linhaSelecionada.find('.btn-mapear-disciplina').removeAttr('disabled').removeClass('disabled');

                    var spanMatriculas = linhaSelecionada.find('.span-matriculas');
                    spanMatriculas.empty();

                    var qtd_mat = linhaSelecionada.data('qtd_matriculas');
                    var spanClass = 'bg-green';
                    if (qtd_mat != response.qtd) {
                        var spanClass = 'bg-red';
                    }
                    spanMatriculas.append('<span data-toggle="tooltip" class="badge '+ spanClass +'">'+ qtd_mat +'/'+ response.qtd +'</span>');
                } else {
                    toastr.error('A disciplina não foi oferecida para este período ou não possui alunos matriculados.', '', {timeOut: 8000, progressBar: true});

                    linhaSelecionada.find('.fc-disciplina-prog').val('');
                    linhaSelecionada.find('.btn-mapear-disciplina').attr('disabled', 'disabled').addClass('disabled');
                    linhaSelecionada.find('.btn-migrar-notas').attr('disabled', 'disabled').addClass('disabled');
                }
            });
        });

        $(".btn-mapear-disciplina").click(function(e) {
            e.preventDefault();

            var token = "{{ csrf_token() }}";
            var linhaSelecionada = $(e.currentTarget).closest('tr');
            var currentTarget = $(e.currentTarget);

            var ofd_id = linhaSelecionada.data('ofd_id');
            var codigo_prog = linhaSelecionada.find('.fc-codigo-prog').val();
            var disciplina_prog = linhaSelecionada.find('.fc-disciplina-prog').val();

            if (!ofd_id || !codigo_prog || !disciplina_prog) {
                toastr.error('Todas as informções da disciplina são obrigatórios', '', {timeOut: 8000, progressBar: true});

                return;
            }

            var data = {
                ofd_id: ofd_id,
                codigo_prog: codigo_prog,
                disciplina_prog: disciplina_prog,
                _token: token
            };

            $.harpia.httppost('{{url("/")}}/integracaouema/async/ofertas/integrar', data).done(function (response) {
                if(!$.isEmptyObject(response)) {
                    toastr.success('Informações integradas com sucesso', '', {timeOut: 5000, progressBar: true});

                    currentTarget.attr('disabled', 'disabled').addClass('disabled');
                } else {
                    toastr.error('Erro ao tentar integrar as informações da disciplina', '', {timeOut: 5000, progressBar: true});
                }
            });
        });

    </script>
@endsection

