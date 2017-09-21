/**
 * Exibe mensagem de erro ou sucesso
 */
function mensagem(tipo, msg, nome_objeto){
	$("#" + nome_objeto).html("");
	if(tipo == "erro"){
		$("#" + nome_objeto).append('<br/><div class="alert alert-danger alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ msg +'</div>');
	}else if(tipo == "sucesso"){
		$("#" + nome_objeto).append('<br/><div class="alert alert-success alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ msg +'</div>');
	}else $("#" + nome_objeto).html(msg);
}

/**
 * Valida input para aceitar apenas números
 */
$(document).on("keyup","#access_key", function(e){
	$(this).val(this.value.replace(/\D/g, ''));
});

$(document).on("change","#access_key", function(e){
	if($("#access_key").val().length!=44){
		mensagem("erro", "Valor informado não possui <b>44 dígitos</b> e não corresponde a chave de acesso.", "mensagem");
		$("#div_mensagem").show();
		return false;
	}else
		$("#div_mensagem").hide();
});