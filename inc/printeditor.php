<?php

/**
 * Prints AFC editor HTML codes
 */
function afc_print_the_editor(){
	$data = new afceditorform();
	$data->printTheForm();
}

/**
 * Generates AFC editor HTML code
 */
class afceditorform{

	/**
     * Generates font faces for editor fonts list select element
     * @return string
     */
	function generateFontsSelect(){
		$afcFonts = new afcfonts();
		$fonts = $afcFonts->getCols();
		$fontsStr = '';
		foreach( $fonts as $font){
			$fontsStr .= '<option value="' . $font['name'] . '">' . $font['name'] . '   [' . $font['status'] . ']</option>';
		}
		return $fontsStr;
	}

	/**
     * Generates a list of possible font weights
     * @return string
     */
	function generateFontWeightOptions(){
		$weights = '';
		for( $i=100; $i < 1000; $i += 100 ){
			$weights .= '<option value="' . $i . '">' . $i . '</option>';
		}
		return $weights;
	}

	/**
     * Generats pageTypes list for editor form
     * @return string
     */
	function generateTaxonomyList(){
		$output = '<div class="taxonomylist">';
		$pageTypes = afcStrings::getString('pagetypes');
		foreach( $pageTypes as $pt=>$val ){
			$output .= '<div class="afcui toggle checkbox tax" id="chb_' . $pt . '"><input type="checkbox"  value="' . $pt .'" ><label>' . $val . '</label></div>';
		}
		$output .= '</div>';
		return $output;
	}

	/**
     * Generates list of available properties
     * @return string
     */
	function generatePropertyList(){
		$prop = '';
		foreach( afcStrings::getString('propertyList') as $property=>$val ){
			$prop .= '<option value="' . $property . '"' . (($property == 'fontfamily')? 'selected="selected"' : '') . '>' . str_replace( ':', '', $val ) . '</option>';
		}
		return $prop;
	}

	/**
     * Prints final editor HTML code
     */
	function printTheForm(){
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
		$strings = afcStrings::getString('editorStrings');
		$pStrings = afcStrings::getString('propertyList');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <title><?php echo _e("AFC Editor","afc_texdomain"); ?></title>
    <?php 
        wp_print_styles( array( 'wp-color-picker', 'afc-editor-ui', 'afc-frontend-css', 'afc-editor-fonts', 'afc-editor-style' ) );
        if(is_rtl()){
            wp_print_styles( array( 'afc-editor-rtl' ) );            
        }
        wp_print_scripts( array( 'jquery', 'afc-iris-color-picker', 'wp-color-picker', 'afc-editor-ui', 'afc-editor-js' ) );
        
    ?>
    <script>
        jj(document).ready(function () {
            jj('.afcframe').load(function () {
                jj(this).contents().find('body #wpadminbar a').click(function () { return false; });
                jj(this).contents().find('body #wpadminbar .admin-bar-search').css('display', 'none');
            });
        });
    </script>
</head>
<body>
    <div class="afcwrapper">
        <div class="afctoolbox afcwrap">


            <div class="panelleft">
                <div class="groups">
                    <div class="group1">
                        <div class="controlset">
                            <h3><?php echo $strings['selectedelement']; ?></h3>
                            <div class="afcselectedelement"></div>
                        </div>
                        <div class="controlset">
                            <h3 class="taxonomytitle"  data-content="<?php echo $strings['choosetaxonomyhelp']; ?>"><?php echo $strings['choosetaxonomy']; ?> </h3>
                            <?php echo $this->generateTaxonomyList(); ?>
                        </div>
                    </div>
                    <div class="group2">
                        <div class="controlset dataselect">
                            <h3><?php echo $strings['properties']; ?> </h3>
                            <select id="propertiesdropdown" class="propertylist afcui dropdown button">
                                <?php echo $this->generatePropertyList(); ?>
                            </select>
                        </div>
                        <div class="databox">
                            <table>
                                <tr class="fontfamily">
                                    <th>
                                        <h3><?php echo $pStrings['fontfamily']; ?></h3>
                                    </th>
                                    <td>
                                        <select id="fontselectionid" class="fontselection afcui dropdown button">
                                            <option value="none" selected="selected"><?php _e( 'No Font', 'afc_textdomain' ); ?></option>
                                            <?php echo $this->generateFontsSelect(); ?>
                                        </select>
                                        <div class="afcui toggle checkbox afcforcechange">
                                            <input class="" type="checkbox" />
                                            <label><?php echo $strings['force']; ?></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="fontsize">
                                    <th>
                                        <h3><?php echo $pStrings['fontsize']; ?></h3>
                                    </th>
                                    <td>
                                        <label class="afcfontsize numlbl">0</label><span class="dis">px</span>
                                        <span class="increasethesize fontsize afcui icon button green"><i class="icon arrow up"></i></span>
                                        <span class="decreasethesize fontsize afcui icon button red"><i class="icon arrow down"></i></span>
                                    </td>
                                </tr>
                                <tr class="fontweight">
                                    <th>
                                        <h3><?php echo $pStrings['fontweight']; ?></h3>
                                    </th>
                                    <td>
                                        <select class="fontweight afcui dropdown button">
                                            <option value="none" selected="selected"><?php echo $strings['unset']; ?></option><?php echo $this->generateFontWeightOptions(); ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="fontstyle">
                                    <th>
                                        <h3><?php echo $pStrings['fontstyle']; ?></h3>
                                    </th>
                                    <td>
                                        <select class="fontstyle afcui dropdown button" value="none">
                                            <option value="none" selected="selected"><?php echo $strings['unset']; ?></option>
                                            <option value="normal"><?php echo $strings['normal']; ?></option>
                                            <option value="italic"><?php echo $strings['italic']; ?></option>
                                            <option value="oblique"><?php echo $strings['oblique']; ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="textcolor">
                                    <th>
                                        <h3><?php echo $pStrings['textcolor']; ?></h3>
                                    </th>
                                    <td>
                                        <span class="afccolorpickerholder textcolor">
                                            <span>
                                                <input id="afctextcolor" type="text" />
                                            </span>
                                        </span>
                                    </td>
                                </tr>
                                <tr class="textdecoration">
                                    <th>
                                        <h3><?php echo $pStrings['textdecoration']; ?></h3>
                                    </th>
                                    <td>
                                        <select class="textdecoration afcui dropdown button" value="unset">
                                            <option value="unset" selected="selected"><?php echo $strings['unset']; ?></option>
                                            <option value="none"><?php echo $strings['none']; ?></option>
                                            <option value="underline"><?php echo $strings['underline']; ?></option>
                                            <option value="overline"><?php echo $strings['overline']; ?></option>
                                            <option value="line-through"><?php echo $strings['linethrough']; ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="textshadow">
                                    <th>
                                        <h3><?php echo $pStrings['textshadow']; ?></h3>
                                    </th>
                                    <td>
                                        <table class="innertable">
                                            <tr>
                                                <th><?php echo $strings['h-shadow']; ?></th>
                                                <td>
                                                    <label class="afctxth-shadow numlbl">0</label><span class="dis">px</span>
                                                    <span class="increasethesize h-shadow afcui icon button green"><i class="icon arrow up"></i></span>
                                                    <span class="decreasethesize h-shadow afcui icon button red"><i class="icon arrow down"></i></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php echo $strings['v-shadow']; ?></th>
                                                <td>
                                                    <label class="afctxtv-shadow numlbl">0</label><span class="dis">px</span>
                                                    <span class="increasethesize v-shadow afcui icon button green"><i class="icon arrow up"></i></span>
                                                    <span class="decreasethesize v-shadow afcui icon button red"><i class="icon arrow down"></i></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php echo $strings['blur']; ?></th>
                                                <td>
                                                    <label class="afcshadow-blur numlbl">0</label><span class="dis">px</span>
                                                    <span class="increasethesize shadow-blur afcui icon button green"><i class="icon arrow up"></i></span>
                                                    <span class="decreasethesize shadow-blur afcui icon button red"><i class="icon arrow down"></i></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php echo $strings['color']; ?></th>
                                                <td>
                                                    <span class="afccolorpickerholder shadowcolor">
                                                        <span>
                                                            <input id="textshadow-color" type="text" />
                                                        </span>
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panelright">
                <div class="afc-op-button afcui icon button yellow" onclick="afcUnlock()" data-variation="inverted" data-content="<?php echo $strings['clear']; ?>"><i class="icon unlock alternate"></i></div>
                <div class="afc-op-button afcui icon button orange" onclick="afcRemoveSelector()" data-variation="inverted" data-content="<?php echo $strings['reset']; ?>"><i class="icon remove"></i></div>
                <div class="afc-op-button afcui icon button blue" onclick="afcSaveChanges()" data-variation="inverted" data-content="<?php echo $strings['save']; ?>"><i class="icon save"></i></div>
                <div class="afc-op-button afcui icon button red" onclick="afcSwitchTab()" data-variation="inverted" data-content="<?php echo $strings['switchtab']; ?>"><i class="icon undo"></i></div>
            </div>

        </div>
        <div class="sitecontainer">
            <iframe class="afcframe" src="<?php echo afc_get_url( 'normal' ); ?>"></iframe>
        </div>
    </div>
    <div class="ui modal small">
        <i class="close icon"></i>
        <div class="header">
            <?php echo _e( 'AFC Alert', 'afc_textdomain' ); ?>
        </div>
        <div class="content">
            <div class="description">
                <?php echo _e( 'Default Message', 'afc_textdomain' ); ?>
            </div>
        </div>
        <div class="actions">
            <div class="afcui button green"><?php echo _e( 'Ok', 'afc_textdomain' ); ?></div>
        </div>
    </div>
    <div class="afcui green icon attached button toggleeditor">
        <span class="text "><?php echo _e( 'Toggle Editor', 'afc_textdomain' ); ?></span>
    </div>
    <div class="afcwaiting">
        <div></div>
    </div>
</body>
</html>
<?php
	}
}
?>