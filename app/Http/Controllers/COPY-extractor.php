<?php
	
	/**
	 * Abre/cria arquivo para armazenar o conteúdo
	 * @param  String $content Conteúdo para ser salva no arquivo
	 * @return int             Quantidade de bytes escritos, ou FALSE em caso de erro
	 */
	function save_file($content){
		$file_name = "nfce_content.html";
		$file 	   = fopen($file_name, 'w+');
		
		$write = fwrite($file, $content);
		fclose($file);

		return $write;
	}

	/**
	 * [get_content description]
	 * @param  String $key Chave de acesso da NFC-e
	 * @return String      Tabela com todas as informações da NFC-e
	 */
	function get_nfce_content($key){
		$link = "https://www.sefaz.rs.gov.br/ASP/AAE_ROOT/NFE/SAT-WEB-NFE-NFC_QRCODE_1.asp?chNFe=".$key;

		// Busca conteúdo do link
		$content = utf8_encode(file_get_contents($link));
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
	 * Extrai os dados do Estabelecimento a partir de objeto DOMElement 
	 * @param  DOMElement $table1 Objeto que contem a 1ª parte dos dados
	 * @param  DOMElement $table2 Objeto que contem a 2ª parte dos dados
	 * @return Array              Array com os dados do estabelecimento
	 */
	function get_company_data(DOMElement  $table1, DOMElement  $table2){
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
	function get_nfce_data(DOMElement $table){
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
	function get_products_data(DOMElement $table){
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
	 * Imprime na tela todos os dados da nota formatados
	 * @param  Array  $data [description]
	 * @return [type]       [description]
	 */
	function print_nfce_data(Array $data){
		foreach ($data as $key => $value) {
			echo "<h3>".strtoupper($key)."</h3>";
			
			foreach ($value as $k => $v) {
				if(!is_array($v))
					echo "<b>".$k.": </b>".$v."<br/>";
				else{
					echo "<h4>Produto ".$k."</h4>";
					foreach ($v as $index => $inf) {
						echo "<b> ".$index.": </b>".$inf."<br/>";
					}
					echo "<hr>";
				}
			}

			echo "<hr>";
		}
	}

	/**
	 * Script responsável por extrair os dados da nota
	 * @param  int  $_POST contendo a chave de acesso da nota com 44 dígitos
	 * @return void        imprime conjunto com todos os dados dos produtos e do estabelecimento
	 */
	function data_extract(){
		$key = $_POST["key"];
		$content = get_nfce_content($key);

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
		$data['estabelecimento'] = get_company_data($tables->item(1), $tables->item(2));

		# TABELA 3 - Cabeçalho sobre a nota (sem uso aparente)

		// TABELA 4 - DAdos da NFC-e: Número, Serie, Data, Chave e Protocolo
		$data['nfce'] = get_nfce_data($tables->item(4));

		# TABELA 5 - Consumidor geralmente não identificado (sem uso aparente)

		// TABELA 6 - Dados sobre os PRODUTOS
		$data['produtos'] = get_products_data($tables->item(6));

		# TABELA 7 - Valores totais da nota (sem uso aparente)
		// echo "<pre>"; print_r($data); echo "</pre>";
		print_nfce_data($data);
		return;
	}
?>