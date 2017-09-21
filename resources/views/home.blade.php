@extends('layout')

@section('content')
	<div class="jumbotron">
		<h1 class="display-6">Extração de dados da NFC-e</h1>
		<p class="lead">Nosso projeto está em desenvolvimento e precisamos de uma base de dados sólida para realização de testes. Nos ajude e cadastre suas notas através da chave de acesso.</p>
		<p><a class="btn btn-lg btn-success" href="/extractor" role="button">Cadastre uma NFC-e</a></p>
	</div>

	<div>
		<p>Este projeto está sendo desenvolvido para a disciplina de Projeto de Software II, no curso de Sistemas de Informação da Universidade Federal de Santa Maria. O propósito da aplicação é auxíliar na localização e comparação de ofertas de produtos em estabelecimentos que forneçam Nota Fiscal de Consumidor Eletrônica, a NFC-e.</p>
	</div>

	<div class="row marketing">
		<div class="col-lg-6">
			<h4> Dra. Andrea Charão</h4>
			<p>Orientadora</p>

			<h4>Franciel Krein</h4>
			<p>
				<a href="https://github.com/fkrein" target="_blank">github.com/fkrein</a>
			</p>

		</div>

		<div class="col-lg-6">
			<h4>Lucas Lima de Oliveira</h4>
			<p><a href="https://github.com/lucaslioli" target="_blank">github.com/lucaslioli</a></p>

			<h4>Luis Henrique Medeiros</h4>
			<p><a href="https://github.com/zillyy" target="_blank">github.com/zillyy</a></p>

		</div>
	</div>
@endsection