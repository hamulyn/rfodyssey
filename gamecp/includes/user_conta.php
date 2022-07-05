<?php
//////////////////////////////////////////////////////////////
//    Game CP: RF Online Game Control Panel                    //
//    Module: admin_donations_google.php                        //
//    Copyright (C) www.AaronDM.com                            //
//////////////////////////////////////////////////////////////

# Write Module to Menu
if( !empty($setmodules) )
{
    $file = basename(__FILE__);
    $module['Donations']['Deposito Direto em Conta'] = $file;
    return;
}

# Write out our basic information about this page
$lefttitle = "Deposito Direto em Conta";

# Build the rest of the site
if ($this_script == $script_name) {

    $exit_stage = 0;

    if (isset($isuser) && $isuser == true) {


            $out .= '<center>'."\n";
            $out .= '<h2>Your account currently has <span style="color: #8F92E8;">'.number_format($userdata['points']).'</span> Game Points <span style="color: #8F92E8;"> '.number_format($userdata['vote_points']).'</span> Vote Point </h2>'."\n";
            $out .= '<form method="post" action="'.$script_name.'?do='.$_GET['do'].'">'."\n";
            $out .= '</form>'."\n";
            $out .= '</center>'."\n";
            $out .= '<br>'."\n";


            $out .= '<div class="spacer">&nbsp;</div>
    <div  align="center"><font color="#FF0000" size="4">OBS: So seram Creditado o Valor em GP apos a Confirmação do Pagamento.<br>
	Docs, Teds e Envelopes podem demorar ate 24hrs para a confirmação</div></font><br>
	
<div id="Div19"></div>
      <table width="750" border="1" bordercolor="#FFFFFF" align="center">
        <tr>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Argencia</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Operacao</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Conta</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Tipo</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Banco</th>		  
          <th width="193" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Beneficiado</th>	
          <th width="213" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">CPF p/ DOC</th>		  
		  
        </tr>
        <tr>
        </tr>
        </tr>
        <tr>
          <td align="center"><font color="#FFFFFF" size="4">0673</td>
          <td align="center"><font color="#FFFFFF" size="4">013</td>
          <td align="center"><font color="#FFFFFF" size="4">000013320-7</td>
          <td align="center"><font color="#FFFFFF" size="4">Poupança</td>
          <td align="center"><font color="#FFFFFF" size="4">Caixa Economica</td>		  
          <td align="center"><font color="#FFFFFF" size="4">Edson Alves da Silva</td>
          <td align="center"><font color="#FFFFFF" size="4">215.967.728-08</td>		  
          
        </tr>
      </table>';
            $out .= '<br>'."\n";
            $out .= '<br>'."\n";
            $out .= '<div class="spacer">&nbsp;</div>

			    
    <div  align="center"><font color="#FFFFFF" size="4">Apos feito o Deposito 
	Favor Envie um E-mail para <font color="#FF0000" size="4">rfeqgbr@hotmail.com <font color="#FFFFFF" size="4">Com as Informacoes Seguintes:<br><br>

    Comprovante de Dep<br>
    Nome da Conta<br>
    Quantidade em Game Points</div>
   <div class="spacer">&nbsp;</div>
   
        <table width="510" border="1" bordercolor="#ffffff" align="center"><center>TABELA DE PREÇOS
        <tr>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Preço</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Creditos em GP</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Bonus</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Total</th>		  
        </tr>
        <tr>
        </tr>
        </tr>
        <tr>
          <td align="center">5,00 BRL</td>		
          <td align="center">5.000</td>
          <td align="center">2.100</td>
          <td align="center">7.100</td>
          
        </tr>
        <tr>
          <td align="center">10,00 BRL</td>		
          <td align="center">10.000</td>
          <td align="center">3.800</td>
          <td align="center">13.800</td>
          
        </tr>
        <tr>
          <td align="center">15,00 BRL</td>		
          <td align="center">15.000</td>
          <td align="center">5.500</td>
          <td align="center">20.500</td>
          
        </tr>
        <tr>
		  <td align="center">20,00 BRL</td>
          <td align="center">20.000</td>
          <td align="center">7.100</td>
          <td align="center">27.100</td>
          
        </tr>
        <tr>
          <td align="center">25,00 BRL</td>		
          <td align="center">25.000</td>
          <td align="center">8.800</td>
          <td align="center">33.800</td>
          
        </tr>
        <tr>
          <td align="center">30,00 BRL</td>		
          <td align="center">30.000</td>
          <td align="center">15.000</td>
          <td align="center">45.000</td>         
        </tr>
		<tr>
          <td align="center">35,00 BRL</td>		
          <td align="center">35.000</td>
          <td align="center">12.100</td>
          <td align="center">47.100</td>         
        </tr>
		<tr>
          <td align="center">40,00 BRL</td>		
          <td align="center">40.000</td>
          <td align="center">13.800</td>
          <td align="center">53.800</td>         
        </tr>
		<tr>
          <td align="center">45,00 BRL</td>		
          <td align="center">45.000</td>
          <td align="center">15.500</td>
          <td align="center">60.500</td>         
        </tr>
		<tr>	
          <td align="center">50,00 BRL</td>		
          <td align="center">50.000</td>
          <td align="center">17.100</td>
          <td align="center">67.100</td>         
        </tr>
      </table>
	  ';


    } else {
        $out .= $lang['no_permission'];
    }
} else {
    $out .= $lang['invalid_page_load'];
}
?>