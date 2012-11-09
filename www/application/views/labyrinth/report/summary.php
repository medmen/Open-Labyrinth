<?php
/**
 * Open Labyrinth [ http://www.openlabyrinth.ca ]
 *
 * Open Labyrinth is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Open Labyrinth is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Open Labyrinth.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2012 Open Labyrinth. All Rights Reserved.
 *
 */

function getRandomColor() {
    mt_srand((double) microtime() * 1000000);
    $c = '';
    while (strlen($c) < 6) {
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return $c;
}
?>
<?php if (isset($templateData['map'])) { ?>
    <table width="100%" height="100%" cellpadding='6'>
        <tr>
            <td valign="top" bgcolor="#bbbbcb">
                <h4><?php echo __('Aggregate report for Labyrinth "') . $templateData['map']->name . '"'; ?></h4>
                <table width="100%" cellpadding="6">
                    <tr bgcolor="#ffffff" align="left">
                        <td align="left">
                            <p><a href="labyrinth_report.asp?mapid=21">back to reports</a></p>
                            <p>number of sessions: <?php if(isset($templateData['sessions'])) echo count($templateData['sessions']); ?> (more than <?php if(isset($templateData['minClicks'])) echo count($templateData['minClicks']); ?> clicks):</p>

                            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="565" height="420">
                                <param name="FlashVars" value="&amp;dataXML=&lt;graph bgcolor='FFFFFF' canvasbgcolor='FFFFFF' xaxisname='number of nodes in session' yaxisname='frequency' caption='Number of decisions per session'  &gt;
                                       &lt;set name='1' value='0' color='8888A8' /&gt;
                                       &lt;set name='2' value='0' color='8888A8' /&gt;
                                       &lt;set name='3' value='0' color='8888A8' /&gt;
                                       &lt;set name='4' value='3' color='8888A8' /&gt;
                                       &lt;set name='5' value='3' color='8888A8' /&gt;
                                       &lt;/graph&gt;">
                                <param name="movie" value="<?php echo URL::base(); ?>documents/FC_2_3_Column3D.swf">
                                <param name="quality" value="high">
                                <param name="bgcolor" value="#FFFFFF">
                                <embed src="<?php echo URL::base(); ?>documents/FC_2_3_Column3D.swf" flashvars="&amp;dataXML=&lt;graph bgcolor='FFFFFF' canvasbgcolor='FFFFFF' xaxisname='number of nodes in session' yaxisname='frequency' caption='Number of decisions per session'  &gt;
                                       &lt;set name='1' value='0' color='8888A8' /&gt;
                                       &lt;set name='2' value='0' color='8888A8' /&gt;
                                       &lt;set name='3' value='0' color='8888A8' /&gt;
                                       &lt;set name='4' value='3' color='8888A8' /&gt;
                                       &lt;set name='5' value='3' color='8888A8' /&gt;
                                       &lt;/graph&gt;" quality="high" bgcolor="#FFFFFF" name="Line" align="" width="565" height="420" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">
                            </object>
                            <div align="left">
                            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="565" height="420">
                                <param name="FlashVars" value="&dataXML=<graph bgcolor='FFFFFF' canvasbgcolor='FFFFFF' xaxisname='time taken in session' yaxisname='frequency' caption='Time taken per session'>
                                       <?php 
                                            if(isset($templateData['sessions']) and count($templateData['sessions']) > 0) { 
                                                $timeTakes = array();
                                                foreach($templateData['sessions'] as $session) {
                                                    if(count($session->traces) > 0) {
                                                        $timeTakes[] = $session->traces[count($session->traces) - 1]->date_stamp - $session->start_time;
                                                    }
                                                }
                                                
                                                $maxValue = 0;
                                                foreach($timeTakes as $time) {
                                                    if($time > $maxValue) {
                                                        $maxValue = $time;
                                                    }
                                                }
                                                
                                                $f = $maxValue / 20;
                                                $tc = 0;
                                                $tl = 0;
                                                if($f < 1) {
                                                    $tc = 1;
                                                    $tl = $maxValue;
                                                } else {
                                                    $tc = (int)$f;
                                                    if($tc < $f) { $tc += 1; }
                                                    $tl = 21;
                                                }
                                                
                                                $timeF = array();
                                                for($i = 0; $i < $tl; $i++) {
                                                    $timeF[$i] = 0;
                                                }
                                                foreach($timeTakes as $time) {
                                                    for($i = 0; $i < $tl; $i++) {
                                                        $v1 = ($i)/$tc;
                                                        $v2 = ($i+1)/$tc;
                                                        if($time <= $v2 and $time > $v1) {
                                                            $timeF[$i] = $timeF[$i] + 1;
                                                        }
                                                    }
                                                }
                                                
                                                for($i = 0; $i < $tl; $i++) {
                                                    ?> <set name='<?php echo $tc*($i+1); ?>' value='<?php echo $timeF[$i]; ?>' color='8888A8' /> <?php 
                                                }
                                            }
                                       ?>
                                       </graph>">
                                <param name="movie" value="<?php echo URL::base(); ?>documents/FC_2_3_Column3D.swf">
                                <param name="quality" value="high">
                                <param name="bgcolor" value="#FFFFFF">
                                <embed src="<?php echo URL::base(); ?>documents/FC_2_3_Column3D.swf" flashvars="&amp;dataXML=&lt;graph bgcolor='FFFFFF' canvasbgcolor='FFFFFF' xaxisname='time taken in session' yaxisname='frequency' caption='Time taken per session'  &gt;
                                       <?php 
                                            if(isset($templateData['sessions']) and count($templateData['sessions']) > 0) { 
                                                for($i = 0; $i < $tl; $i++) {
                                                    ?> <set name='<?php echo $tc*($i+1); ?>' value='<?php echo $timeF[$i]; ?>' color='8888A8' />; <?php
                                                }
                                            }
                                       ?>
                                       </graph>" quality="high" bgcolor="#FFFFFF" name="Line" align="" width="565" height="420" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">
                            </object>
                            </div>
                            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="565" height="420">
                                <param name="FlashVars" value="&dataXML=<graph bgcolor='FFFFFF' canvasbgcolor='FFFFFF' xaxisname='terminal nodes' yaxisname='frequency' caption='terminal nodes per session'>
                                       &lt;set name='new node E (64)' value='9' color='8888A8' /&gt;
                                       &lt;set name='node 2 (62)' value='1' color='8888A8' /&gt;
                                       &lt;set name='new node (63)' value='1' color='8888A8' /&gt;
                                       </graph>">
                                <param name="movie" value="<?php echo URL::base(); ?>documents/FC_2_3_Column3D.swf">
                                <param name="quality" value="high">
                                <param name="bgcolor" value="#FFFFFF">
                                <embed src="<?php echo URL::base(); ?>documents/FC_2_3_Column3D.swf" flashvars="&dataXML=<graph bgcolor='FFFFFF' canvasbgcolor='FFFFFF' xaxisname='terminal nodes' yaxisname='frequency' caption='terminal nodes per session'>
                                       &lt;set name='new node E (64)' value='9' color='8888A8' /&gt;
                                       &lt;set name='node 2 (62)' value='1' color='8888A8' /&gt;
                                       &lt;set name='new node (63)' value='1' color='8888A8' /&gt;
                                       </graph>" quality="high" bgcolor="#FFFFFF" name="Line" align="" width="565" height="420" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">
                            </object>
                            <?php if(isset($templateData['allCounters']) and count($templateData['allCounters']) > 0) { ?>
                            <?php foreach($templateData['allCounters'] as $counter) { ?>
                                <p>Counter: <?php echo $counter->name; ?> (<?php echo $counter->id; ?>)</p>
                                <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="565" height="420">
                                <param name="FlashVars" value="&amp;dataXML=&lt;graph caption='Counters' lineThickness='2' showValues='1' formatNumberScale='1' rotateNames='1' decimalPrecision='2' anchorRadius='2' anchorBgAlpha='50' showAlternateVGridColor='1' anchorAlpha='100' animation='1' limitsDecimalPrecision='0' divLineDecimalPrecision='1'>
                                       <categories>
                                       <?php foreach($templateData['sessions'] as $session) { ?>
                                       <c n='<?php echo $session->id; ?>' />
                                       <?php } ?>
                                       </categories>
									   <?php foreach($templateData['sessions'] as $session) { ?>
									   <dataset seriesName='session <?php echo $session->id; ?>' color='<?php $c = getRandomColor(); echo $c; ?>' anchorBorderColor='<?php echo $c; ?>'>
										    <?php if(isset($templateData['startValueCounters'])) { ?>
											<s v='<?php echo $templateData['startValueCounters'][$counter->id]; ?>' />
											<?php } ?>
											<?php if(isset($templateData['counters']) and count($templateData['counters']) > 0)  { ?>
												<?php if(array_key_exists(1, $templateData['counters'][$counter->name]) and count($templateData['counters'][$counter->name][1]) > 0) { ?>
												<?php for($i = 1; $i < count($templateData['counters'][$counter->name][1]); $i++) { ?>
												<s v='<?php echo $templateData['counters'][$counter->name][1][$i]; ?>' />
												<?php } ?>
												<s v='<?php echo $templateData['counters'][$counter->name][1][0]; ?>' />
												<?php } ?>
											<?php } ?>
									   </dataset>
									   <?php } ?>
                                       </graph>">
                                <param name="movie" value="<?php echo URL::base(); ?>documents/FC_2_3_MSLine.swf">
                                <param name="quality" value="high"><param name="bgcolor" value="#FFFFFF">
                                <embed src="<?php echo URL::base(); ?>documents/FC_2_3_MSLine.swf" flashvars="&dataXML=<graph caption='Counters' lineThickness='2' showValues='1' formatNumberScale='1' rotateNames='1' decimalPrecision='2' anchorRadius='2' anchorBgAlpha='50' showAlternateVGridColor='1' anchorAlpha='100' animation='1' limitsDecimalPrecision='0' divLineDecimalPrecision='1'>
                                       <categories>
                                       <?php foreach($templateData['sessions'] as $session) { ?>
                                       <c n='<?php echo $session->id; ?>' />
                                       <?php } ?>
                                       </categories>
										<?php foreach($templateData['sessions'] as $session) { ?>
									   <dataset seriesName='session <?php echo $session->id; ?>' color='<?php $c = getRandomColor(); echo $c; ?>' anchorBorderColor='<?php echo $c; ?>'>
											<?php if(isset($templateData['startValueCounters'])) { ?>
											<s v='<?php echo $templateData['startValueCounters'][$counter->id]; ?>' />
											<?php } ?>
											<?php if(isset($templateData['counters']) and count($templateData['counters']) > 0)  { ?>
												<?php if(array_key_exists(1, $templateData['counters'][$counter->name]) and count($templateData['counters'][$counter->name][1]) > 0) { ?>
												<?php for($i = 1; $i < count($templateData['counters'][$counter->name][1]); $i++) { ?>
												<s v='<?php echo $templateData['counters'][$counter->name][1][$i]; ?>' />
												<?php } ?>
												<s v='<?php echo $templateData['counters'][$counter->name][1][0]; ?>' />
												<?php } ?>
											<?php } ?>
									   </dataset>
									   <?php } ?>
                                       </graph>" quality="high" bgcolor="#FFFFFF" name="Line" align="" width="565" height="420" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">
                            </object>
                            <?php } ?>
                            <?php } ?> 
                            <p>data table:</p>
                            <table border="1">
                                <tr>
                                    <td><p>sessionID</p></td>
                                    <td><p>number of nodes</p></td>
                                    <td><p>time taken (s)</p></td>
                                    <td><p>last node</p></td>
                                </tr>
                                <?php if(isset($templateData['sessions']) and count($templateData['sessions']) > 0) { ?>
                                <?php foreach($templateData['sessions'] as $session) { ?>
                                <tr>
                                    <td><p><?php echo $session->id; ?></p></td>
                                    <td><p><?php echo count($session->traces); ?></p></td>
                                    <td><p><?php echo $session->traces[count($session->traces) - 1]->date_stamp - $session->start_time; ?></p></td>
                                    <td><p><?php echo $session->traces[count($session->traces) - 1]->node_id; ?></p></td>
                                </tr>
                                <?php } ?>
                                <?php } ?>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?php } ?>
