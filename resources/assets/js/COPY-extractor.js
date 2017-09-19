/**
 * Exibe mensagem de erro ou sucesso
 */
function mensagem(tipo, msg, nome_objeto){
	$("#" + nome_objeto).html("");
	if(tipo == "erro"){
		$("#" + nome_objeto).append('<br/><div class="alert alert-danger alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Erro!</strong> ' + msg + '</div>');
	}else if(tipo == "sucesso"){
		$("#" + nome_objeto).append('<br/><div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' + msg + '</div>');
	}else $("#" + nome_objeto).html(msg);
}

/**
 * Valida input para aceitar apenas números
 */
$(document).on("keyup","#access_key", function(e){
	$(this).val(this.value.replace(/\D/g, ''));
});

/**
 * Quando é realizado submit no formulário, aqui ele é tratado e enviado por ajax
 */
$(document).on("submit","#form_key", function(e){
	e.preventDefault();

	$("#div_mensagem").hide();

	if($("#access_key").val().length!=44){
		mensagem("erro", "Valor informado não corresponde a chave de acesso. <b>Verifique se contém 44 dígitos</b>.", "mensagem");
		$("#div_mensagem").show();
		return;
	}

	$("#loading").show();

	$.ajax({
		type: "POST",
		url: "extractor.php",
		data: {key: $("#access_key").val()},
		success: function(data){
			// mensagem(json['tipo'], json['msg'], "mensagem");
			$("#div_resultado").html(data);
		},
		error: function(data){
			mensagem("erro", "Ocorreu um erro ao solicitar a extração dos dados.", "mensagem");
			$("#div_mensagem").show();
		}
	})
	$("#loading").hide();
});