<?php

namespace Modulos\IntegracaoUema\Http\Requests;

use Modulos\Core\Http\Request\BaseRequest;

class IntegrarOfertaRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ofd_id' => 'required',
            'codigo_prog' => 'required',
            'ito_disciplina_prog' => 'required'
        ];
    }
}
