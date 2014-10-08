<?php

class PagueVeloz_Boleto_Model_Cron
{

    public static function verificaBoletoPago()
    {
        $boletosPago = array();
        $boletoMethod = Mage::getModel('pagueveloz_boleto/boletoMethod');
        $_diasVencimento = $boletoMethod->getConfig('vencimento');

        // @TODO Usar enum/constante para status de "pago"
        $_boletos = Mage::getModel('pagueveloz_boleto/boleto')->getCollection()
                ->addFieldToFilter('status', array('neq' => 'pago'));
        try {
            foreach ($_boletos as $_boletoData) {
                $dataDateTime = new DateTime($_boletoData->getDataVencimento());

                for ($i = 0; $i <= $_diasVencimento; $i++) {
                    $dataDateTime->modify("-1 day");
                    $data = $dataDateTime->format('Y-m-d'); // FORMAT LIKE > '2013-11-23'
                    if (!isset($boletosPago[$data])) {
                        $boletosPago[$data] = $boletoMethod->getBoletoPago($data);
                    }

                    if (isset($boletosPago[$data]->Message)) {
                        $boletoMethod->log($boletosPago[$data]->Message);
                        continue;
                    }
                }
            }

            foreach ($boletosPago as $_boletosData) {
                foreach ($_boletosData as $_boleto) {
                    $_order = Mage::getModel('sales/order')->loadByIncrementId($_boleto->SeuNumero);
                    foreach ($_boleto->Pagamentos as $_pagamento) {
                        $_order->setStatus($boletoMethod->getOrderStatus())
                            ->setState($boletoMethod->getOrderStatus())
                            ->addStatusHistoryComment("BOLETO PAGO EM: {$_pagamento->DataProcessamento} | {$_pagamento->Valor} R$")
                            ->save();

                        $_boletoData->setStatus('pago')
                            ->setValorPago($_pagamento->Valor)
                            ->setUpdatedTime(Mage::getSingleton('core/date')->gmtDate())
                            ->save();
                    }

                    $boletoMethod->log("[{$_order->getIncrementId()}] Boleto Pago | ID: " . $_boleto->Id . " | URL: " . $_boleto->Url);
                }
            }
        } catch (Exception $e) {
            $boletoMethod->log($e->getMessage());
        }

    }

    public function writeFile($msg)
    {
        $file = '/home/andre/workspace/magento/var/log/BOLETO_TESTES.txt';
        // Open the file to get existing content
        $current = file_get_contents($file);
        // Append a new person to the file
        $current .= $msg . "\n";
        // Write the contents back to the file
        file_put_contents($file, $current);
    }

}
