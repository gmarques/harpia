@extends('layouts.modulos.integracaouema')

@section('title')
    Turmas
@stop

@section('subtitle')
    Integração dos turmas
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
