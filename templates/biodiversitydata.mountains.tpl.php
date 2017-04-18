<legend><?php echo getMLtext('biodiversity_data') . ": " . getMLtext('region_mountains') ?></legend>
<div class="row-fluid">
    <div class="span12">                      
        <div id="intro_box">
            <div id="intro">
                <?php if ($user['siterole'] == 'admin'): ?>
                    <div style='float:right'>
                        <form action="out.edit_text.php">
                            <input type='hidden' name='key' id='key' value='mountains' />
                            <input type='submit' value="<?php printMLtext('edit') ?>" />
                        </form>
                    </div>
                <?php endif; ?>
                <div><?php echo $user_text ?></div>
            </div>
            <div id="image_set">
                <img src='images/virungas.jpg' width='400px' height='200px' alt='pic' title='pic' />
                <p><?php printMLtext('image_caption_virungas') ?></p>                
                <img src='images/ambalavao.jpg' width='400px' height='200px' alt='pic' title='pic' />                   
                <p><?php printMLtext('image_caption_ambalavao') ?></p>
                <img src='images/virungas2.jpg' width='400px' height='200px' alt='pic' title='pic' />  
                <p><?php printMLtext('image_caption_virungas2') ?></p>
            </div>
        </div>
        <div class="span12">
        <table style='border-spacing: 10px; border-collapse: separate;'>
            <colgroup>
                <col width='33%'/>
                <col width='33%'/>
                <col width='33%'/>
            </colgroup>             
            <tr>   
                <td colspan = '3'>
                    <h3><?php printMLtext('region_mountains') ?></h3>
                </td>                
            </tr>
            <tr>
                <td class='welltd'>
                    <table border='0'>
                        <tr>
                            <td colspan='2'><a href='out.speciestree.mountains.php' alt="<?php printMLtext('biodiversity_data') ?>" title="<?php printMLtext('biodiversity_data') ?>"><h4><?php printMLtext('biodiversity_data') ?></h4></a></td>
                        </tr>
                        <tr>
                            <td><img src='images/red_colobus_monkey.jpg' height='100px' width='140px' border='0' alt='pic'/></td>
                            <td>
                                <p><?php printMLtext('arbmis_intro_species') ?></p>
                                <ul>
								<li><a href='out.speciestree.mountains.php' alt="<?php printMLtext('species') ?>" title="<?php printMLtext('species') ?>"><?php printMLtext('species') ?></a></li>
								<li><a href='out.listdatasets.mountains.php' alt="<?php printMLtext('datasets') ?>" title="<?php printMLtext('datasets') ?>"><?php printMLtext('datasets') ?></a></li>
								<li><a href='out.listoccurrence.mountains.php' alt="<?php printMLtext('records') ?>" title="<?php printMLtext('records') ?>"><?php printMLtext('records') ?></a></li>
								</ul>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class='welltd'>
                    <table border='0'>
                        <tr>
                            <td colspan='2'><a href='out.map.mountains.php' alt="<?php printMLtext('map') ?>" title="<?php printMLtext('map') ?>"><h4><?php printMLtext('map') ?></h4></a></td>
                        </tr>
                        <tr>
                            <td><img src='images/Coffea_arabica.jpg' height='100px' width='140px' border='0' alt='pic'/></td>
                            <td>
                                <p><?php printMLtext('arbmis_intro_map') ?></p>
                                <a href='out.map.mountains.php' alt="<?php printMLtext('map') ?>" title="<?php printMLtext('map') ?>"><?php printMLtext('region_mountains') ?></a>
                            </td>
                        </tr>
                    </table>
                </td>               
                <td class='welltd'>
                    <table border='0'>
                        <tr>
                            <td colspan='2'><a href='library-mnt/' alt="<?php printMLtext('libraries') ?>" title="<?php printMLtext('libraries') ?>"><h4><?php printMLtext('libraries') ?></h4></a></td>
                        </tr>
                        <tr>
                            <td><img src='images/shrike.jpg' height='100px' width='140px' border='0' alt='pic'/></td>
                            <td>
                                <p><?php printMLtext('arbmis_intro_library') ?></p>
								<a href='library-mnt/'><?php printMLtext('library_mnt') ?></a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </div>
    </div>                    
</div>
