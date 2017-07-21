<?php

namespace Modulos\IntegracaoUema\Http\Requests;

use Modulos\Core\Http\Request\BaseRequest;

class IntegrarMatriculasTurmaRequest extends BaseRequest
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
            'trm_id' => 'required'
        ];
    }
}
