<?php

/*
| Define a estrutura de dados e os métodos para um Funcionário.
*/
class Funcionario {
    private $nome;
    private $cpf;
    private $dataNascimento;
    private $cargo;
    private $salarioBase;
    private $numeroDependentes;
    private $emailCorporativo;
    private $codigoIbgeCidadeTrabalho;

    // --- Getters ---
    public function getNome() { return $this->nome; }
    public function getCpf() { return $this->cpf; }
    public function getDataNascimento() { return $this->dataNascimento; }
    public function getCargo() { return $this->cargo; }
    public function getSalarioBase() { return $this->salarioBase; }
    public function getNumeroDependentes() { return $this->numeroDependentes; }
    public function getEmailCorporativo() { return $this->emailCorporativo; }
    public function getCodigoIbgeCidadeTrabalho() { return $this->codigoIbgeCidadeTrabalho; }

    // --- Setters ---
    public function setNome($v) { $this->nome = $v; }
    public function setCpf($v) { $this->cpf = $v; }
    public function setDataNascimento($v) { $this->dataNascimento = $v; }
    public function setCargo($v) { $this->cargo = $v; }
    public function setSalarioBase($v) { $this->salarioBase = floatval($v); }
    public function setNumeroDependentes($v) { $this->numeroDependentes = intval($v); }
    public function setEmailCorporativo($v) { $this->emailCorporativo = $v; }
    public function setCodigoIbgeCidadeTrabalho($v) { $this->codigoIbgeCidadeTrabalho = $v; }

    public function calculaBeneficio() {
        if ($this->numeroDependentes <= 2) {
            return $this->salarioBase * 0.10;
        } else {
            return $this->salarioBase * 0.20;
        }
    }
}

/*
| Funções reutilizáveis para cálculos e consultas a serviços externos.
*/
function calcularIdade($dataNasc) {
    if (empty($dataNasc)) return null;
    try {
        $nasc = new DateTime($dataNasc);
        $hoje = new DateTime();
        $diff = $hoje->diff($nasc);
        return $diff->y;
    } catch (Exception $e) {
        return null;
    }
}


function buscarMunicipioIBGE($codigo) {
    $codigo = trim($codigo);
    if ($codigo === '') return null;

    $url = "https://servicodados.ibge.gov.br/api/v1/localidades/municipios/" . urlencode($codigo);
    $json = @file_get_contents($url);

    if ($json === false) {
        return null;
    }

    $dados = json_decode($json, true);
    if (!is_array($dados)) return null;

    $municipio = isset($dados['nome']) ? $dados['nome'] : null;
    $ufSigla = isset($dados['microrregiao']['mesorregiao']['UF']['sigla']) ? $dados['microrregiao']['mesorregiao']['UF']['sigla'] : null;
    $ufNome = isset($dados['microrregiao']['mesorregiao']['UF']['nome']) ? $dados['microrregiao']['mesorregiao']['UF']['nome'] : null;

    if ($municipio) {
        return [
            'municipio' => $municipio,
            'uf_sigla' => $ufSigla,
            'uf_nome' => $ufNome
        ];
    }
    return null;
}

function old($name) {
    return isset($_POST[$name]) ? htmlspecialchars($_POST[$name]) : '';
}

/*
| Lógica que executa quando o formulário é enviado (método POST).
*/

$func = null;
$idade = null;
$municipioIBGE = null;
$beneficio = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $salario_base = $_POST['salario_base'] ?? '';
    $numero_dependentes = $_POST['numero_dependentes'] ?? '';
    $email_corporativo = $_POST['email_corporativo'] ?? '';
    $codigo_ibge = $_POST['codigo_ibge'] ?? '';

    $func = new Funcionario();
    $func->setNome($nome);
    $func->setCpf($cpf);
    $func->setDataNascimento($data_nascimento);
    $func->setCargo($cargo);
    $func->setSalarioBase($salario_base);
    $func->setNumeroDependentes($numero_dependentes);
    $func->setEmailCorporativo($email_corporativo);
    $func->setCodigoIbgeCidadeTrabalho($codigo_ibge);

    $idade = calcularIdade($func->getDataNascimento());
    $municipioIBGE = buscarMunicipioIBGE($func->getCodigoIbgeCidadeTrabalho());
    $beneficio = $func->calculaBeneficio();
}

/*
| Código HTML e CSS para a interface do usuário.
*/
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Funcionário</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border: 1px solid #ddd; }
        h1 { font-size: 20px; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn { margin-top: 15px; padding: 10px 15px; cursor: pointer; }
        .resultado { margin-top: 25px; padding: 15px; background: #eef; border: 1px solid #99c; }
        .erro { color: #b00; }
        small { color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastro de Funcionário</h1>

        <form method="post">
            <label>Nome:
                <input 
                type="text" 
                name="nome" 
                value="<?php echo old('nome'); ?>" required>
            </label>
            <div class="row">
                <div>
                    <label>CPF:
                        <input 
                        type="text" 
                        name="cpf" 
                        placeholder="somente números" 
                        value="<?php echo old('cpf'); ?>" required>
                    </label>
                </div>


                <div>
                    <label>Data de Nascimento:
                        <input 
                        type="date" 
                        name="data_nascimento" 
                        value="<?php echo old('data_nascimento'); ?>" required>
                    </label>
                </div>
            </div>


            <label>Cargo:
                <input 
                type="text" 
                name="cargo" 
                value="<?php echo old('cargo'); ?>" required>
            </label>


            <div class="row">
                <div>
                    <label>Salário Base:
                        <input 
                        type="number" 
                        step="0.01" 
                        name="salario_base" 
                        value="<?php echo old('salario_base'); ?>" required>
                    </label>
                </div>
                <div>


                    <label>Nº de Dependentes:
                        <input 
                        type="number" 
                        name="numero_dependentes" 
                        value="<?php echo old('numero_dependentes'); ?>" required>
                    </label>
                </div>
            </div>

            
            <label>E-mail Corporativo:
                <input 
                type="email" 
                name="email_corporativo" 
                value="<?php echo old('email_corporativo'); ?>" required>
            </label>


            <label>Código IBGE da cidade de trabalho:
                <input 
                type="text" 
                name="codigo_ibge" 
                placeholder="ex: 3550308 (São Paulo)" 
                value="<?php echo old('codigo_ibge'); ?>" required>
            </label>


            <button 
            class="btn" 
            type="submit">Cadastrar</button>
        </form>

        <?php if ($func !== null): ?>
            <div class="resultado">
                <h2>Detalhes do Funcionário</h2>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($func->getNome()); ?></p>
                <p><strong>CPF:</strong> <?php echo htmlspecialchars($func->getCpf()); ?></p>
                <p><strong>Data de Nascimento:</strong> <?php echo htmlspecialchars($func->getDataNascimento()); ?></p>
                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($func->getCargo()); ?></p>
                <p><strong>Salário Base:</strong> R$ <?php echo number_format($func->getSalarioBase(), 2, ',', '.'); ?></p>
                <p><strong>Nº Dependentes:</strong> <?php echo htmlspecialchars($func->getNumeroDependentes()); ?></p>
                <p><strong>E-mail Corporativo:</strong> <?php echo htmlspecialchars($func->getEmailCorporativo()); ?></p>
                <p><strong>Código IBGE:</strong> <?php echo htmlspecialchars($func->getCodigoIbgeCidadeTrabalho()); ?></p>
                
                <hr>

                <h3>Informações Calculadas</h3>
                <p>
                    <strong>Idade (anos):</strong>
                    <?php echo ($idade !== null) ? intval($idade) : '<span class="erro">Data inválida</span>'; ?>
                </p>
                <p>
                    <strong>Município/UF pela API do IBGE:</strong><br>
                    <?php
                        if ($municipioIBGE) {
                            $mun = htmlspecialchars($municipioIBGE['municipio']);
                            $ufSigla = htmlspecialchars($municipioIBGE['uf_sigla']);
                            $ufNome = htmlspecialchars($municipioIBGE['uf_nome']);
                            echo "$mun - $ufSigla ($ufNome)";
                        } else {
                            echo '<span class="erro">Não encontrado. Verifique o código IBGE informado.</span>';
                        }
                    ?>
                </p>
                <p>
                    <strong>Benefício (segundo nº de dependentes):</strong>
                    R$ <?php echo number_format($beneficio, 2, ',', '.'); ?>
                </p>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>