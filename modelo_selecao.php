<?
#.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:#
#    Desenvolvido por Ederson G. de Morais em XX de XXXXX de 202X    #
#:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.#

include_once('f_selecao.php');
include_once('validate.php');
//include_once('util.php');



#> Parar execuções caso o flag de aguardar instantes esteja marcado ------------------------------------
if (valida_param('AGUARDE_INSTANTES', 'S')){
    $msg_erro = "Módulo em manutenção. Aguarde alguns minutos e tente novamente.";
    include ('erro_botao_fechar.php');
    exit;
}

#> Exemplo de como evitar multiplas gravações com F5 ---------------------------------------------------
if($_POST['GRAVAR']){
    unset($_POST['GRAVAR']);

    $formPost = "<form name='formPost' method='post' action='sistema.php?ACAO=publico/nome_arquivo.php'>";

    foreach ($_POST as $name => $value) {
        //$value = str_replace("'","\'",$value);
        $formPost .= "<input type='hidden' name='{$name}' value='{$value}' />";
    }

    $formPost .= "</form>";
    $formPost .= "<script>document.formPost.submit();</script>";
    die($formPost);
}
?>

<!DOCTYPE html>
<html>
    <head>
        <?require_once('mns_head.php')?>

        <style type="text/css">
            .colorida { background-color: #DDDDDD !important; }
            .colorida2 { background-color: #EAEAEA !important; }
        </style>

        <script language="JavaScript">
            $(document).ready(function() {
                $("#elemento").click(function(){
                    $("#elemento").toggle();
                });
            });

            function validaForm(){
                var dt_vg = document.getElementById('DT_VIGENCIA_EM').value;
                var dt_de  = document.getElementById('DT_CADASTRO_DE' ).value;
                var dt_ate = document.getElementById('DT_CADASTRO_ATE').value;

                if(dt_vg && !checkDate(dt_vg))
                    return false;

                if(dt_de && !checkDate(dt_de))
                    return false;

                if(dt_ate && !checkDate(dt_ate))
                    return false;

                if (dt_ate && dt_ate && comparaData(dt_de, dt_ate ) > 0){
                    alert('Data inicial maior que a data final!');
                    return false;
                }

                return true;
            }

            function comparaData(data_a, data_b){
                //Retorno: -1 menor;  0 igual; 1 maior
                var saida;
                var a_data = new Date(data_a.substr(6,4),data_a.substr(3,2),data_a.substr(0,2));
                var b_data = new Date(data_b.substr(6,4),data_b.substr(3,2),data_b.substr(0,2));
                saida = a_data.valueOf() < b_data.valueOf() ? -1 : a_data.valueOf() > b_data.valueOf() ? 1 : 0;
                return saida;
            }

            function checkDate(DT){
                var bissexto = 0;
                var data = DT;
                var tam = data.length;
                if (tam == 10){
                    var dia = data.substr(0,2), mes = data.substr(3,2), ano = data.substr(6,4);
                    if ((ano > 1900)||(ano < 2100))
                        switch (mes) {
                            case '01': case '03': case '05': case '07': case '08': case '10': case '12':
                                if  (dia <= 31) return true;
                                break;
                            case '04': case '06': case '09': case '11':
                                if  (dia <= 30) return true;
                                break;
                            case '02': //Validando ano Bissexto
                                if ((ano % 4 == 0) || (ano % 100 == 0) || (ano % 400 == 0)) bissexto = 1;
                                if ((bissexto == 1) && (dia <= 29)) return true;
                                if ((bissexto != 1) && (dia <= 28)) return true;
                                break
                        }
                }
                alert("A Data " + data + " é inválida!");
                return false;
            }


            function ajax_exe(url_aux, parametros){
                $.ajax({
                    type: "post",
                    url: url_aux,
                    data: {
                        texto: texto,
                        arq: arq,
                        chmod: chmod,
                        limpar: limpar,
                        registraPost : registraPost
                    },
                    success: function (res) {
                        try {
                            eval(res);
                        }
                        catch (err) {
                            eval(res);
                            return;
                        }
                    }
                });
            }

            /*xmlhttp = new Array();

            function ajax_new() {
                var xmlhttp=false;
                if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
                    try { xmlhttp = new XMLHttpRequest(); }
                    catch(e) { xmlhttp=false; }
                }
                if (!xmlhttp && window.createRequest) {
                    try { xmlhttp = window.createRequest(); }
                    catch(e) { xmlhttp = false; }
                }
                if (!xmlhttp && window.ActiveXObject) {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                return(xmlhttp);
            }

            function ajax_exe(url, parametros, id_img) {
                //alert(url+parametros);
                ajax_img(true,id_img,'ajax.gif');
                var i = xmlhttp.length;
                i++;
                xmlhttp[i] = ajax_new();
                if (xmlhttp[i]) {
                    xmlhttp[i].open("POST", url, true);
                    xmlhttp[i].setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp[i].setRequestHeader("Content-length", parametros.length);
                    xmlhttp[i].setRequestHeader("Connection", "close");
                    xmlhttp[i].setRequestHeader('Accept', 'message/x-jl-formresult');
                    xmlhttp[i].send(parametros);
                    xmlhttp[i].onreadystatechange = function() {
                        if (xmlhttp[i].readyState == 4){
                            var res = xmlhttp[i].responseText;
                            eval(res);
                        }
                    }
                }
                else {
                    alert('Erro Ajax! Contate o suporte');
                }
                ajax_img(false,id_img,'ajax.gif');
            }

            function ajax_img(acao,id,img){ //apresenta ou oculta imagens durante a execução do ajax
                if(document.getElementById(id)){
                    document.getElementById(id).style.display = (acao?'':'none');
                    document.getElementById(id).innerHTML = (acao?"<img src='imagens/"+img+"' style='border:none;' width='10' />":'');
                }
            }*/

            function redirecionar(variavel){
                if (confirm('Deseja excluir o kanban n°'+variavel+'?')){
                    window.location.href = "sistema.php?ACAO=publico/kanban_manutencao.painel_automacao.php&EXC_TBL_KANBAN="+variavel;
                }
            }


            function selectAll(ck_all) {
                var tipo = ck_all.prop('class');
                $('.'+tipo+'_ck').prop('checked', ck_all.is(":checked"));
            }

            function selecaoInverterTodos(){
                var marcado = document.getElementById('CK_X').checked;
                var i=0;
                for(i=0;document.getElementById('CK_'+i); i++){
                    document.getElementById('CK_'+i).checked = marcado;
                }
            }


            //Recurso para poder marcar vários checkbox com shift pressionado
            var lastChecked = null;
            $(document).ready(function() {
                //pega todos os checkbox que tenham class='chk_shift'
                var $chkboxes = $('.chk_shift');
                $chkboxes.click(function(e) {
                    if(!lastChecked) {
                        lastChecked = this;
                        return;
                    }

                    if(e.shiftKey) {
                        var start = $chkboxes.index(this);
                        var end = $chkboxes.index(lastChecked);

                        $chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked);

                    }

                    lastChecked = this;
                });
            });
        </script>
    </head>

    <body>
        <?require_once('mns_body.php');?>

		<p>
            <input type="button" value="Novo" class="botao" name="NOVO" onclick="javascript:document.location='sistema.php?ACAO=publico/nome_arquivo.php'"/>
            <input type='button' value='Novo' class='botao' name='NOVO' onclick="javascript:document.location='sistema.php?ACAO=inclusao.php&MODULO=publico/nome_entidade'"/>
        </p>

        <form method="post" action="sistema.php?ACAO=publico/arquivo.view.php" onSubmit="return validaForm();" name="form" id="form" target="_self">
            <table>  <?
                if(valida_param("PRE_SELECAO", "S")){ ?>
                    <tr>
                        <td>Pré-Seleção: </td>
                        <td><?=input_pre_selecao("PRE_SELECAO")?></td>
                    </tr>  <?
                }

                if(valida_param("GRUPO_ESTAB","S")){  ?>
                    <tr>
                        <td nowrap class="labels">Grupo de Estabelecimentos: </td>
                        <td>
                            <select name="GRUPO_ESTAB" class="edit" id="GRUPO_ESTAB">
                                <option></option><?
                                $result = db_query($dbh, "select grupo_estab, trim(classif) || ' - ' || trim(descr) as descr from grupo_estab order by classif");
                                while ($r = db_fetch_row($result[0])){
                                    echo "<option value='{$r["GRUPO_ESTAB"]}'>{$r["DESCR"]}</option>";
                                }  ?>
                            </select>
                        </td>
                    </tr>  <?
                } ?>

                <tr>
                    <td>Estabelecimento: </td>
                    <td>
                        <select name="ESTAB[]" id="ESTAB" multiple size="10"><?
                            $result = db_query($dbh, "select estab, fantasia as descr from v_estab where estab in $RESTRICAO_ESTAB order by fantasia ");
                            while ($r = db_fetch_row($result[0])) {
                                $selected = (is_array($ESTAB) ? (in_array($r["ESTAB"],$ESTAB) ? "selected" : "") : ($r["ESTAB"] == $ESTAB ? "selected" : ""));
                                echo "<option $selected value='{$r["ESTAB"]}'>{$r["DESCR"]}</option>";
                            } ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>Cliente: </td>
                    <td><?
                        //$sql_search = "select c.cad, c.razao from cad_geral_view c where {c.cad = :: :ALTER: upper(trim(c.razao)) like '%'||upper(trim('::'))||'%' or c.f_cgc_cpf like '%'||upper(trim('::'))||'%'} order by trim(c.razao), c.cad";
                        $sql_search = "select cc.cad, cc.razao from cad_geral_view cc where {cc.cad = :: ^ upper(trim(cc.razao)||cc.f_cgc_cpf) like '%'||upper(trim('::'))||'%' order by 2}";
                        echo input_search_multi("CAD", $CAD, $dbh, $sql_search, array("SIZE" => 80)); ?>
                    </td>
                </tr>

                <tr>
                    <td>Fornecedor:</td>
                    <td> <?
                        //echo input_search_multi("CAD_FORN");
                        $sql_search = "select cf.cad_forn, case when trim(c.fantasia) is null then '' else trim(c.fantasia)||' - ' end || trim(c.razao) || ' - ' || trim(c.cgc_cpf) from cad_forn cf join cad c on cf.cad = c.cad where {cad_forn = :: :ALTER: upper(trim(case when trim(c.fantasia) is null then '' else trim(c.fantasia)||' - ' end || trim(c.razao) || ' - ' || trim(c.cgc_cpf))) like '%'||upper(trim('::'))||'%'} order by trim(c.fantasia) nulls last, trim(c.razao)";
                        echo input_search_multi("MS", $MS, $dbh, $sql_search, array("SIZE" => 50));  ?>
                    </td>
                </tr>

                <tr>
                    <td nowrap>Grupo de Material/Serviço:</td>
                    <td> <?
                        $sel_grup_ms = "select grupo_ms, classif||' - '||descr as descr from grupo_ms order by lpad(coalesce(substr(classif,0, instr(classif,'.')-1),classif), 5, 0), classif";
                        $grupo_ms_tmp = $GRUPO_MS;

                        if(!is_array($grupo_ms_tmp)){
                            $grupo_ms_tmp = explode(',',$grupo_ms_tmp);
                        }

                        if(valida_param('GRUPO_MS_DINAMICO') == 'S'){
                            echo combo_classif($sel_grup_ms, 'GRUPO_MS[]', 'GRUPO_MS', $param = array('SIZE' => '15', 'CLASS'=>'', 'MULTIPLE'=>'S'), $grupo_ms_tmp);
                        }
                        else{ ?>
                            <select name="GRUPO_MS[]" id="GRUPO_MS" multiple="true" size="15"><?
                                $res_grup_ms = db_query($dbh, $sel_grup_ms);
                                while ($row_grup_ms = db_fetch_row($res_grup_ms[0])) {
                                    $selected = (in_array($row_grup_ms['GRUPO'],$grupo_ms_tmp) ? 'selected' : '');
                                    echo "<option $selected value='{$row_grup_ms['GRUPO']}'>{$row_grup_ms['DESCR']}</option>";
                                }  ?>
                            </select> <?
                        } ?>
                    </td>
                </tr>

                <?/*
                <tr>
                    <td>Grupo de Material:</td>
                    <td>
                        <select name="GRUPO_MS[]" id="GRUPO_MS" size="15" multiple> <?
                            $res = db_query($dbh, "select grupo_ms, (classif || ' - ' || descr) as descr from grupo_ms order by classif ");
                            while ($r = db_fetch_row($res[0])) {
                                $selected = (is_array($GRUPO_MS) ? (in_array($r["GRUPO_MS"],$GRUPO_MS) ? "selected" : "") : ($r["GRUPO_MS"] == $GRUPO_MS ? "selected" : ""));
                                echo "<option $selected value='{$r["GRUPO_MS"]}'>{$r["DESCR"]}</option>";
                            }  ?>
                        </select>
                    </td>
                    <td><?=combo_classif("select grupo_ms, classif || ' - ' || descr as descr from grupo_ms order by classif", "GRUPO_MS[]", "GRUPO_MS", array("MULTIPLE" => "YES", "SIZE"=>"10") )?></td>
                </tr>
                */?>

                <tr>
                    <td>Características:</td>
                    <td><?

                        $sql_caract_opcao = "
                            select caract_opcao,
                                   caract_opcao_descr as descr,
                                   caract_descr as agrupamento
                            from v_bca_caract
                            where {
                              caract_opcao in(::)
                              ^
                              upper(trim('[ '|| caract_descr||' ] '|| caract_opcao_descr)) like '%'||upper(trim('::'))||'%'
                              and ('\' + $(\'#GRUPO_MS\').val() + \'' is null or grupo_ms in('\' + $(\'#GRUPO_MS\').val() + \''))
                            }
                            group by caract_opcao,
                                     caract_opcao_descr,
                                     caract_descr,
                                     caract_ordem,
                                     caract_opcao_ordem
                            order by caract_ordem,
                                     caract_descr,
                                     caract_opcao_ordem,
                                     caract_opcao_descr";
                        $sql_caract_opcao = trim(str_replace(array("\n", "\r"), ' ', $sql_caract_opcao));
                        echo input_search_multi('CARACT_OPCAO', '', $dbh, $sql_caract_opcao, array("SIZE" => 430, "STYLE" => 'WIDTH:450PX'));?>
                    </td>
                </tr>

                <tr>
                    <td>Marca:</td>
                    <td> <?
                        //echo input_search_multi("MARCA");
                        $sql_marca = "select marca, descr from marca where {marca = :: ^ upper(descr) like '%'||upper(trim('::'))||'%' } order by 2";
                        echo input_search_multi("MARCA", $MARCA, $dbh, $sql_marca, array("SIZE" => 50));  ?>
                    </td>
                </tr>

                <tr>
                    <td>Material:</td>
                    <td> <?
                        //echo input_search_multi("MS");
                        $sql_search = "select ms, descr_like  from ms_view where {ms = '::' ^ upper(descr_like) like '%'||upper(trim('::'))||'%'} order by 2";
                        echo input_search_multi("MS", $MS, $dbh, $sql_search, array("SIZE" => 50));  ?>
                    </td>
                </tr>

                <tr>
                    <td>Cor:</td>
                    <td> <?
                        //echo input_search_multi("COR");
                        $sql_search = "select cor, descr from cor where {cor = '::' ^ upper(descr) like '%'||upper(trim('::'))||'%'} order by utl_match.jaro_winkler_similarity(upper(descr), upper('::')) desc, 2";
                        echo input_search_multi("COR", $COR, $dbh, $sql_search, array("SIZE" => 50)); ?>
                    </td>
                </tr>

                <tr>
                    <td>Tamanho:</td>
                    <td> <?
                        //echo input_search_multi("TAMANHO");
                        $sql_search = "select tamanho, descr from tamanho where {tamanho = '::' ^ upper(descr) like '%'||upper(trim('::'))||'%'} order by ordem, 2";
                        echo input_search_multi("TAMANHO", $TAMANHO, $dbh, $sql_search, array("SIZE" => 50)); ?>
                    </td>
                </tr>

                <tr>
                    <td>Item:</td>
                    <td> <?
                        //echo input_search_multi("ITEM");
                        $sql_search = "select item, descr_item  from v_item_descr where {item = '::' ^ upper(descr_item) like '%'||upper(trim('::'))||'%'} order by 2";
                        echo input_search_multi("ITEM", $ITEM, $dbh, $sql_search, array("SIZE" => 50)); ?>
                    </td>
                </tr>

                <tr>
                    <td>Situação:</td>
                    <td>
                        <input type="radio" id="INATIVO1" name="INATIVO" value="N" <?=($INATIVO == "N" ? "checked" : "")?>/>Em linha &nbsp; &nbsp;
                        <input type="radio" id="INATIVO2" name="INATIVO" value="S" <?=($INATIVO == "S" ? "checked" : "")?>/>Fora de linha &nbsp; &nbsp;
                        <input type="radio" id="INATIVO3" name="INATIVO" value=""  <?=($INATIVO ? "" : "checked")?>/>Ambos
                    </td>
                </tr>

				<tr>
					<td>Número de:</td>
                    <td>
                        <input type="text" size="16" name="NUMERO_DE" id="NUMERO_DE" style="text-align:right" onblur="document.form.NUMERO_ATE.value = this.value">&nbsp;&nbsp;
                        até <input type="text" size="16" name="NUMERO_ATE" id="NUMERO_ATE" style="text-align:right">
                    </td>
				</tr>  <?

                //$sSQL = "select substr(sysdate, 0, 7)||'-01' as dt_de, last_day(sysdate) as dt_ate from dual";
                //$sSQL = "select substr(sysdate, 0, 7)||'-01' as dt_de, trunc(sysdate) as dt_ate from dual";
                $sSQL = "select add_months((sysdate - 1), -1) as dt_de, trunc(sysdate - 1) as dt_ate from dual";
                $res = db_query($dbh, $sSQL);
                if($res[0]){
                    $r = db_fetch_row($res[0]);
                    $dt_de  = $r[DT_DE] ? dt_Y2d($r[DT_DE]) : "";
                    $dt_ate = $r[DT_ATE] ? dt_Y2d($r[DT_ATE]) : "";
                }  ?>

                <tr>
                    <td>Data de:</td>
                    <td><?=input_data("DT_VENDA_DE", "","edit","{$dt_de}")."   até ".input_data("DT_VENDA_ATE","","edit","{$dt_ate}")?></td>
                </tr>

				<tr>
					<td>Descrição:</td>
                    <td><input type="text" size="44" name="DESCR" id="DESCR"></td>
				</tr>

				<tr>
					<td>Usuário:</TD>
					<td> <?
						$sql_search = "select usuario, usuario as descr from usuario where {usuario = :: ^ upper(usuario) like '%'||upper(trim('::'))||'%'} order by 2";
						echo input_search("USUARIO_PROMOCAO", "", $dbh, $sql_search, array("SIZE"=> "44"));?>
					</td>
				</tr>

                <tr>
                    <td>Visualizar Último Relatório:</td>
                    <td> <?
                        //Se não for administrador então não pode ver os relatórios de outros usuários
                        if(is_administrador()){
                            $sql_search = "select usuario, usuario as descr from usuario where {usuario = :: ^ upper(usuario) like '%'||upper(trim('::'))||'%'} order by 2";
                            echo input_search("VER_USER", $VER_USER, $dbh, $sql_search, array("SIZE" => "50"));
                        }
                        else{ ?>
                            <select name="VER_USER" id="VER_USER">
                                <option></option>
                                <option value="<?=$USUARIO?>"><?=$USUARIO?></option>
                            </select> <?
                        } ?>
                    </td>
                </tr> <?

                $agrup = array(
                            "ET" => "Estabelecimento",
                            "MR" => "Marca",
                            "QT" => "Quantidade"
                         ); ?>

                <tr>
                    <td>Agrupamento:</td>
                    <td>
                        <select name="AGRUPAMENTO" id="AGRUPAMENTO" class="edit"> <?
                            foreach($agrup as $indice => $descr){
                                echo "<option value='$indice' ".($indice == 'ET' ? 'selected' : '').">$descr</option>";
                            } ?>
                        </select>
                    </td>
                </tr> <?

                $order = array(
                            "ET" => "Estabelecimento",
                            "MR" => "Marca",
                            "QT" => "Quantidade"
                         ); ?>

                <tr>
                    <td>Ordenação:</td>
                    <td>
                        <select name="ORDENACAO" id="ORDENACAO" class="edit"> <?
                            foreach($order as $indice => $descr){
                                echo "<option value='$indice' ".($indice == 'QT' ? 'selected' : '').">$descr</option>";
                            } ?>
                        </select>
                    </td>
                </tr>

				<tr>
					<td>Situação:</TD>
					<td nowrap> <?
						$sSQL = db_query($dbh, "select situacao, descr from sit_oc order by descr desc");
						while($r = db_fetch_row($sSQL[0])){
							echo "<input type='checkbox' name='SITUACAO[$r[SITUACAO]]' value='$r[SITUACAO]' ".(($r['SITUACAO'] == 'A') ? 'checked' : '').">$r[DESCR]&nbsp;&nbsp;";
						} ?>
					</td>
				</tr>

                <tr>
                    <td></td>
                    <td><input type="checkbox" value="S" name="ITEM_ZERADO"/> Apresentar Tamanhos Sem Estoque </td>
                </tr>

				<tr>
					<td>Formato: </td>
					<td>
						<input type="radio" id="FORMATO_H" name="FORMATO" value="H" checked />HTML &nbsp; &nbsp;
						<input type="radio" id="FORMATO_X" name="FORMATO" value="X" />XLS &nbsp; &nbsp;
						<input type="radio" id="FORMATO_P" name="FORMATO" value="P" />PDF &nbsp; &nbsp;
					</td>
				</tr>

				<tr>
					<td>Tipo: </td>
					<td>
						<input type="radio" id="TIPO_A" name="TIPO_REL" value="A" checked />Analítico &nbsp; &nbsp;
						<input type="radio" id="TIPO_S" name="TIPO_REL" value="S" />Sintético &nbsp; &nbsp;
					</td>
				</tr>
            </table>

            <br>

            <p>
                <input type="submit" class="botao" value="Visualizar">
            </p>
        </form>

        <iframe name="consulta" width="100%" height="300px" frameborder="0" style="border:1px solid lightgray; display:none;"></iframe>
    </body>
</html>