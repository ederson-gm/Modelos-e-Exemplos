<?
#.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:#
#    Desenvolvido por Ederson G. de Morais em XX de XXXXX de 202X    #
#:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.:.#

//include_once('validate.php');
//include_once('util.php');
include_once('f_selecao.php');


#> Parar execuções caso o flag de aguardar instantes esteja marcado ------------------------------------
if (valida_param('AGUARDE_INSTANTES', 'S')){
    $msg_erro = "Módulo em manutenção. Aguarde alguns minutos e tente novamente.";
    include ('erro_botao_fechar.php');
    exit;
}


#> TRATAMENTO DOS FILTROS =================================================================================================================================================================

if ($PRE_SELECAO) {
    $aSEL[] = "<b>Pré Seleção:</b> " . selecao_valor($dbh, "select pre_selecao, trim(classif)||' - '||trim(descr) as descr from pre_selecao where pre_selecao = $PRE_SELECAO", 'DESCR');
    //$aWHERE[] = " f_pre_selecao(user, $PRE_SELECAO, '$MODULO') like '%:'||pre_selecao||':%' ";
}

#----------------------------------
if ($GRUPO_ESTAB) {
    $GRUPO_ESTAB = is_array($GRUPO_ESTAB) ? implode(',', $GRUPO_ESTAB) : $GRUPO_ESTAB;
    $aSEL[]      = "<b>Grupo de Estabelecimento:</b> " . selecao_valores($dbh, "select grupo_estab, classif || ' - ' || descr as grupo_estab_descr from grupo_estab where grupo_estab in ($GRUPO_ESTAB) order by classif ", 'GRUPO_ESTAB_DESCR');
    $aWHERE[]    = "e.grupo_estab in (select ge2.grupo_estab
                                      from grupo_estab ge1
                                      join grupo_estab ge2 on ge2.classif like ge1.classif || '%'
                                      where ge1.grupo_estab in ($GRUPO_ESTAB)
                                      group by ge2.grupo_estab)";
    #Buscar os grupos filhos;
    $tmp_ge      = selecao_valores($dbh, "select f_tbl2str(cursor(select ge2.grupo_estab from grupo_estab ge1 join grupo_estab ge2 on ge2.classif like ge1.classif || '%' where ge1.grupo_estab in ($GRUPO_ESTAB) group by ge2.grupo_estab),',') as grupo from dual","GRUPO");
    $aWHERE[]    = "e.grupo_estab in($tmp_ge)";
}

#----------------------------------
$ESTAB = ($ESTAB ? $ESTAB : selecao_valores($dbh, "select estab from estab where estab in $RESTRICAO_ESTAB and (select count(*) from estab where estab not in $RESTRICAO_ESTAB) > 0", 'ESTAB')); //Necessário aparecer no filtro a descrição do estabelecimento caso o usuário tenha restrição de estabelecimento. Isso é preciso senão dá a entender que o saldo é de todos os estabelecimentos.
if ($ESTAB) {
    $ESTAB    = is_array($ESTAB) ? implode(',', $ESTAB) : $ESTAB;
    $aSEL[]   = "<b>Estabelecimento:</b> " . selecao_valores($dbh, "select estab, fantasia as estab_descr from estab where estab in ($ESTAB) order by razao ", 'ESTAB_DESCR');
    $aWHERE[] = "e.estab in($ESTAB)";
}

#----------------------------------
$CAD = (is_array($CAD) ? array_filter($CAD) : $CAD); //remover elementos nulos que podem ser retornados pela input_search_multi
$CAD = (empty($CAD) ? null : $CAD);
if ($CAD) {
    $CAD = (is_array($CAD) ? implode(',', $CAD) : $CAD);
    $aSEL[]   = "<b>Cliente:</b> " . selecao_valores($dbh, "select razao from cad_geral_view where cad in($CAD)", 'RAZAO');
    $aWHERE[] = "c.cad in($CAD)";
}

#----------------------------------
if ($GRUPO_MS) {
    $GRUPO_MS = is_array($GRUPO_MS) ? implode(',', $GRUPO_MS) : $GRUPO_MS;
    $aSEL[]   = "<b>Grupo de Materiais:</b> " . selecao_valores($dbh, "select grupo_ms, classif || ' - ' || descr as grupo_ms_descr from grupo_ms where grupo_ms in ($GRUPO_MS) order by classif ", 'GRUPO_MS_DESCR');
    $aWHERE[] = "ms.grupo_ms in (select gm2.grupo_ms
                                 from grupo_ms gm1
                                 join grupo_ms gm2 on gm2.classif like gm1.classif || '%'
                                 where gm1.grupo_ms in ($GRUPO_MS)
                                 group by gm2.grupo_ms)";
    #Buscar os grupos filhos;
    $tmp_gm   = selecao_valores($dbh, "select f_tbl2str(cursor(select gm2.grupo_ms from grupo_ms gm1 join grupo_ms gm2 on gm2.classif like gm1.classif || '%' where gm1.grupo_ms in ($GRUPO_MS) group by gm2.grupo_ms),',') as grupo from dual","GRUPO");
    $aWHERE[] = "ms.grupo_ms in($tmp_gm)";
}

#----------------------------------
if ($ESTACAO_MS) {
    $ESTACAO_MS = is_array($ESTACAO_MS) ? implode(',', $ESTACAO_MS) : $ESTACAO_MS;
    $aSEL[] = "<b>Estação:</b> ".selecao_valores($dbh, "select descr from estacao_ms where estacao_ms in($ESTACAO_MS) order by descr", 'DESCR');
    $aWHERE[] = "vid.estacao_ms = '$ESTACAO_MS'";
}

#----------------------------------
$MARCA = (is_array($MARCA) ? array_filter($MARCA) : $MARCA); //remover elementos nulos que podem ser retornados pela input_search_multi
$MARCA = (empty($MARCA) ? null : $MARCA);
if ($MARCA) {
    $MARCA = (is_array($MARCA) ? implode(',', $MARCA) : $MARCA);
    $aSEL[]   = "<b>Marca:</b> " . selecao_valores($dbh, "select descr from marca where marca in($MARCA)", 'DESCR');
    $aWHERE[] = "vid.marca in($MARCA)";
}

#----------------------------------
$MS = (is_array($MS) ? array_filter($MS) : $MS); //remover elementos nulos que podem ser retornados pela input_search_multi
$MS = (empty($MS) ? null : $MS);
if ($MS) {
    $MS = (is_array($MS) ? implode(',', $MS) : $MS);
    $aSEL[]   = "<b>Material:</b> " . selecao_valores($dbh, "select descr from ms_view where ms in($MS) order by descr", 'DESCR');
    $aWHERE[] = "vid.ms in($MS)";
}

#----------------------------------
$COR = (is_array($COR) ? array_filter($COR) : $COR); //remover elementos nulos que podem ser retornados pela input_search_multi
$COR = (empty($COR) ? null : $COR);
if ($COR) {
    $COR = (is_array($COR) ? implode(',', $COR) : $COR);
    $aSEL[]   = "<b>Cor:</b> " . selecao_valores($dbh, "select descr from cor where cor in($COR) order by descr", 'DESCR');
    $aWHERE[] = "vid.cor in($COR)";
}

#----------------------------------
$TAMANHO = (is_array($TAMANHO) ? array_filter($TAMANHO) : $TAMANHO); //remover elementos nulos que podem ser retornados pela input_search_multi
$TAMANHO = (empty($TAMANHO) ? null : $TAMANHO);
if ($TAMANHO) {
    $TAMANHO = (is_array($TAMANHO) ? implode(',', $TAMANHO) : $TAMANHO);
    $aSEL[]   = "<b>Tamanho:</b> " . selecao_valores($dbh, "select descr from tamanho where tamanho in($TAMANHO) order by ordem, descr", 'DESCR');
    $aWHERE[] = "vid.tamanho in($TAMANHO)";
}

#----------------------------------
$ITEM = (is_array($ITEM) ? array_filter($ITEM) : $ITEM); //remover elementos nulos que podem ser retornados pela input_search_multi
$ITEM = (empty($ITEM) ? null : $ITEM);
if ($ITEM) {
    $ITEM = (is_array($ITEM) ? implode(',', $ITEM) : $ITEM);
    $aSEL[]   = "<b>Item:</b> " . selecao_valores($dbh, "select descr_item as descr from v_item_descr where item in($ITEM) order by descr", 'DESCR');
    $aWHERE[] = "vid.item in($ITEM)";
}

#----------------------------------
if ($DT_DE || $DT_ATE) {
    $aSEL[] = selecao_de_ate('<b>Data: </b>', $DT_DE, $DT_ATE);
    if ($DT_DE) {
        $DT_DE_temp = dt_d2Y($DT_DE);
        $aWHERE [] = " data >= '$DT_DE_temp' ";
    }
    if ($DT_ATE) {
        $DT_ATE_temp = dt_d2Y($DT_ATE);
        $aWHERE [] = " data <= '$DT_ATE_temp' ";
    }
}

#----------------------------------
if($NUMERO_DE || $NUMERO_ATE){
    $aSEL[] = selecao_de_ate("<b>Número:</b>", $NUMERO_DE, $NUMERO_ATE);
    /*if($NUMERO_DE){
    }
    if($NUMERO_ATE){
    }*/
}

#----------------------------------
if ($DT_CADASTRO_DE || $DT_CADASTRO_ATE) {
    $aSEL[] = selecao_de_ate('<b>Data de Cadastro: </b>', $DT_CADASTRO_DE, $DT_CADASTRO_ATE);
    if ($DT_CADASTRO_DE) {
        $DT_CADASTRO_DE_temp = dt_d2Y($DT_CADASTRO_DE);
        $aWHERE [] = " dt_cadastro >= '$DT_CADASTRO_DE_temp' ";
    }
    if ($DT_CADASTRO_ATE) {
        $DT_CADASTRO_ATE_temp = dt_d2Y($DT_CADASTRO_ATE);
        $aWHERE [] = " dt_cadastro <= '$DT_CADASTRO_ATE_temp' ";
    }
}

#----------------------------------
if($DT_VIGENCIA_EM){
    $aSEL[] = "<b>Vigente em: </b> {$DT_VIGENCIA_EM}";
    $dt_vigencia_em_tmp = dt_d2Y($DT_VIGENCIA_EM);
    $aWHERE[] = "'$dt_vigencia_em_tmp' between trunc(dt_de) and trunc(dt_ate)";
}

#----------------------------------
if($DESCR){
    $aSEL[] = "<b>Descrição:</b> $DESCR";
}

#----------------------------------
if($USUARIO_FILTRO){
    $aSEL[] = "<b>Usuário:</b> $USUARIO_FILTRO";
    $aWHERE[] = "usuario = '{$USUARIO_FILTRO}'";
}

#----------------------------------
if ($SITUACAO) {
    $SITUACAO = ":".(is_array($SITUACAO) ? implode(":", $SITUACAO) : $SITUACAO).":";
    $aSEL[]   = "<b>Situação:</b> " . selecao_valores($dbh, "select situacao, descr from sit_pre_promo where '$SITUACAO' like '%:'||situacao||':%' order by descr desc ", 'DESCR');
    //$aWHERE1[] = "situacao in($SITUACAO)";
}

#----------------------------------
if($INATIVO == 'N'){   // N->ativos / S->inativos / ''->ambos
    $aSEL[] = "<b>Situação:</b> Em Linha";
} elseif($INATIVO == 'S'){
    $aSEL[] = "<b>Situação:</b> Fora de Linha";
} elseif(!$INATIVO) {
    $aSEL[] = "<b>Situação:</b> Ambos(Em Linha e Fora de Linha)";
}

#----------------------------------
$agrup = array(
    "GE" => array("DESCR"=>"Grupo de Estabelecimentos", "CAMPO_VLR"=>"ge.grupo_estab",  "CAMPO_DESCR"=>"ge.classif||' - '||ge.descr",                   "CAMPO_ORDEM"=>""),
    "ET" => array("DESCR"=>"Estabelecimento",           "CAMPO_VLR"=>"e.estab",         "CAMPO_DESCR"=>"e.fantasia",                                    "CAMPO_ORDEM"=>""),
    "GM" => array("DESCR"=>"Grupo de Material",         "CAMPO_VLR"=>"gm.grupo_ms",     "CAMPO_DESCR"=>"gm.classif||'-'||gm.descr",                     "CAMPO_ORDEM"=>""),
    "EM" => array("DESCR"=>"Estação",                   "CAMPO_VLR"=>"vid.estacao_ms",  "CAMPO_DESCR"=>"vid.estacao_ms_descr",                          "CAMPO_ORDEM"=>""),
    "MR" => array("DESCR"=>"Marca",                     "CAMPO_VLR"=>"vid.marca",       "CAMPO_DESCR"=>"vid.marca_descr",                               "CAMPO_ORDEM"=>""),
    "MS" => array("DESCR"=>"Material",                  "CAMPO_VLR"=>"vm.ms",           "CAMPO_DESCR"=>"vm.descr_padrao",                               "CAMPO_ORDEM"=>""),
    "IT" => array("DESCR"=>"Item",                      "CAMPO_VLR"=>"vid.item",        "CAMPO_DESCR"=>"vid.descr_item",                                "CAMPO_ORDEM"=>""),
    "CR" => array("DESCR"=>"Cor",                       "CAMPO_VLR"=>"vid.cor",         "CAMPO_DESCR"=>"vid.cor_descr",                                 "CAMPO_ORDEM"=>""),
    "TM" => array("DESCR"=>"Tamanho",                   "CAMPO_VLR"=>"vid.tamanho",     "CAMPO_DESCR"=>"vid.tam_descr",                                 "CAMPO_ORDEM"=>"vid.tam_ordem"),
    "DT" => array("DESCR"=>"Data",                      "CAMPO_VLR"=>"t.dt_cadastro",   "CAMPO_DESCR"=>"to_char(t.dt_cadastro,'DD/MM/YYYY HH24:MI Dy', 'nls_language =\"BRAZILIAN PORTUGUESE\"')",   "CAMPO_ORDEM"=>"to_char(t.dt_cadastro,'YYYY-MM-DD HH24:MI:SS)', 'nls_language =\"BRAZILIAN PORTUGUESE\"')"),
    "ST" => array("DESCR"=>"Situação",                  "CAMPO_VLR"=>"vid.inativo",     "CAMPO_DESCR"=>"case when vid.inativo = 'N' then 'Em Linha' else 'Fora de linha' end", "CAMPO_ORDEM"=>"")
);

#----------------------------------
if($AGRUPAMENTO){
    $aSEL[] = "<b>Agrupado por:</b> {$agrup[$AGRUPAMENTO]['DESCR']}";
}
else{
    $AGRUPAMENTO = "ET";
}
$agrup[$AGRUPAMENTO]['CAMPO_ORDEM'] = ($agrup[$AGRUPAMENTO]['CAMPO_ORDEM'] ? $agrup[$AGRUPAMENTO]['CAMPO_ORDEM'] : $agrup[$AGRUPAMENTO]['CAMPO_DESCR']);

#----------------------------------
if($ORDENACAO){
    $aSEL[] = "<b>Ordenado por:</b> {$agrup[$ORDENACAO]['DESCR']}";
}
else{
    $ORDENACAO = "DT";
}
$agrup[$ORDENACAO]['CAMPO_ORDEM'] = ($agrup[$ORDENACAO]['CAMPO_ORDEM'] ? $agrup[$ORDENACAO]['CAMPO_ORDEM'] : $agrup[$ORDENACAO]['CAMPO_DESCR']);

#----------------------------------
if(is_array($aSEL)){
    $sSEL = implode('; ', $aSEL).'.';
}
if(is_array($aWHERE)){
    $sWHERE = 'where '.implode(' and ', $aWHERE);
}

#======================================================================================================================
if($KEY){
    //exemplo update
    $sSQL = "update tabela set campo = 'S' where pk_tabela = '$KEY'";
    $res = db_query($dbh, $sSQL);

    //exemplo update com clob
    $sSQL = "update tabela set campo_clob = empty_clob() where pk_tabela = :key returning campo_clob into :lob";
    $clob = oci_new_descriptor($dbh, OCI_D_LOB);
    $stmt = oci_parse($dbh, $sSQL);
    oci_bind_by_name($stmt, ':key', $KEY);
    oci_bind_by_name($stmt, ":lob", &$clob, -1, OCI_B_CLOB);
    if($res = oci_execute($stmt, OCI_DEFAULT)){
        if(is_object($clob)){
            $res[0] = $texto->save($texto_clob);
        }
    }
    $clob->free();
    oci_free_statement($stmt);


    if ($res[0]) {
        db_commit($dbh);
        echo "<script>
                 alert('Dados gravados com sucesso!');
                 window.location.assign('sistema.php?ACAO=publico/nome_arquivo.selecao.php');
              </script>
        ";
        exit;
    } else {
        db_rollback($dbh);
        $msg_erro = db_error($php_errormsg, $sSQL);
        $msg_erro = (trim($msg_erro) ? trim($msg_erro) : 'Erro ao atualizar os dados do registro.')."<br><br>";
        include('erro_botao_fechar.php');
        exit;
    }
}

#======================================================================================================================
$sp =  "
    begin
        sp_exemplo (:grupo_estab,
                    :estab,
                    :grupo_ms,
                    :marca,
                    :ms,
                    :item,
                    :agrupamento,
                    :ordem
                    :cursor,
                    :id);
    end;
";

$stmt = oci_parse($dbh, $sp);
$cursor = oci_new_cursor($dbh);

oci_bind_by_name($stmt, ':grupo_estab', $GRUPO_ESTAB);
oci_bind_by_name($stmt, ':estab', $ESTAB);
oci_bind_by_name($stmt, ':grupo_ms', $GRUPO_MS);
oci_bind_by_name($stmt, ':marca', $MARCA);
oci_bind_by_name($stmt, ':ms', $MS);
oci_bind_by_name($stmt, ':item', $ITEM);
oci_bind_by_name($stmt, ':agrupamento', $AGRUPAMENTO);
oci_bind_by_name($stmt, ':ordem', $ORDEM);
oci_bind_by_name($stmt, ':cursor', &$cursor, -1, OCI_B_CURSOR);
oci_bind_by_name($stmt, ':id', &$ID);

$resultado = @oci_execute($stmt);
if ($resultado) {
    db_commit($dbh);

    if(@oci_execute($cursor)){
        while (($r = oci_fetch_assoc($cursor)) != false) {
            $dados[$r['AGRUP']][] = $r;
            $total['REG']++;
            $total['VLR_TOTAL']+=$r[VLR_TOTAL];
            $total_parcial[$r['AGRUP']]['REG']++;
            $total_parcial[$r['AGRUP']]['VLR_TOTAL']+=$r[VLR_TOTAL];
        }
    }
}
else {
    db_rollback($dbh);
    $msg_erro = db_error($php_errormsg, $sp);
    $msg_erro = (trim($msg_erro) ? trim($msg_erro) : 'Erro ao atualizar os dados do registro.')."<br><br>";
}
oci_free_statement($stmt);


#======================================================================================================================
$sSQL = "
    select {$agrup[$AGRUPAMENTO]['CAMPO_VLR']} as agrup,
           {$agrup[$AGRUPAMENTO]['CAMPO_DESCR']} as agrup_descr,
           {$agrup[$AGRUPAMENTO]['CAMPO_ORDEM']} as agrup_ordem,
           {$agrup[$ORDENACAO]['CAMPO_ORDEM']} as ordenacao,
           campo_clob as texto_clob,
           campo_x,
           campo_y
    from tabela
    {$sWHERE}
    order by agrup_ordem, agrup_descr, agrup, ordenacao
";
$res = db_query($dbh, $sSQL);
while ($r = db_fetch_row($res[0])) {
    $r["TEXTO_CLOB"] = (is_object($r["TEXTO_CLOB"]) ? $r["TEXTO_CLOB"]->load() : $r["TEXTO_CLOB"]);

    $dados[$r['AGRUP']][] = $r;
    $total['REG']++;
    $total['VLR_TOTAL']+=$r[VLR_TOTAL];
    $total_parcial[$r['AGRUP']]['REG']++;
    $total_parcial[$r['AGRUP']]['VLR_TOTAL']+=$r[VLR_TOTAL];
}

#======================================================================================================================
if (substr($USUARIO, 0, 3) == 'MNS' && $FORMATO != 'X'){
    echo "<!--$sp-->";
    echo "<!--$sSQL-->";
}

#======================================================================================================================
if($msg_erro || !is_array($dados)){
    $msg_erro = "<br>{$sSEL}<br><br><br>".($msg_erro ? "<img src='imagens/7anipt1a.gif' alt='ERRO! - '/> {$msg_erro}" : 'Sua busca não retornou resultados.')."<br><br>";
    include('erro_botao_fechar.php');

    $url = 'sistema.php?ACAO=publico/nome_arquivo.php'.'sistema.php?ACAO=inclusao.php&MODULO=publico/nome_entidade';
    include('erro_botao_voltar.php');

    exit;
}
else{

    if($FORMATO == 'P'){
        //include_once('pdf_mannes.php');
        include_once( 'full_pdf.php' );

        $tipo_fonte = 'Arial';
        $tam_fonte = 5.5;
        $linha = 4;
        $fill = true;
        $orientacao = "L"; //(P)ortrait, (L)andscape

        $w = array(
            0,
            10,
            10,
            10
        );
        $w[0] = (($orientacao == "L" ? 277 : 190) - array_sum($w));

        class PDF2 extends PDF_MANNES {
            function Header() {
                global $tipo_fonte, $tam_fonte, $linha, $w;
                parent::Header();
                $this->SetFillColor(220);
                $this->SetFont($tipo_fonte,'B', $tam_fonte);

                $w2 = $w;
                $this->Cell(array_shift($w2), $linha, "Estabelecimento",'1','','L', 1);
                $this->Cell(array_shift($w2), $linha, "Coluna1",'LRB','','C', 1);
                $this->Cell(array_shift($w2), $linha, "Coluna2",'LRB','','C', 1);
                $this->Cell(array_shift($w2), $linha, "Coluna3",'LRB','','C', 1);
                $this->Ln();
            }
        }

        $_POST['ESTAB'] = ($ESTAB ? selecao_valor($dbh, "select min(estab) as estab from estab where estab in ($ESTAB)", 'ESTAB') : selecao_valor($dbh, "select min(estab) as estab from v_estab", 'ESTAB'));
        $pdf = new PDF2($orientacao,'mm','A4');
        $pdf->titulo('Título Relatório');
        $pdf->selecao(str_replace('<b>','',str_replace('</b>','',$sSEL)));
        $pdf->AliasNbPages();
        $pdf->Open();
        $pdf->AddPage();
        $pdf->SetFillColor(235);
        $pdf->SetFont($tipo_fonte, '', $tam_fonte);

        foreach($dados as $estab => $dd){
            $w2 = $w;
            $fill = !$fill;

            $pdf->SetMulti();
            $pdf->MultiCell(array_shift($w2), $linha, $dd['FANTASIA'],'LR','L', $fill);

            $pdf->Cell(array_shift($w2), $linha, $dd['CAMPO1'],'LR','','R', $fill);
            $pdf->TruncCell(array_shift($w2), $linha, $dd['CAMPO2'],'LR','','R', $fill);
            $pdf->Cell(array_shift($w2), $linha, $dd['CAMPO3'],'LR','','R', $fill);
            $pdf->Ln();
        }

        $w2 = $w;
        $fill = !$fill;
        $pdf->SetFillColor(235);
        $pdf->SetFont($tipo_fonte,'B', $tam_fonte);

        $pdf->Cell(array_sum($w2), 1, "","T","","", 0); //linha final
        $pdf->Ln();

        $pdf->TruncCell(array_shift($w2), $linha, "Total {$total['REG']} registro".($total['REG']>1?'s':''),'','','L', $fill);
        $pdf->Cell(array_shift($w2), $linha, $total['CAMPO1'],'','','R', $fill);
        $pdf->Cell(array_shift($w2), $linha, $total['CAMPO2'],'','','R', $fill);
        $pdf->Cell(array_shift($w2), $linha, $total['CAMPO3'],'','','R', $fill);

        $pdf->gera_pdf($arq_pdf);
        die("<html><script>document.location='$arq_pdf';</script></html>");

    }
    elseif(in_array($FORMATO,array('X','C','T'))){
        foreach($dados as $k => $dd){
            if(!$saida){
                $saida  = "<tr bgcolor='#DDDDDD'>";
                $saida .= "  <td><b>Estabelecimento</b></td>";
                $saida .= "  <td><b>Coluna1</b></td>";
                $saida .= "  <td><b>Coluna2</b></td>";
                $saida .= "  <td><b>Coluna3</b></td>";
                $saida .= "</tr>";
            }
            $saida .= "<tr>";
            $saida .= "  <td>{$dd['FANTASIA']}</td>";
            $saida .= "  <td>{$dd['CAMPO1']}</td>";
            $saida .= "  <td>{$dd['CAMPO2']}</td>";
            $saida .= "  <td>{$dd['CAMPO3']}</td>";
            $saida .= "</tr>";
        }


        if ($FORMATO == 'X') {
            header("Content-type: application/msexcel");
            header("Cache-control: private");
            header("Content-Disposition: attachment; filename=nome_relatorio_".date('Ymd_His').".xls");
            //header('Pragma: no-cache');
            //header("Expires: 0");
            //header("Content-Transfer-Encoding: UTF-8");
            die("<table border='1'> <tr><td colspan='99'>{$sSEL}</td></tr> <tr><td colspan='99'></td></tr> {$saida} </table>");
        }
        elseif ($FORMATO == 'C') {
            header("Content-type: text/csv");
            header("Cache-control: private");
            header("Content-Disposition: attachment; filename=nome_relatorio_".date('Ymd_His').".csv");
            //header('Pragma: no-cache');
            //header("Expires: 0");
            //header("Content-Transfer-Encoding: UTF-8");
            die("<table>{$saida}</table>");
        }
        else{
            header("Content-type: text/txt");
            header("Cache-control: private");
            header("Content-Disposition: attachment; filename=nome_relatorio_".date('Ymd_His').".txt");
            //header('Pragma: no-cache');
            //header("Expires: 0");
            //header("Content-Transfer-Encoding: UTF-8");
            die("<table>{$saida}</table>");
        }
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <?require_once('mns_head.php')?>
    </head>

    <body>
        <?require_once('mns_body.php');?>

		<p>
            <input type="button" value="Novo" class="botao" name="NOVO" onclick="javascript:document.location='sistema.php?ACAO=publico/nome_arquivo.php'"/>
            <input type='button' value='Novo' class='botao' name='NOVO' onclick="javascript:document.location='sistema.php?ACAO=inclusao.php&MODULO=publico/nome_entidade'"/>
        </p>
        <p><?=$sSEL?>&nbsp;</p> <?

        $saida .= "<table class='tabela'>";
        $saida .= "<tr class='cabecalho freeze'>";
        $saida .= "  <td><b>Estabelecimento</b></td>";
        $saida .= "  <td><b>Coluna1</b></td>";
        $saida .= "  <td><b>Coluna2</b></td>";
        $saida .= "  <td><b>Coluna3</b></td>";
        $saida .= "</tr>";

        foreach($dados as $dd_agrup){
            $saida .= "<tr class='agrupamento'>";
            $saida .= "  <td colspan='4'><b>{$agrup[$AGRUPAMENTO]['DESCR']}: {$dd_agrup[0]['AGRUP_DESCR']}</b></td>";
            $saida .= "</tr>";

            foreach($dd_agrup as $dd){
                //$class = (++$zebrado%2 ? "branca" : "colorida2");
                $link_estab = "sistema.php?ACAO=publico/estab.view.php&mnsSetRef=1&ESTAB={$dd['ESTAB']}";
                //"sistema.php?ACAO=alteracao.php&MODULO=publico/nome_entidade&KEY={$dd['KEY']}";

                $saida .= "<tr>";
                $saida .= "  <td><a target='_blank' class='edit' href='{$link_estab}' title='Abrir relatório de Estabelecimentos'>{$dd['FANTASIA']}</a></td>";
                $saida .= "  <td>{$dd['CAMPO1']}</td>";
                $saida .= "  <td>{$dd['CAMPO2']}</td>";
                $saida .= "  <td>{$dd['CAMPO3']}</td>";
                $saida .= "</tr>";
            }
            $saida .= "<tr class='total'>";
            $saida .= "  <td nowrap><b>Subtotal {$dd_agrup[0]['AGRUP_DESCR']}: {$total_parcial[$dd['AGRUP']]['REG']} registro".($total_parcial[$dd['AGRUP']]['REG'] > 1 ? 's' : '')."</b></td>";
            $saida .= "  <td>{$total['CAMPO1']}</td>";
            $saida .= "  <td>{$total['CAMPO2']}</td>";
            $saida .= "  <td>{$total['CAMPO3']}</td>";
            $saida .= "</tr>";
        }

        $saida .= "<tr class='rodape'>";
        $saida .= "  <td nowrap><b>Total: {$total['REG']} registro".($total['REG'] > 1 ? 's' : '')."</b></td>";
        $saida .= "  <td>{$total['CAMPO1']}</td>";
        $saida .= "  <td>{$total['CAMPO2']}</td>";
        $saida .= "  <td>{$total['CAMPO3']}</td>";
        $saida .= "</tr>";
        $saida .= "</table>";

        echo $saida; ?>

        <br>

        <p>
            <input type="button" class="botao" value="Fechar" onClick="javascript: if(confirm('Deseja fechar este relatório?')){window.close();}">&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" class="botao" value="Voltar" onclick="javascript: location='sistema.php?ACAO=publico/nome_arquivo.selecao.php'">&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="buttom" class="botao" value="Voltar" onClick="javascript:if(window.history.length > 1){window.history.back();}else{window.close();}">&nbsp;&nbsp;&nbsp;&nbsp;
            <?
            $CUSTOM['#HTML#FORM#ANTES#BOTAO#'] = "<input type='button' class='botao' value='Voltar' onclick='javascript: location=\"sistema.php?ACAO=publico/nome_arquivo.selecao.php'\"'>";
            $CUSTOM['#HTML#FORM#ANTES#BOTAO#'] = "<input type='buttom' class='botao' value='Voltar' onClick='javascript:if(window.history.length > 1){window.history.back();}else{window.close();}'>&nbsp;&nbsp;&nbsp;&nbsp;";
            ?>
        </p>
    </body>
</html>
