<?php

namespace Modulos\Academico\Repositories;

use Modulos\Academico\Models\Certificado;
use Modulos\Academico\Models\Diploma;
use Modulos\Academico\Models\Livro;
use Modulos\Academico\Models\Registro;
use Modulos\Core\Repository\BaseRepository;
use Auth;
use Ramsey\Uuid\Uuid;

class RegistroRepository extends BaseRepository
{
    protected $livroRepository;

    const FOLHAS_LIVRO = 200;
    const REGISTROS_FOLHA = 3;

    public function __construct(Registro $registro)
    {
        $this->livroRepository = new LivroRepository(new Livro());
        parent::__construct($registro);
    }

    public function create(array $data)
    {
        try {
            if (isset($data['tipo_livro']) && $data['tipo_livro'] == 'CERTIFICADO') {
                return $this->certificar($data);
            }

            return $this->diplomar($data);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }
        }
    }

    private function certificar(array $data)
    {
        $ultimoLivro = $this->ultimoLivro();

        // Se null, nao ha livros de certificacao ainda ou se nao ha vagas no livro, criar um novo
        if (!$ultimoLivro || !$this->livroTemRegistroDisponiveis($ultimoLivro)) {
            $numero = 1;

            if (!is_null($ultimoLivro)) {
                $numero = $ultimoLivro->liv_numero;
                $numero++;
            }

            $this->livroRepository->create([
                'liv_numero' => $numero,
                'liv_tipo_livro' => 'DIPLOMA'
            ]);

            $ultimoLivro = $this->ultimoLivro();
        }

        // Adicionar ao livro
        $uuid = Uuid::uuid4();

        $registro = parent::create([
            'reg_liv_id' => $ultimoLivro->liv_id,
            'reg_usr_id' => Auth::user()->usr_id,
            'reg_folha' => $this->folhaParaNovoRegistro($ultimoLivro),
            'reg_registro' => $this->numeroParaNovoRegistro($ultimoLivro),
            'reg_codigo_autenticidade' => $uuid->toString()
        ]);

        $certificado = new Certificado();
        $certificado->crt_reg_id = $registro->reg_id;
        $certificado->crt_mat_id = $data['matricula'];
        $certificado->crt_mdo_id = $data['modulo'];
        $certificado->save();

        return $registro;
    }

    private function diplomar(array $data)
    {
        $ultimoLivro = $this->ultimoLivro('DIPLOMA');

        // Se null, nao ha livros de certificacao ainda ou se nao ha vagas no livro, criar um novo
        if (!$ultimoLivro || !$this->livroTemRegistroDisponiveis($ultimoLivro)) {
            $numero = 1;

            if (!is_null($ultimoLivro)) {
                $numero = $ultimoLivro->liv_numero;
                $numero++;
            }

            $this->livroRepository->create([
                'liv_numero' => $numero,
                'liv_tipo_livro' => 'DIPLOMA'
            ]);

            $ultimoLivro = $this->ultimoLivro();
        }

        // Adicionar ao livro
        $uuid = Uuid::uuid4();

        $registro = parent::create([
            'reg_liv_id' => $ultimoLivro->liv_id,
            'reg_usr_id' => Auth::user()->usr_id,
            'reg_folha' => $this->folhaParaNovoRegistro($ultimoLivro),
            'reg_registro' => $this->numeroParaNovoRegistro($ultimoLivro),
            'reg_codigo_autenticidade' => $uuid->toString()
        ]);

        // Diploma
        $diploma = new Diploma();
        $diploma->dip_reg_id = $registro->reg_id;
        $diploma->dip_mat_id = $data['matricula'];
        $diploma->dip_processo = $data['processo'];
        $diploma->dip_codigo_autenticidade_externo = $data['codigo_externo'];
        $diploma->save();

        return $registro;
    }

    /**
     * @param string $tipo CERTIFICADO || DIPLOMA
     * @return mixed
     */
    private function ultimoLivro($tipo = 'CERTIFICADO')
    {
        $result = $this->livroRepository->findBy([
            'liv_tipo_livro' => $tipo
        ]);

        return $result->last();
    }

    private function livroTemRegistroDisponiveis(Livro $livro)
    {
        $registros = $this->findBy([
            'reg_liv_id' => $livro->liv_id
        ]);

        if ($registros->count() == RegistroRepository::FOLHAS_LIVRO * RegistroRepository::REGISTROS_FOLHA) {
            return false;
        }

        return true;
    }

    private function folhaParaNovoRegistro(Livro $livro)
    {
        if ($this->livroTemRegistroDisponiveis($livro)) {
            $ultimoRegistro = $this->ultimoRegistroLivro($livro);

            // Livro ainda nao tem registros
            if (!$ultimoRegistro) {
                return 1;
            }

            // Pula para proxima folha
            if ($ultimoRegistro->reg_registro == RegistroRepository::REGISTROS_FOLHA) {
                return (int) ($ultimoRegistro->reg_folha + 1);
            }

            // O novo registro fica na mesma folha
            return (int) ($ultimoRegistro->reg_folha);
        }

        return 0;
    }

    private function numeroParaNovoRegistro(Livro $livro)
    {
        if ($this->livroTemRegistroDisponiveis($livro)) {
            $ultimoRegistro = $this->ultimoRegistroLivro($livro);

            // Livro ainda nao tem registros
            if (!$ultimoRegistro) {
                return 1;
            }

            // Pula para proxima folha, iniciando  contagem de registros da folha
            if ($ultimoRegistro->reg_registro == RegistroRepository::REGISTROS_FOLHA) {
                return 1;
            }

            // Incrementa
            return (int) ($ultimoRegistro->reg_registro + 1);
        }

        return 0;
    }

    private function ultimoRegistroLivro(Livro $livro)
    {
        $registros = $this->findBy([
            'reg_liv_id' => $livro->liv_id
        ]);

        return $registros->last();
    }


    /**
     * @param $matriculaId
     * @param $moduloId
     * @return bool
     */
    public function matriculaTemRegistro($matriculaId, $moduloId)
    {
        $result = $this->model
            ->join('acd_certificados', 'reg_id', '=', 'crt_reg_id')
            ->where('crt_mat_id', '=', $matriculaId)
            ->where('crt_mdo_id', '=', $moduloId)
            ->get();

        if ($result->count()) {
            return true;
        }

        return false;
    }

    /**
     * @param $options
     * @return mixed
     */
    public function findBy($options)
    {
        $query = $this->model;

        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $query = $query->where($key, '=', $value);
            }

            return $query->get();
        }

        return $query->all();
    }
}
