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
                            <th>Nota PROG</th>
                            <th>Final</th>
                            <th>Final PROG</th>
                            <th>Media</th>
                            <th>Media PROG</th>
                            <th>Situação</th>
                            <th width="100px">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($matriculados as $matricula)
                            <tr @if(isset($matricula['itm_codigo_prog']) && $matricula['mof_nota1'] != null && $matricula['mof_nota1'] != $matricula['prog_nota1']) style="background-color: #f2dede;" @endif>
                                <td>{{$matricula['mof_id']}}</td>
                                <td>{{$matricula['itm_codigo_prog']}}</td>
                                <td>{{$matricula['itm_polo']}}</td>
                                <td>{{$matricula['pes_nome']}}</td>
                                <td>{{$matricula['mof_nota1']}}</td>
                                <td>{{$matricula['prog_nota1']}}</td>
                                <td>{{$matricula['mof_final']}}</td>
                                <td>{{$matricula['prog_final']}}</td>
                                <td>{{$matricula['mof_mediafinal']}}</td>
                                <td>{{$matricula['prog_media']}}</td>
                                <td>
                                    <span
                                        data-toggle="tooltip"
                                        class="badge @if(in_array($matricula['mof_situacao_matricula'], ['aprovado_media', 'aprovado_final'])) bg-green @else bg-red @endif">
                                            {{$matricula['mof_situacao_matricula']}}
                                    </span>
                                </td>
                                <td>
                                    @if(
                                        isset($matricula['itm_codigo_prog'])
                                        && $matricula['mof_nota1'] != null
                                        && $matricula['mof_nota1'] != $matricula['prog_nota1']
                                    )
                                        <a href="#"class="btn btn-warning btn-migrar-nota" data-ofd_id="{{$matricula['mof_ofd_id']}}" data-mat_id="{{$matricula['mof_mat_id']}}"><i class="fa fa-exchange"></i> Migrar</a>
                                    @endif</td>
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


@section('scripts')
    <script type="text/javascript">

        $(".btn-migrar-nota").click(function(e) {
            e.preventDefault();

            var token = "{{ csrf_token() }}";
            var currentTarget = $(e.currentTarget);

            var ofd_id = currentTarget.data('ofd_id');
            var mat_id = currentTarget.data('mat_id');

            if (!ofd_id || !mat_id) {
                toastr.error('Todas as informções da matrícula são obrigatórios', '', {timeOut: 8000, progressBar: true});
            }

            var data = {
                ofd_id: ofd_id,
                mat_id: mat_id,
                _token: token
            };

            $.harpia.httppost('{{url("/")}}/integracaouema/async/matriculas/migrarnotaaluno', data).done(function (response) {
                if(!$.isEmptyObject(response)) {
                    toastr.success('Notas migradas com sucesso', '', {timeOut: 5000, progressBar: true});

//                    currentTarget.attr('disabled', 'disabled').addClass('disabled');
                } else {
                    toastr.error('Erro ao tentar integrar as informações da disciplina', '', {timeOut: 5000, progressBar: true});
                }
            });
        });

    </script>
@endsection
