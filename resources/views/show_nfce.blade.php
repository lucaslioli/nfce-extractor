@extends('layout')

@section('content')
	<?php 
	if(!is_array($data)){
		echo '<div class="alert alert-danger text-center" role="alert"><strong>Erro! </strong>'.$data.' <a href="/extractor">Informe outra</a></div>';
	} else {
		echo '<div class="text-center"><h1>DADOS DA NOTA</h1><hr></div>';
		foreach ($data as $key => $value) {
			echo "<h3>".strtoupper($key)."</h3>";

			if($key == "produtos")
				echo "<table class='table'>
						<tr>
							<th>#</th>
							<th>Código</th>
							<th>Descricao</th>
							<th>Valor</th>
							<th>UN</th>
						</tr>";
			
			foreach ($value as $k => $v) {
				if(!is_array($v))
					echo "<b>".$k.": </b>".$v."<br/>";
				else{
					echo "<tr><td>".$k."</td>";
					foreach ($v as $index => $inf) {
						echo "<td>".$inf."</td>";
					}
					echo "</tr>";
				}
			}

			if($key == "produtos")
				echo "</table>";

			echo "<hr>";
		}
		echo '<div class="text-center"><a class="btn btn-success" href="/extractor" role="button">Cadastrar outra NFC-e</a></div><br/>';
	}
	?>
@endsection