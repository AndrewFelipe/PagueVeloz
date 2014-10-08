<?php

class PagueVeloz_Api_Model_Webservice_Assinar extends PagueVeloz_Api_Model_Webservice_PagueVeloz
{

    private $_default_header = 'Content-Type: application/json';

    public function __construct(PagueVeloz_Api_Model_Interfaces_IHttpClient $machine = null)
    {
        parent::__construct('api/v1/Assinar', $machine);
    }

    public function Post(PagueVeloz_Api_Model_Dto_AssinarDTO $dto)
    {
        $contexto = new PagueVeloz_Api_Model_Common_HttpContext();
        $contexto->setMethod('post');
        $contexto->addHeader($this->_default_header);
        $contexto->setHost($this->getHost());

        $json = '{
                    "Nome": "%s",
                    "Documento": "%s",
                    "TipoPessoa": "%s",
                    "Email": "%s",
                    "LoginUsuarioDefault": "%s"
             }';
        $json = sprintf($json, $dto->getNome(), $dto->getDocumento(), $dto->getTipoPessoa(), $dto->getEmail()->getEmail(), $dto->getLoginUsuarioDefault()->getEmail());

        $contexto->setBody($json);

        return $this->getMachine()->Send($contexto);
    }

}
