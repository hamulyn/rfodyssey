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
    $module['Donations']['Donate Wester Union '] = $file;
    return;
}

# Write out our basic information about this page
$lefttitle = "Donate Wester Union";

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
    <div  align="center"><font color="#FF0000" size="4">Payment for International Users<br>
	NOTE:Using this method of payment can take up to 24hrs to prove payment and addition of Credits.</div></font><br>	
	
<div id="Div19"></div>
      <table width="700" border="1" bordercolor="#FFFFFF" align="center">
        <tr>
          <th width="200" bgcolor="#000000" scope="col" ><center><font color="yellow" size="4">First Name</th>
          <th width="200" bgcolor="#000000" scope="col" ><center><font color="yellow" size="4">Last Name</th>		  
          <th width="200" bgcolor="#000000" scope="col" ><center><font color="yellow" size="4">Country</th>
          <th width="200" bgcolor="#000000" scope="col" ><center><font color="yellow" size="4">City</th>		  
          <th width="200" bgcolor="#000000" scope="col" ><center><font color="yellow" size="4">Cell Number</th>		  
        </tr>
        <tr>
        </tr>
        </tr>
        <tr>
          <td align="center"><font color="#FFFFFF" size="4">Edson Alves</td>
          <td align="center"><font color="#FFFFFF" size="4">da Silva</td>
          <td align="center"><font color="#FFFFFF" size="4">Brazil</td>
          <td align="center"><font color="#FFFFFF" size="4">Rio de Janeiro</td>		  
          <td align="center"><font color="#FFFFFF" size="4">+55 (21) 96587-9825</td>		  
          
        </tr>
      </table>';
            $out .= '<br>'."\n";
            $out .= '<br>'."\n";
            $out .= '<div class="spacer">&nbsp;</div>

			    
 <div  align="center"><font color="#FFFFFF" size="4">
 After done Deposit Please Send an e-mail to <font color="#FF0000" size="4">rfeqgbr@hotmail.com <font color="#FFFFFF" size="4">With the Following Information::<br><br>

    Image Deposit Receipt <br>
    Account Name <br>
	Amount Game Points </div>
   <div class="spacer">&nbsp;</div>
   
      <table width="510" border="1" bordercolor="#ffffff" align="center"><center>TABLE PRICE
        <tr>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Price</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Credits in GP</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Bonus</th>
          <th width="183" bgcolor="#000000" scope="col"><center><font color="yellow" size="4">Total</th>		  
        </tr>
        <tr>
        </tr>
        </tr>
        <tr>
          <td align="center">2,50 USD</td>		
          <td align="center">5.000</td>
          <td align="center">2.100</td>
          <td align="center">7.100</td>
          
        </tr>
        <tr>
          <td align="center">5,00 USD</td>		
          <td align="center">10.000</td>
          <td align="center">3.800</td>
          <td align="center">13.800</td>
          
        </tr>
        <tr>
          <td align="center">7,50 USD</td>		
          <td align="center">15.000</td>
          <td align="center">5.500</td>
          <td align="center">20.500</td>
          
        </tr>
        <tr>
		  <td align="center">10,00 USD</td>
          <td align="center">20.000</td>
          <td align="center">7.100</td>
          <td align="center">27.100</td>
          
        </tr>
        <tr>
          <td align="center">12,50 USD</td>		
          <td align="center">25.000</td>
          <td align="center">8.800</td>
          <td align="center">33.800</td>
          
        </tr>
        <tr>
          <td align="center">15,00 USD</td>		
          <td align="center">30.000</td>
          <td align="center">15.000</td>
          <td align="center">45.000</td>         
        </tr>
		<tr>
          <td align="center">17,50 USD</td>		
          <td align="center">35.000</td>
          <td align="center">12.100</td>
          <td align="center">47.100</td>         
        </tr>
		<tr>
          <td align="center">20,00 USD</td>		
          <td align="center">40.000</td>
          <td align="center">13.800</td>
          <td align="center">53.800</td>         
        </tr>
		<tr>
          <td align="center">22,50 USD</td>		
          <td align="center">45.000</td>
          <td align="center">15.500</td>
          <td align="center">60.500</td>         
        </tr>
		<tr>	
          <td align="center">25,00 USD</td>		
          <td align="center">50.000</td>
          <td align="center">17.100</td>
          <td align="center">67.100</td>         
        </tr>
      </table>';


    } else {
        $out .= $lang['no_permission'];
    }
} else {
    $out .= $lang['invalid_page_load'];
}
?>