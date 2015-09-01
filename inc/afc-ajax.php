<?php
add_action( 'wp_ajax_afc_ajax', 'afc_ajax_handler' );
/**
 * Handles ajax requests in AFC
 */
function afc_ajax_handler(){
    echo 'jh';
	if( is_user_logged_in() && current_user_can('manage_options') ){	
		if( !isset( $_POST['afcnonce'] ) || $_POST['afcnonce'] == '' )
			die('Unsecure request.');
		if( !wp_verify_nonce( $_POST['afcnonce'], 'afc-editor-nonce' ) )
			die('Invalid nonce.');

		$afcaction = ( isset( $_POST['afcaction'] ) && trim( $_POST['afcaction'] ) != '' ) ? $_POST['afcaction'] : 0 ;
		$afcdata = ( isset( $_POST['afcdata'] ) && is_array( $_POST['afcdata'] ) ) ? $_POST['afcdata'] : 0 ;
		$afcselectors = new afcselectors();
		if( $afcaction ){
			switch ($afcaction){
                case 'logincheck':
                    echo 'afc Logged In';
                    break;
				case 'removeall': 
						$afcselectors->reset($afcdata);
						echo __( 'Operation Successfull !', 'afc_textdomain' );
					break;
				case 'add': 
					if( $afcdata ){
						$afcselectors->addelems($afcdata);
						echo __( 'Operation Successfull !', 'afc_textdomain' );
					}
					else 
						echo __( 'Operation Failed !', 'afc_textdomain' );
					break;
				case 'removethiselem': 
					if( $afcdata ){
						$afcselectors->updateElems( 'remove', $afcdata );
						echo __( 'Operation Successfull !', 'afc_textdomain' );
					}
					else 
						echo __( 'Operation Failed !', 'afc_textdomain' );
					break;
				case 'printeditorjs':	
					?>
					<script type='text/javascript' src="<?php echo ADVANCEDFONTCHANGERURL . 'js/font-edit.js'; ?>"></script>
					<?php
					break;
				case 'getelems':
					$returneddata = $afcselectors->getByPageType();
					if( count( $returneddata ) > 0 )
						echo json_encode( $returneddata );
					else 
						echo '0';
					break;
				default:
					echo __( 'Operation Failed !', 'afc_textdomain' );
					break;
			}
		}
	}
    else{
        echo 'afc Not Logged In';
        die();
    }
	die();
}
?>