<?php 


class TempoEfetivoServico{

public $codfunc;
private $conn;

private $licencas ; 
private DateTime $dataInclusao;

private $diasInclusao;
private $diasLicencas;

private $diasEfetivoServico;

    public function getLicencas(){
        return $this->licencas;
    }

    public function getDiasInclusao(){
        return $this->diasInclusao;
    }
    public function getDiasLicencas(){
        return $this->diasLicencas;
    }
    public function getDiasEfetivoServico(){
        return $this->diasEfetivoServico;
    }

    public function __construct($codfunc){
        $this->codfunc = $codfunc;
        $this->conn = ConexaoPDO::conectar();

        $this->buscaLicencas();
        $this->calculaTempoLicencas();
        $this->buscaInclusao();
        $this->calculaDiasInclusao();
        $this->calculaEfetivoServico();
    }

    private function buscaLicencas(){

            $sql = "SELECT DATAINICIO, DATAFIM 
                    FROM RH_LICENCA 
                    WHERE CODFUNC= ? AND CODTIPOLICENCA=1" ; 
        
            $buscaLicencas = $this->conn->prepare($sql);
            $buscaLicencas->execute([$this->codfunc]);
            $this->licencas =  $buscaLicencas->fetchAll(ConexaoPDO::FETCH_OBJ);
       
    }

    private function calculaTempoLicencas(){
        $diasTotalLicenca = 0 ;

        if (count($this->licencas) > 0 ){
            foreach ($this->licencas as $licenca){
                $inicioLicenca = new DateTime($licenca->DATAINICIO);
                $fimLicenca = new DateTime($licenca->DATAFIM);
                $tempoLicenca = $inicioLicenca->diff($fimLicenca)->days;
                $diasTotalLicenca += $tempoLicenca;
            }    
        }
        $this->diasLicencas = $diasTotalLicenca ; 
    }

    private function buscaInclusao(){

            $sql = "SELECT *, C.SEXO, DATE_FORMAT(F.DATAINCLUSAO,'%d/%m/%X') AS DATAINC,
                    DATE_FORMAT(F.DATANASCIMENTO,'%d/%m/%Y') AS DATANASC,
                    DATE_FORMAT(F.DATAINCLUSAO,'%d/%m/%Y') AS DATADEINCLUSAO,
                    DATE_FORMAT(F.DATAINCLUSAO,'%Y-%m-%d') AS DATADEINCLUSAOCALCULO,
                    DATE_FORMAT(F.DATAEXPEDICAO,'%d/%m/%Y') AS DATAEXPED FROM FUNCIONARIO F, F_COMPLEMENTO C
                    WHERE F.CODFUNC = ? 
                    AND F.CODFUNC = C.CODFUNC";
            $inclusao = $this->conn->prepare($sql);
            $inclusao->execute([$this->codfunc]);
           $ob = $inclusao->fetch(ConexaoPDO::FETCH_OBJ);
            $this->dataInclusao = new DateTime($ob->DATADEINCLUSAOCALCULO) ; 
    }

    private function calculaDiasInclusao(){
        $hoje = new DateTime(date("Y-m-d"));
        $diasInclusao = $this->dataInclusao->diff($hoje)->days;
        $this->diasInclusao = $diasInclusao;
    }

    private function calculaEfetivoServico(){
        $this->diasEfetivoServico = $this->diasInclusao - $this->diasLicencas ; 
    }
        
 }
