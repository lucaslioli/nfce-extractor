<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DOMDocument, DOMElement;
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
	 * Igual a get_nfce_content() porém, extrai os dados mais completos
	 * @param  String $key Chave de acesso da NFC-e
	 * @return String      Tabela com todas as informações da NFC-e
	 */
	public static function get_nfce_content_tabs($key)
	{
		$link = "https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-COM_2.asp?chaveNFe=".$key."&HML=false&NF=F082C5B49";

		// $link = "localpathfortests";

		// Busca conteúdo do link
		$content = utf8_encode(file_get_contents($link));
		// Verifica se o link é válido
		if(strpos($content, "chaveNFe") === FALSE)
			return FALSE;
		// Elimina os espapaços indesejados da string
		$content = trim(preg_replace('/\s+/', ' ', $content));
		// Seleciona apenas a parte do body que contem as abas (outra opção é '</script><body>')
		$content = explode("</b></li></ul>", $content)[1];
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
	 * Igual a get_company_data() porém, extrai os dados mais completos
	 * @param  DOMElement $div Objeto que contem os dados
	 * @return Array           Array com os dados do estabelecimento
	 */
	public static function get_company_data_tabs(DOMElement  $div)
	{
		$content = array();
		// Dados do ESTABELECIMENTO: Nome, CNPJ, Inscrição Estadual
		$cols = $div->getElementsByTagName('td');
		foreach ($cols as $col) {
			$label = $col->getElementsByTagName('label')->item(0);

			if($label){
				// $label->nodeValue = utf8_decode($label->nodeValue);

				if(stripos($label->nodeValue, "Razão Social") !== FALSE){
					$content['nome'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "CNPJ") !== FALSE){
					$content['cnpj'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Endereço") !== FALSE){
					$content['endereco'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Bairro") !== FALSE){
					$content['bairro'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "CEP") !== FALSE){
					$content['cep'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Município") !== FALSE && !isset($content['cidade'])){
					$cidade = $col->getElementsByTagName('span')->item(0)->nodeValue;
					$cidade = explode('- ', $cidade);
					if(isset($cidade[1])){
						$content['cidade'] = $cidade[1];
					}else{
						$content['cidade'] = $cidade;
					}
					
				
				}else if(stripos($label->nodeValue, "UF") !== FALSE){
					$content['uf'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				}
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
	 * Igual a get_nfce_data() porém, extrai os dados mais completos
	 * @param  DOMElement $div Objeto que contem a tabela com os dados
	 * @return Array           Array com os dados da NFC-e
	 */
	public static function get_nfce_data_tabs(DOMElement $div)
	{
		$content = array();
		// Dados da NFC-e: Número, Serie, Data e Protocolo
		$cols = $div->getElementsByTagName('td');
		foreach ($cols as $col) {
			$label = $col->getElementsByTagName('label')->item(0);
			
			if($label){
				// $label->nodeValue = utf8_decode($label->nodeValue);

				if(stripos($label->nodeValue, "Série") !== FALSE){
					$content['serie'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Número") !== FALSE){
					$content['numero'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
				
				}else if(stripos($label->nodeValue, "Data de Emissão") !== FALSE){
					// ex: " dd/mm/yyyy h:i:s"
					$data = explode(" ", $col->getElementsByTagName('span')->item(0)->nodeValue);
					$content['data_emissao'] = $data[0];
					$content['hora_emissao'] = $data[1];
				
				}else if(stripos($label->nodeValue, "Protocolo") !== FALSE){
					$input = $div->getElementsByTagName('input')->item(0);
					$content['protocolo'] = $input->getAttribute('value');
				}
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
	 * Extrai os dados dos Produtos a partir de objeto DOMElement
	 * @param  DOMElement $div Objeto que contem a tabela com todos os produtos
	 * @return Array           Array com os dados dos produtos
	 */
	public static function get_products_data_tabs(DOMElement $div)
	{
		$data = array();
		$tables = $div->getElementsByTagName('table');
		
		/**
		 * Cada produto na nota possui 2 tabelas principais (e internas) de dados dentro da div "aba_nft_3";
		 * Na aba de produtos, o cabeçalho seria a tabela 0, ao buscar pela lista de tabelas, por isso $i=1;
		 * A lista de tabelas retorna também as tabelas filhas, o que permitiria a replicação de dados;
		 * Cada uma das 2 tabelas principais possui a classe "toggle" ou "toggable";
		 * Essas classes são verificadas, assim é possível pegar todas as TDs internas com segurança;
		 */
		
		$c = 0;
		for ($i=1; $i < $tables->length; $i++) {
			$classe = $tables->item($i)->getAttribute("class");

			if(stripos($classe, "toggle") !== FALSE){
				$cols = $tables->item($i)->getElementsByTagName('td');
				
				foreach ($cols as $col) {
					if($col->getAttribute("class")=="fixo-prod-serv-descricao"){
						$data[$c]['descricao'] = $col->nodeValue;
					}else if ($col->getAttribute("class")=="fixo-prod-serv-uc"){
						$data[$c]['un'] = $col->nodeValue;
					}
				}

			}else if(stripos($classe, "toggable") !== FALSE){
				$cols = $tables->item($i)->getElementsByTagName('td');

				foreach ($cols as $col) {
					$label = $col->getElementsByTagName('label')->item(0);
					
					if($label){
						// $label->nodeValue = utf8_decode($label->nodeValue);
		
						if(stripos($label->nodeValue, "Código do Produto") !== FALSE){
							$data[$c]['codigo'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
						
						}else if(stripos($label->nodeValue, "Código NCM") !== FALSE){
							$data[$c]['ncm'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
						
						}else if(stripos($label->nodeValue, "Código EAN Comercial") !== FALSE){
							$data[$c]['ean'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
						
						}else if(stripos($label->nodeValue, "Valor unitário de comercialização") !== FALSE){
							$data[$c]['valor'] = $col->getElementsByTagName('span')->item(0)->nodeValue;
						}
					}
				}
				$c++;
			}
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

	/**
	 * Igual a get_all_data() porém, extrai os dados mais completos
	 * @param  int  $key       chave de acesso da nota com 44 dígitos
	 * @param  int  $just_show Opcional. Se for 1, não irá gravar no banco. Default: 0
	 * @return void            retorna array com todos os dados ou mensagem de erro
	 */
	public static function get_all_data_tabs($key, int $just_show = 0)
	{
		$content = self::get_nfce_content_tabs($key);

    	if($content == FALSE)
    		return 'Chave de Acesso inválida!';

		// Inicializa um objeto DOM
		$dom = new DOMDocument;
		// Descarta os espaços em branco
		$dom->preserveWhiteSpace = false;
		// Carrega o conteúdo como HTML (Utilizado o @ devido a erro interno da lib)
		@$dom->loadHTML(utf8_decode($content));

		// Seleciona todos os elementos table
		$elements = $dom->getElementsByTagName('div');
		
		$data = array();
		
		// Dados da NF-e - div aba_nft_0
		$data['nfce'] = self::get_nfce_data_tabs($elements->item(1));
		$data['nfce']['chave_acesso'] = $key;

		// Dados da Empresa - div aba_nft_1
		$data['estabelecimento'] = self::get_company_data_tabs($elements->item(3));

		// Dados da Empresa - div aba_nft_3
		$data['produtos'] = self::get_products_data_tabs($elements->item(17));
		
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
