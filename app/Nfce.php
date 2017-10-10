<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DOMDocument;
use DOMElement;
use Exeption;

class Nfce extends Model
{
	protected $fillable = ['access_key'];

    /**
	 * Retorna todo conteúdo da página contendo a nota
	 * @param  String $key Chave de acesso da NFC-e
	 * @return String      Tabela com todas as informações da NFC-e
	 */
	public static function get_nfce_content($key)
	{
		$link = "https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-NFC_QRCODE_1.asp?chNFe=".$key;

		// Busca conteúdo do link
		$content = utf8_encode(file_get_contents($link));
		// Verifica se o link é válido
		if(strpos($content, "#FFFFEA") === FALSE)
			return FALSE;
		// Elimina os espapaços indesejados da string
		$content = trim(preg_replace('/\s+/', ' ', $content));
		// Seleciona apenas a parte onde o background é da cor #FFFFEA
		$content = explode("#FFFFEA", $content)[1];
		// Separa apenas a tabela que possui o conteúdo de interesse
		$content = strstr($content, "<table");
		$content = substr($content, 0, strpos($content, "Versão XSLT"));
		$content = substr($content, 0, strrpos($content, "<tr>"))."</table>";

		return $content;
	}

	/**
	 * Retorna todo conteúdo da página contendo as informações da nota em abas
	 * @param  String $key Chave de acesso da NFC-e
	 * @return String      Tabela com todas as informações da NFC-e
	 */
	public static function get_nfce_content_flaps($key)
	{
		$link = "https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-COM_2.asp?chaveNFe=".$key."&HML=false&NF=F082C5B49";

		// Busca conteúdo do link
		$content = utf8_encode(file_get_contents($link));
		// Verifica se o link é válido
		if(strpos($content, "chaveNFe") === FALSE)
			return FALSE;
		// Elimina os espapaços indesejados da string
		$content = trim(preg_replace('/\s+/', ' ', $content));
		// Seleciona apenas a parte do body que contem as abas
		$content = explode("</script><body>", $content)[1];
		// Separa apenas a parte que possui os dados, elemina os botões do final
		$content = substr($content, 0, strpos($content, "</body>"));

		return $content;
	}

	/**
	 * Extrai os dados do Estabelecimento a partir de objeto DOMElement 
	 * @param  DOMElement $table1 Objeto que contem a 1ª parte dos dados
	 * @param  DOMElement $table2 Objeto que contem a 2ª parte dos dados
	 * @return Array              Array com os dados do estabelecimento
	 */
	public static function get_company_data(DOMElement  $table1, DOMElement  $table2)
	{
		$content = array();
		// TABELA 1 - Dados do ESTABELECIMENTO: Nome, CNPJ, Inscrição Estadual
		$cols = $table1->getElementsByTagName('td');
		foreach ($cols as $col) {
			$class = $col->getAttribute("class");
			if($class == "NFCCabecalho_SubTitulo"){
				$content['nome'] = $col->nodeValue;

			}else if ($class == "NFCCabecalho_SubTitulo1") {
				// ex: " CNPJ: 00.000.000/0000-00 Inscrição Estadual: 0000000000"
				$data = explode(" ", $col->nodeValue);
				$content['cnpj'] = $data[2];
				$content['inscricao_estadual'] = $data[5];
			}
		}

		// TABELA 2 - Dados do ESTABELECIMENTO: Endereço
		$cols = $table2->getElementsByTagName('td');
		foreach ($cols as $col) {
			$class = $col->getAttribute("class");
			if ($class == "NFCCabecalho_SubTitulo1") {
				$content['endereco'] = $col->nodeValue;
			}
		}

		return $content;
	}

	/**
	 * Extrai os dados da NFC-e a partir de objeto DOMElement
	 * @param  DOMElement $table Objeto que contem a tabela com os dados
	 * @return Array             Array com os dados da NFC-e
	 */
	public static function get_nfce_data(DOMElement $table)
	{
		$content = array();
		// TABELA 4 - DAdos da NFC-e: Número, Serie, Data, Chave e Protocolo
		$cols = $table->getElementsByTagName('td');
		$flag = NULL;
		foreach ($cols as $col) {
			if(stripos($col->nodeValue, "nfc-e") !== FALSE){
				// ex: " NFC-e nº: 0000 Série: 000 Data de Emissão: dd/mm/yyyy h:i:s"
				$data = explode(" ", $col->nodeValue);
				$content['numero'] = $data[3];
				$content['serie'] = $data[5];
				$content['data_emissao'] = $data[9];
				$content['hora_emissao'] = $data[10];
			
			}else if(stripos($col->nodeValue, "protocolo") !== FALSE){
				// ex: " Protocolo de Autorização 0000000000000000"
				$data = explode(": ", $col->nodeValue);
				$content['protocolo'] = $data[1];
			
			}else if(stripos($col->nodeValue, "chave de acesso") !== FALSE){
				$flag = 1;
			
			}else if ($flag) {
				// ex: 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000
				$content["chave_acesso"] = $col->nodeValue;
				$flag++;
			}
		}
		return $content;
	}

	/**
	 * Extrai os dados dos Produtos a partir de objeto DOMElement
	 * @param  DOMElement $table Objeto que contem a tabela com todos os produtos
	 * @return Array             Array com os dados dos produtos
	 */
	public static function get_products_data(DOMElement $table)
	{
		$data = array();
		// TABELA 6 - Dados sobre os PRODUTOS
		$rows = $table->getElementsByTagName('tr');

		for ($i=1; $i < $rows->length; $i++) { 
			$cols = $rows->item($i)->getElementsByTagName('td');
			
			$data[$i]['codigo'] = $cols->item(0)->nodeValue;
			$data[$i]['descricao'] = $cols->item(1)->nodeValue;
			$data[$i]['valor'] = $cols->item(4)->nodeValue;
			$data[$i]['un'] = $cols->item(3)->nodeValue;
		}

		return $data;
	}

	/**
	 * Script responsável por extrair os dados da nota
	 * @param  int  $key       chave de acesso da nota com 44 dígitos
	 * @param  int  $just_show Opcional. Se for 1, não irá gravar no banco. Default: 0
	 * @return void            retorna array com todos os dados ou mensagem de erro
	 */
	public static function get_all_data($key, int $just_show = 0)
	{
		$content = self::get_nfce_content($key);

    	if($content == FALSE)
    		return 'Chave de Acesso inválida!';

		// Inicializa um objeto DOM
		$dom = new DOMDocument;
		// Descarta os espaços em branco
		$dom->preserveWhiteSpace = false;
		// Carrega o conteúdo como HTML
		$dom->loadHTML(utf8_decode($content));
		// Seleciona todos os elementos table
		$tables = $dom->getElementsByTagName('table');
		
		$data = array();

		// TABELA 1 e 2 - Dados do ESTABELECIMENTO: Nome, CNPJ, Inscrição Estadual e Endereço
		$data['estabelecimento'] = self::get_company_data($tables->item(1), $tables->item(2));

		# TABELA 3 - Cabeçalho sobre a nota (sem uso aparente)

		// TABELA 4 - DAdos da NFC-e: Número, Serie, Data, Chave e Protocolo
		$data['nfce'] = self::get_nfce_data($tables->item(4));

		# TABELA 5 - Consumidor geralmente não identificado (sem uso aparente)

		// TABELA 6 - Dados sobre os PRODUTOS
		$data['produtos'] = self::get_products_data($tables->item(6));

		# TABELA 7 - Valores totais da nota (sem uso aparente)
		
		if($just_show)
			return $data;
		
		try {
			Nfce::create(['access_key' => $key]);
			return $data;

		} catch (\Illuminate\Database\QueryException $e) {
			return "Chave de acesso já cadastrada!";
			// return $e->getMessage();
		}
	}
}
