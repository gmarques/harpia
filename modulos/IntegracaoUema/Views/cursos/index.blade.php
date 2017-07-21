@extends('layouts.modulos.integracaouema')

@section('title')
    Cursos
@stop

@section('subtitle')
    Integração dos cursos
@stop

@section('content')
    @if(!is_null($tabela))
        <div class="box box-primary">
            <div class="box-body">
                {!! $tabela->render() !!}
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
        $(".fc-cod-prog").blur(function(e){
            var linhaSelecionada = $(e.currentTarget).closest('tr');
            var codCurso = e.currentTarget.value;

            if (!codCurso) {
                toastr.error('O código do curso é obrigatório', '', {timeOut: 5000, progressBar: true});

                return;
            }

            $.harpia.httpget('{{url("/")}}/integracaouema/async/cursos/' + codCurso).done(function (response) {
                if(!$.isEmptyObject(response)) {
                    linhaSelecionada.find('.fc-nome-curso-prog').val(response);
                    linhaSelecionada.find('.btn-integrar').removeAttr('disabled').removeClass('disabled');
                } else {
                    toastr.error('Curso não localizado', '', {timeOut: 5000, progressBar: true});

                    linhaSelecionada.find('.fc-nome-curso-prog').val('');
                    linhaSelecionada.find('.btn-integrar').attr('disabled', 'disabled').addClass('disabled');
                }
            });
        });

        $(".btn-integrar").click(function(e) {
            e.preventDefault();

            var token = "{{ csrf_token() }}";
            var linhaSelecionada = $(e.currentTarget).closest('tr');
            var currentTarget = $(e.currentTarget);

            var crs_id = currentTarget.data('crs_id');
            var codigo_prog = linhaSelecionada.find('.fc-cod-prog').val();
            var nome_curso = linhaSelecionada.find('.fc-nome-curso-prog').val();

            if (!crs_id || !codigo_prog || !nome_curso) {
                toastr.error('O código do curso é obrigatório', '', {timeOut: 5000, progressBar: true});

                return;
            }

            var data = {
                crs_id: crs_id,
                codigo_prog: codigo_prog,
                nome_curso: nome_curso,
                _token: token
            };

            $.harpia.httppost('{{url("/")}}/integracaouema/async/cursos/integrar', data).done(function (response) {
                if(!$.isEmptyObject(response)) {
                    toastr.success('Informações integradas com sucesso', '', {timeOut: 5000, progressBar: true});

                    currentTarget.data('crs_id', response.result);
                    currentTarget.attr('disabled', 'disabled').addClass('disabled');
                } else {
                    toastr.error('Erro ao tentar integrar as informações do curso', '', {timeOut: 5000, progressBar: true});
                }
            });
        });

    </script>
@endsection
