@extends('layout')

@section('content')
	<div class="col-md-12">
	    <form action="" method="post" id="form_key">
	        <h2 class="form-signin-heading">Extração de dados da NFC-E</h2>
	        <div class="form-group">
	            <input type="text" name="key" class="form-control" id="access_key" placeholder="Chave de Acesso (ex.: 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000)" />
	        </div>
	        <button type="submit" class="btn btn-outline-primary btn-block">Cadastrar</button>
	    </form>
	    <!-- GIF Loading -->
	    <div class="col-md-offset-3 col-md-6 text-center" id="loading" hidden="true">
	        <img src="https://d2x5ku95bkycr3.cloudfront.net/img/loading.gif">
	    </div>
	    <!-- Mensagem de Alerta -->
	    <div class="form-group" id="div_mensagem">
	        <div class="col-md-offset-3 col-md-6 text-center">
	            <div id="mensagem"></div>
	        </div>
	    </div>
	</div>
	<!-- Resultado -->
	<div class="col-md-12">
	    <br/>
	    <div class="form-group col-md-offset-3 col-md-6" id="div_resultado"></div>
	</div>
@endsection