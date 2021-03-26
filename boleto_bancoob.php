<?php

Error_reporting(0);

// ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc)	//

// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 7;
$taxa_boleto = 0;
$data_venc = $_POST['data_venc']; //date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
$valor_cobrado = $_POST['valor_cobrado']; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".", $valor_cobrado);
$valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

//$dadosboleto["nosso_numero"] = "08123456";  // Até 8 digitos, sendo os 2 primeiros o ano atual (Ex.: 08 se for 2008)


/*************************************************************************
 * +++
 *************************************************************************/

// http://www.bancoob.com.br/atendimentocobranca/CAS/2_Implanta%C3%A7%C3%A3o_do_Servi%C3%A7o/Sistema_Proprio/DigitoVerificador.htm
// http://blog.inhosting.com.br/calculo-do-nosso-numero-no-boleto-bancoob-sicoob-do-boletophp/
// http://www.samuca.eti.br
// 
// http://www.bancoob.com.br/atendimentocobranca/CAS/2_Implanta%C3%A7%C3%A3o_do_Servi%C3%A7o/Sistema_Proprio/LinhaDigitavelCodicodeBarras.htm

// Contribuição de script por:
// 
// Samuel de L. Hantschel
// Site: www.samuca.eti.br
// 

if (!function_exists('formata_numdoc')) {
	function formata_numdoc($num, $tamanho)
	{
		while (strlen($num) < $tamanho) {
			$num = "0" . $num;
		}
		return $num;
	}
}

$IdDoSeuSistemaAutoIncremento = '10003'; // Deve informar um numero sequencial a ser passada a função abaixo, Até 6 dígitos
$agencia = "4364"; // Num da agencia, sem digito
$conta = "494"; // Num da conta, sem digito
$convenio = "7641"; //Número do convênio indicado no frontend

$NossoNumero = formata_numdoc($IdDoSeuSistemaAutoIncremento, 7);
$qtde_nosso_numero = strlen($NossoNumero);
$sequencia = formata_numdoc($agencia, 4) . formata_numdoc(str_replace("-", "", $convenio), 10) . formata_numdoc($NossoNumero, 7);
$cont = 0;

$calculoDv = '';
for ($num = 1; $num <= strlen($sequencia); $num++) {
	$cont++;
	if ($cont == 1) {
		$constante = 3;
	}
	if ($cont == 2) {
		$constante = 1;
	}
	if ($cont == 3) {
		$constante = 9;
	}
	if ($cont == 4) {
		$constante = 7;
		$cont = 0;
	}

	$calculoDv = $calculoDv + (substr($sequencia, $num, 1) * $constante);
}

$Resto = $calculoDv % 11;

if ($Resto == 0 || $Resto == 1) {
	$Dv = 0;
} else {
	$Dv = 11 - $Resto;
}


$dadosboleto["nosso_numero"] = $NossoNumero . $Dv;

/*************************************************************************
 * +++
 *************************************************************************/



$dadosboleto["numero_documento"] = "0";	// Num do pedido ou do documento
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $_POST['sacado'];
$dadosboleto["endereco1"] = $_POST['endereco1'];
$dadosboleto["endereco2"] = $_POST['endereco2'];

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Custeio mensal Conf.CCT - ref. 01/2021";
//$dadosboleto["demonstrativo2"] = "Apos vencimento cobrar juros de - R$ ".number_format($taxa_boleto, 2, ',', '');
//$dadosboleto["demonstrativo3"] = "BoletoPhp - http://www.boletophp.com.br";

// INSTRUÇÕES PARA O CAIXA
$dadosboleto["instrucoes1"] = "Apos vencimento cobrar juros de 2% + 0,33% ao dia";
//$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
//$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: xxxx@xxxx.com.br";
$dadosboleto["instrucoes4"] = "Sinduscon DF / STICOMBE DF";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "N";
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "DM";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
// DADOS ESPECIFICOS DO SICOOB
$dadosboleto["modalidade_cobranca"] = "01";
$dadosboleto["numero_parcela"] = "001";


// DADOS DA SUA CONTA - BANCO SICOOB
$dadosboleto["agencia"] = $agencia; // Num da agencia, sem digito
$dadosboleto["conta"] = $conta; // Num da conta, sem digito

// DADOS PERSONALIZADOS - SICOOB
$dadosboleto["convenio"] = $convenio; // Num do convênio - REGRA: No máximo 7 dígitos
$dadosboleto["carteira"] = "1";

// SEUS DADOS
$dadosboleto["identificacao"] = "";
$dadosboleto["cpf_cnpj"] = "03.656.261/0001-52";
$dadosboleto["endereco"] = "";
$dadosboleto["cidade_uf"] = "";
$dadosboleto["cedente"] = "SERVICO SOCIAL DA INDUSTRIA DA CONSTRUCAO CIVIL DO DISTRITO FEDERAL";

// NÃO ALTERAR!
include("include/funcoes_bancoob.php");
include("include/layout_bancoob.php");

?>

<html>
<head>
<meta charset="utf-8">
</head>
<form method="POST" action="gerar_pdf.php">
<button name="gerar_pdf">Gerar</button>
</form>
</html>

