<?php
/*
Plugin Name: Vespar Update
Description: update private plugins and themes as simple as possible.
Plugin URI: https://vespar.ir
Author: Hoomaan Hormozdi
Author URI: https://github.com/Hormozdi
Version: 1.0.0
Text Domain: VesparUpdater
Vespar Slug: 5d20691972584
*/

register_activation_hook( __FILE__, 'vesparUpdaterActivation' );

function vesparUpdaterActivation() {
	if ( ! wp_next_scheduled ( 'vesparUpdaterDailyEvent' ) ) {
		wp_schedule_event(time(), 'daily', 'vesparUpdaterDailyEvent');
	}
}

require_once 'private.txt';

class VesparUpdater
{
	private $vesparTokenSlug  = 'vespar_token';
	private $vesparCheckToken = 'vespar_check_token';
	private $vesparToken      = null;

	public function __construct() {
		$this->vesparToken = esc_attr( get_option( $this->vesparTokenSlug ) );
		add_action ( 'after_setup_theme'      , [ $this, 'load_plugin_textdomain'   ] );
		add_action ( 'admin_menu'             , [ $this, 'vesparUpdaterOptionsPage' ] );
		add_action ( 'admin_notices'          , [ $this,'adminNotice'               ] );
	}

	public function adminNotice() {
		if ( ! get_option( $this->vesparCheckToken, false ) ) {
			ob_start();
			?>
			<div class="notice notice-error">
				<p><?php _e( 'Your vespar token is wrong', 'VesparUpdater' ); ?></p>
			</div>
			<?php
			$output = ob_get_clean();
			echo $output;
		}
	}

	public function vesparUpdaterOptionsPage()	{
		add_options_page( __('Vespar Updater', 'VesparUpdater'), __('Vespar Updater', 'VesparUpdater'), 'manage_options', 'vesparupdater', [ $this, 'vesparUpdaterOptionPage']);
		add_action      ( 'admin_init', [ $this, 'registerVesparUpdaterSettings' ] );
	}

	public function registerVesparUpdaterSettings() {
		if ( isset( $_POST[$this->vesparTokenSlug] ) ) {
			$status = true;
			if( $GLOBALS['PrivateVesparUpdater']->VesparCheckToken( $_POST[$this->vesparTokenSlug] ) ) {
				$GLOBALS['PrivateVesparUpdater']->vesparUpdaterCheckInfo( $_POST[$this->vesparTokenSlug] );
			} else {
				$status = false;
			}
			update_option( $this->vesparCheckToken, $status );
		}
		register_setting( 'vespar_updater_options_group', $this->vesparTokenSlug );
	}

	public function vesparUpdaterOptionPage() {
		ob_start();
		?>
		<div>
			<h2><?php _e('Vespar Updater', 'VesparUpdater') ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'vespar_updater_options_group' ); ?>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="<?=$this->vesparTokenSlug ?>"><?php _e('Vespar Token', 'VesparUpdater') ?></label></th>
							<td>
								<textarea style="direction:ltr; width:100%" id="<?=$this->vesparTokenSlug ?>" name="<?=$this->vesparTokenSlug ?>" rows="3"><?php echo $this->vesparToken; ?></textarea>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
		$output = ob_get_clean();
		echo $output;
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'VesparUpdater', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
	}
}

new VesparUpdater();