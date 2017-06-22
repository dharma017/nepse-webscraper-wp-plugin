<?php 
if (!function_exists('live_nepse_data_marquee')):

   function live_nepse_data_marquee( $atts = '' ){
   				$html = '';
                $data =  get_option('live_nepse');
                $return_data = json_decode($data);
                $date = explode(' ', $return_data->NEPSE->Date);
                $html = "<div class='row'>";
                $html .= "<div class='col-lg-2 col-md-2'>";
                $html .=  "As of ".$date[2];
                $html .= "</div>";
               
                // echo "<pre>";
                //     // print_r($date);
                // 	print_r($return_data);
                // echo "</pre>";
                // die;
                // $html .= $return_data->NEPSE->CurrentIndex;
                // $html .= $return_data->NEPSE->PointIndex;
                // $html .= $return_data->NEPSE->PercentIndex;
                $html .= "<div class='col-lg-8 col-md-8'>";

               

                
                $html .= "<marquee  scrollamount='4' scrolldelay='10' onmouseover='this.stop();' onmouseout='this.start();'>";
                foreach ($return_data->LiveTrading as $key => $value) 
                {

	                $html .=  '&nbsp;&nbsp;<b>';		
	                $html .=  $value->Symbol;		
	                $html .=  '</b>&nbsp;&nbsp;';		
	                $html .=  '<b>(';		
	                $html .=  $value->LTP;		
	                $html .=  ')</b>';	
	                $html .=  '&nbsp;&nbsp;<b>(';		
	                $html .=  $value->Volume;		
	                $html .=  ')</b>';	
	                $html .=  '&nbsp;&nbsp;<b>(';		
	                if ($value->PointChange > 0) {
		                $html .= "<span style='color:#00E676;'>";
                        $html .=  $value->PointChange;      
		                $html .=  "<i class='fa fa-caret-up'> </i>";		
		                $html .= "</span>";
	                	}	
	               	
	               	if ($value->PointChange < 0) {
		                $html .= "<span style='color:#f44336;'>";
		                $html .=  $value->PointChange;	
                        $html .=  "<i class='fa fa-caret-down'> </i>";        

                        $html .= "</span>";
                        }   
                    if ($value->PointChange == 0) {
                        $html .= "<span style='color:#2196F3;'>";
                        $html .=  $value->PointChange;  
                        $html .=  "<i class='fa fa-sort'> </i>";        

                        $html .= "</span>"; //blue
                        }   
                        $html .=  ')</b>';      
                    }
	              $html .= "</marquee>";
                   $html .= "</div>";
                  $html .= "<div class='col-lg-2 col-md-2 text-right'>";
                  /*$html .= "<a href='http://nepalstock.com/' title='nepalstock.com'>View More</a>";*/

                   $html .= "</div>";
                   $html .= "</div>";
                  
	                return $html;
	                // return 'yo it came';
	                exit;

            }

        add_shortcode( 'get_live_stock_marquee', 'live_nepse_data_marquee' );

endif;

if (!function_exists('live_nepse_data_table')):

   function live_nepse_data_table( $atts = '' ){
   				$html = '';
                $data =  get_option('live_nepse');
                $return_data = json_decode($data);
                $date = explode(' ', $return_data->NEPSE->Date);
                $data =  "Share Price ".$return_data->NEPSE->Date;
                //$html =  $return_data->NEPSE->Date;
                $data .= " <table class='table table-bordered table-hover table-striped'> <caption>";
                $data .=	$html;

                $data .=	"</caption>
                	<thead>
                		<tr>
                			<th style='text-align: center;'>
                				S.N.
                			</th>
                			<th style='text-align: center;'>
                				Companies
                			</th>
                			<th style='text-align: center;'>
                				Last Traded Price
                			</th>
                			<th style='text-align: center;'>
                				Percent Change
                			</th>
                			<th style='text-align: center;'>
                				Point Change
                			</th>
                		</tr>
                	</thead>
                	<tbody>";
                	$count = 1;
                	foreach ($return_data->LiveTrading as $key => $value) {
                		if ($value->PointChange > 0) {
                			$data .= "<tr style='background-color:#00E676;color:white;'>"; //green
                		}
                		if ($value->PointChange < 0) {
                			$data .= "<tr style='background-color:#f44336;color:white;'>"; //red
                		}
                		if ($value->PointChange == 0) {
                			$data .= "<tr style='background-color:#2196F3;color:white;'>"; //blue
                		}	                		
		                
		                $data .= 	"<td style='text-align:center;'>";
		                $data .=	$count;	
		                $data .=	"</td>";
		                $data .= 	"<td style='text-align:center;'>";
		                $data .=	$value->Symbol;	
		                $data .=	"</td>";
		                $data .= 	"<td style='text-align:center;'>";
		                $data .=	$value->LTP;	
		                $data .=	"</td>";
		                $data .= 	"<td style='text-align:center;'>";
		                $data .=	$value->PercentChange;
		                $data .=	"</td>";
		                $data .= 	"<td style='text-align:center;'>";
		                $data .=	$value->PointChange;
		                $data .=	"</td>";
		                $data .= "</tr>";
		                $count += 1;
	                	}	
	                $data .="</tbody>
	                </table>";

	                return $data;
	                exit;

            }

        add_shortcode( 'get_live_stock_table', 'live_nepse_data_table' );

endif;