@extends('layout')

@section('script')
	<!-- Extractor JS -->
	<script type="text/javascript" src="js/extractor.js"></script>
@endsection

@section('content')
	<form action="/data_extract" method="post" id="form_key">
		{{ csrf_field() }}
		<h2 class="form-signin-heading">Extração de dados da NFC-E</h2>
		<div class="form-group">
			<input type="text" name="key" class="form-control" id="access_key" placeholder="Chave de Acesso (ex.: 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000)"  maxlength="44" />
		</div>
		<button type="submit" class="btn btn-outline-primary btn-block">Cadastrar</button>
	</form>
	<div>
		<br/>
		<h5>Onde encontrar a Chave de Acesso</h5>
		<p>Número de 44 digitos, encontrado geralmente na parte superior ou inferior da	Nota, próximo aos dados do consumidor e junto dos demai dados referentes a própria NFC-e.</p>
		<div class="text-center">
			<img src="example-nfce.jpg" width="500px">
		</div>
		<br/>
	</div>
	<!-- Mensagem de Alerta -->
	<div class="form-group" id="div_mensagem" style="display: none">
		<div id="mensagem" class="text-center">
		</div>
	</div>
@endsection