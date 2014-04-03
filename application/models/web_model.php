<?php
class Web_model extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->base = $this->config->item('base_url');
    }
    public function mail($to,$subject,$message,$mailDate)
    {
        $footer = "<br/><br/><p>Thank you. <br/>This is an auto-generated email by the <a href='social.psr.com.ph'>PSRInstagram Web Application</a> ($mailDate)";
        $message = $message.$footer;
        $headers = 'MIME-Version: 1.0'."\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1'."\n";
        $from =htmlspecialchars($this->config->item('smtp_user')); 
        $headers .= "From: Admin <itswebadmin@gmail.com>\n";
        $toParam = '';
        if (is_array($to)){
           $ctr = 0;
           $totalData = count($to);
           foreach($to as $param){
              if ($ctr != $totalData - 1){
                 $toParam = $toParam.$param.',';
              }
              else{
                 $toParam = $toParam.$param;
              }   
              $ctr++;
           }     
        }
        else{
           $toParam = $to;
        }
        $emailResult = mail($toParam,$subject,$message,$headers);
        return $emailResult;
    }

    public function getHeader($title){
        $header ="
        <!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01//EN' 'http://www.w3.org/TR/html4/DTD/strict.dtd'>
        <html lang='en'>
            <head>
            <!-- Meta, title, CSS, favicons, etc. -->
            <meta charset='utf-8'>
            <meta http-equiv='X-UA-Compatible' content='IE=edge'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>$title</title>
            <!-- Bootstrap core CSS -->
            <link href='".$this->base."others/css/bootstrap.css' rel='stylesheet'>";
        return $header;
    }
    public function emailReport($tData){
        $table1 =
                "<table  border='1' >
                    <tr>
                        <th></th>
                        <th>Total Entries</th>
                        <th>Total Valid Entries</th>
                        <th>Total Invalid Entries</th>
                        <th>Total Entries Processed</th>
                    </tr>
                    <tr>
                        <td>Today</td>
                        <td>".$tData['today'][0]."</td>
                        <td>".$tData['today'][1]."</td>
                        <td>".$tData['today'][2]."</td>
                        <td>".$tData['today'][3]."</td>
                    </tr>
                    <tr>
                        <td>Week To Date</td>
                        <td>".$tData['weekToDate'][0]."</td>
                        <td>".$tData['weekToDate'][1]."</td>
                        <td>".$tData['weekToDate'][2]."</td>
                        <td>".$tData['weekToDate'][3]."</td>
                    </tr>
                    <tr>
                        <td>Month To Date</td>
                        <td>".$tData['monthToDate'][0]."</td>
                        <td>".$tData['monthToDate'][1]."</td>
                        <td>".$tData['monthToDate'][2]."</td>
                        <td>".$tData['monthToDate'][3]."</td>
                    </tr>
                    <tr>
                        <td>Year To Date</td>
                        <td>".$tData['yearToDate'][0]."</td>
                        <td>".$tData['yearToDate'][1]."</td>
                        <td>".$tData['yearToDate'][2]."</td>
                        <td>".$tData['yearToDate'][3]."</td>
                    </tr>
                </table><br/>";
        $table2 =
                "<table  border='1' >
                    <tr>
                        <th></th>
                        <th>Total Unique Users</th>
                    </tr>
                    <tr>
                        <td>Today</td>
                        <td>".$tData['uniqueUsers'][0]."</td>
                    </tr>
                    <tr>
                        <td>Week To Date</td>
                        <td>".$tData['uniqueUsers'][1]."</td>
                    </tr>
                    <tr>
                        <td>Month To Date</td>
                        <td>".$tData['uniqueUsers'][2]."</td>
                    </tr>
                    <tr>
                        <td>Year To Date</td>
                        <td>".$tData['uniqueUsers'][3]."</td>
                    </tr>
                </table>";
        return $table1.$table2;
    }
    public function reportBody($entriesStatus,$entries,$totEntries,$totValid,$totInvalid,$totProcessed,$totUser){
        $rowEntries='';
        foreach($entries as $row)
        {
            $rowEntries = $rowEntries."<tr>";
            $rowEntries =$rowEntries."<td>".gmdate("M. d, Y  g:i a",$row->timePosted)."</td>";
            $rowEntries =$rowEntries."<td>$row->username</td>";
            $rowEntries =$rowEntries."<td>$row->tin</td>";
            $rowEntries =$rowEntries."<td>$row->amount</td>";
            $rowEntries =$rowEntries."<td>". $entriesStatus[$row->status]->description."</td>";
            $code = '';
            $dateProcessed = '';
            $processedBy = '';
            if (($row->status == 0)||($row->status ==3)){
                $code = 'NA';
                $dateProcessed = 'NA';
                $processedBy = 'NA';
            }
            else{
                $code = $row->code;
                $dateProcessed = gmdate("M. d, Y  g:i a",$row->timeProcessed);
                $processedBy = $row->processedBy;
            }
            if ($row->status == 2)
                $code = 'NA';
            
            $rowEntries =$rowEntries."<td>$code</td>";
            $rowEntries =$rowEntries."<td>$dateProcessed</td>";
            $rowEntries =$rowEntries."<td>$processedBy</td>";
            $rowEntries = $rowEntries."</tr>";
        }
        $table = "                               
        <table cellpadding='0' cellspacing='0' border='0' class='display' id='example'>
                <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                </thead>
                <tbody>
                            <tr>
                                <td><b>Date Posted</b></td>
                                <td><b>Account</b></td>
                                <td><b>Tin</b></td>
                                <td><b>Amount</b></td>
                                <td><b>Status</b></td>
                                <td><b>ECN</b></td>
                                <td><b>Date Processed</b></td>
                                <td><b>Processed By</b></td>
                            </tr>
                            ".$rowEntries."
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>Total Entries</b></td>
                                <td>$totEntries</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>Total Valid</b></td>
                                <td>$totValid</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>Total Invalid</b></td>
                                <td>$totInvalid</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>Total Processed</b></td>
                                <td>$totProcessed</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>Total Accounts</b></td>
                                <td>$totUser</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            </tbody>
	<tfoot>
		<tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
		</tr>
	</tfoot>
</table>
";
        return $table;
    }
    public function getSearchTableHeader()
    {
        $tableHeading = "<table cellpadding='0' cellspacing='0' border='0' class='display' id='example'>
                        <thead>
                                <tr>
                                        <th>hid</th>
                                        <th>Username</th>
                                        <th>Caption</th>
                                        <th>Time Posted</th>
                                        <th>Time Processed</th>
                                        <th>htimeposted</th> 
                                        <th>htimeprocessed</th> 
                                        <th>ProcessedBy</th>
                                        <th>Status</th>
                                        <th>hStatus</th>
                                        <th>Remarks</th>
                                        <th>Tin</th>
                                        <th>ORNo</th>
                                        <th>Amount</th>
                                        <th>Code</th>
                                        <th>Tagname</th>
                                        <th>Remarks</th>
                                </tr>
                        </thead>";
        return $tableHeading;
    }
    public function getSession(){
        return $this->session->userdata('logged_in');
    }
    public function getSearchTableBody($invalidReplies,$entries)
    {
        $content = "";
        $content = $content."<tbody>";
        foreach($entries as $row)
        {
            $content = $content."<tr>";
            $content =$content."<td>$row->id</td>";
            $content =$content."<td>$row->username</td>";
            $content =$content."<td>".rawurldecode($row->caption)."</td>";
            $content =$content."<td><a href='$row->entryLink' target='_blank'>".gmdate("M. d, Y  g:i a",$row->timePosted)."</a></td>";
            if ($row->timeProcessed > 0)
                $content =$content."<td>".gmdate("M. d, Y  g:i a",$row->timeProcessed)."</td>";
            else
                    $content =$content."<td>NA</td>";
            $content =$content."<td>$row->timePosted</td> ";
            $content =$content."<td>$row->timeProcessed</td>"; 
            $content =$content."<td>$row->processedBy</td>";

            if (($row->status == 2)&&($row->remarks > 5))
                $content =$content."<td>$row->desc (<a id='revoke$row->id' href='#'>Revoke</a>)</td>";
            else
                $content =$content."<td>$row->desc</td>";
            $content =$content."<td>$row->status</td>";
            $content =$content."<td><button id='show$row->id'  class='btn btn-xs btn-primary'   >Show</button></td>";
            $content =$content."<td>$row->tin</td>";
            $content =$content."<td>$row->orNo</td>";
            $content =$content."<td>$row->amount</td>";
            $content =$content."<td>$row->code</td>";
            $content =$content."<td>$row->tagName</td>";
            if ($row->status == 1)
                $content =$content."<td>".sprintf($invalidReplies[$row->remarks  - 1]->description,$row->code)."</td>";
            else
                $content =$content."<td>".rawurldecode($invalidReplies[$row->remarks - 1]->description)."</td>";
            $content =$content."</tr>";
        }
        $content = $content."</tbody>";
        return $content;
    }
    public function getSearchTableFooter()
    {
        $footer="<tfoot>
                                <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                </tr>
                        </tfoot>
                </table>";
    }
    public function getNavBar()
    {
            $sessionData =$this->session->userdata('logged_in');
            $navBar = "    <!-- Fixed navbar -->
                            <div class='navbar navbar-inverse navbar-default'>
                                <div class='container'>
                                    <div class='navbar-header'>
                                        <a class='navbar-brand' href='#'>PSR Web Tool</a>
                                    </div>
                                    <div class='navbar-collapse collapse'>
                                        <ul class='nav navbar-nav'>";
            $navBar = $navBar.  "           <li class='active'><a href='".$this->base."users'><span class='glyphicon glyphicon-home'></span> Home</a></li>";
            if (($sessionData['subscription'] == 1)||($sessionData['viewEntries'] == 1)||($sessionData['validation'] == 1))
            {
                $navBar = $navBar.              "<li class='dropdown'>
                                                <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Tag Management <b class='caret'></b></a>
                                                <ul class='dropdown-menu'>";
            }
            if ($sessionData['subscription'] == 1)
                $navBar = $navBar.  "               <li><a href='".$this->base."instagram/subscriptions'>Subscriptions</a></li>";
            if ($sessionData['viewEntries'] == 1)
                $navBar = $navBar.  "               <li><a href='".$this->base."instagram/viewEntries'>View Entries</a></li>";
            if ($sessionData['validation'] == 1)
                $navBar = $navBar.  "               <li><a href='".$this->base."instagram/validation'>Validation</a></li>";
            if (($sessionData['subscription'] == 1)||($sessionData['viewEntries'] == 1)||($sessionData['validation'] == 1))
            {
                $navBar = $navBar.  "               </ul>
                                            </li>";
            }
            if ($sessionData['userAccess'] == 1)                                
                $navBar = $navBar.  "       <li class='active'><a href='".$this->base."users/userMgt'><span class='glyphicon glyphicon-cog'></span> User Management</a></li>";
            if ($sessionData['webToolReport'] == 1)
                  $navBar = $navBar.  "     <li class='active'><a href='".$this->base."instagram/report'><span class='glyphicon glyphicon-file'></span> Web Tool Report</a></li>";
            if ($sessionData['history'] == 1)
                  $navBar = $navBar.  "     <li class='active'><a href='".$this->base."users/history'><span class='glyphicon glyphicon-dashboard'></span> Audit Trail</a></li>";
            $navBar = $navBar.  "           <li class='active'><a href='".$this->base."users/destroy'><span class='glyphicon glyphicon-log-out'></span> Logout</a></li>
                                        </ul>
                                    </div><!--/.nav-collapse -->
                                </div>
                            </div>";
            return $navBar;
    }
    public function getNavBarLogin()
    {
            $navBar = "    <!-- Fixed navbar -->
                            <div class='navbar navbar-inverse navbar-default'>
                                <div class='container'>
                                    <div class='navbar-header'>
                                        <a class='navbar-brand' href='#'>PSR Web Tool</a>
                                    </div>
                                   
                                </div>
                            </div>";
            return $navBar;
    }
    public function getFooter()
    {
            $footer="
                <!--/ Footer -->
                <div id='footer'>
                  <div class='container'>
                    <p class='text-muted credit'>&copy  <a href='http://philweb.com.ph' target='_blank'>PhilWeb Corporation</a> IT Research and Development ".date("Y")."</p>
                  </div>
                </div>";
            return $footer;
    }    
}
?>