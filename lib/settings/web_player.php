<?php
namespace Podlove\Settings;
use \Podlove\Model;

class WebPlayer {

	static $pagehook;
	
	public function __construct( $handle ) {
		
		WebPlayer::$pagehook = add_submenu_page(
			/* $parent_slug*/ $handle,
			/* $page_title */ 'Web Player',
			/* $menu_title */ 'Web Player',
			/* $capability */ 'administrator',
			/* $menu_slug  */ 'podlove_webplayer_settings_handle',
			/* $function   */ array( $this, 'page' )
		);

		register_setting( WebPlayer::$pagehook, 'podlove_webplayer_formats' );
		register_setting( WebPlayer::$pagehook, 'podlove_webplayer_settings' );
	}

	public function page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'podlove-podcast' ); ?>
			<h2><?php echo __( 'Web Player', 'podlove' ); ?></h2>

			<?php echo __( 'Webplayers are able to provide various media formats depending on context. Try to provide as many as possible to maximize compatibility with all browsers.', 'podlove' ); ?>


			<form method="post" action="options.php">
				<?php settings_fields( WebPlayer::$pagehook ); ?>

				<table class="form-table">
					<?php $this->form_fields(); ?>
				</table>
				
				<?php submit_button( __( 'Save Changes' ), 'button-primary', 'submit', TRUE ); ?>
			</form>

		</div>	
		<?php
	}

	public function form_fields() {

		$formats = array(
			'audio' => array(
				'mp3' => array(
					'title'     => __( 'MP3 Audio', 'podlove' ),
					'mime_type' => 'audio/mpeg'
				),
				'mp4' => array(
					'title'     => __( 'MP4 Audio', 'podlove' ),
					'mime_type' => 'audio/mp4'
				),
				'ogg' => array(
					'title'     => __( 'OGG Audio', 'podlove' ),
					'mime_type' => 'audio/ogg'
				),
				'opus' => array(
					'title'     => __( 'Opus Audio', 'podlove' ),
					'mime_type' => 'audio/opus'
				),
			),
			'video' => array(
				'mp4'  => array(
					'title'     => __( 'MP4 Video', 'podlove' ),
					'mime_type' => 'video/mp4'
				),
				'ogg'  => array(
					'title'     => __( 'OGG Video', 'podlove' ),
					'mime_type' => 'video/ogg'
				),
				'webm' => array(
					'title'     => __( 'Webm Video', 'podlove' ),
					'mime_type' => 'video/webm'
				),
			)
		);

		$formats_data = get_option( 'podlove_webplayer_formats', array() );
		$episode_assets = Model\EpisodeAsset::all();

		foreach ( $formats as $format => $extensions ) {
			?>
			<tr valign="top">
				<th scope="row" valign="top" colspan="2">
					<h3><?php echo ucfirst( $format ); ?></h3>
				</th>
			</tr>
			<?php
			foreach ( $extensions as $extension => $extension_data ) {
				$label = $extension_data['title'];
				$mime_type = $extension_data['mime_type'];

				$id    = sprintf( 'podlove_webplayer_formats_%s_%s'  , $format, $extension );
				$name  = sprintf( 'podlove_webplayer_formats[%s][%s]', $format, $extension );
				$value = ( isset( $formats_data[$format] ) && isset( $formats_data[$format][$extension] ) ) ? $formats_data[$format][$extension] : 0;
				?>
				<tr valign="top">
					<th scope="row" valign="top">
						<label for="<?php echo $id ?>"><?php echo $label; ?></label>
					</th>
					<td>
						<div>
							<select name="<?php echo $name; ?>" id="<?php echo $id; ?>">
								<option value="0" <?php selected( 0, $value ); ?> ><?php echo __( 'Unused', 'podlove' ); ?></option>
								<?php foreach ( $episode_assets as $episode_asset ): ?>
									<?php $file_type = $episode_asset->file_type(); ?>
									<?php if ( $file_type && $file_type->mime_type === $mime_type ): ?>
										<option value="<?php echo $episode_asset->id; ?>" <?php selected( $episode_asset->id, $value ); ?>><?php echo $episode_asset->title ?></option>
									<?php endif ?>
								<?php endforeach ?>
							</select>
						</div>
					</td>
				</tr>
				<?php 
			}
		}

		// advanced settings

		$settings = array(
			'chaptersVisible'     => array(
				'label'   => __( 'Chapters Visibility', 'podlove' ),
				'options' => array(
					'true' => __( 'Visible when player loads', 'podlove' ),
					'false' => __( 'Hidden when player loads', 'podlove' )
				)
			),
		);

		?>
		<tr valign="top">
			<th scope="row" valign="top" colspan="2">
				<h3><?php echo __( 'Settings', 'podlove' ); ?></h3>
			</th>
		</tr>
		<?php foreach ( $settings as $setting_key => $field_values ): ?>
			<tr class="row_<?php echo $setting_key; ?>">
				<th scope="row" valign="top">
					<?php if ( isset( $field_values['label'] ) && $field_values['label'] ): ?>
						<label for="<?php echo $setting_key; ?>"><?php echo $field_values['label']; ?></label>
					<?php endif ?>
				</th>
				<td>
					<select name="podlove_webplayer_settings[<?php echo $setting_key; ?>]" id="<?php echo $setting_key; ?>">
						<?php foreach ( $field_values['options'] as $key => $value ): ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php if ( $key == \Podlove\get_webplayer_setting( $setting_key ) ): ?> selected="selected"<?php endif; ?>><?php echo $value; ?></option>
						<?php endforeach; ?>
					</select>
					<?php if ( isset( $field_values['description'] ) &&  $field_values['description'] ): ?>
						<span class="description"><?php echo $field_values['description']; ?></span>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php
	}

}