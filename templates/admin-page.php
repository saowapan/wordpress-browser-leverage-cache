<?php

	lbc_save_options();

?>

<div class="wrap">

	<?php if(!lbc_is_writable_htaccess()) { ?>

		<h2>.htaccess NOT writable!</h2>

		<p>Sorry, your .htaccess is not writable. Please make sure <?php echo ABSPATH . '.htaccess'; ?> is writable!</p>
		<p>Need help making your .htaccess writable? <a href="www.saowapan.com/contact">contact us</a> or post on the WordPress Forums!</p>

	<?php } else { ?>

		<h2>Leverage Browser Cache</h2>
		<form method="post">

			<fieldset>

				<label for="hours">Expires in hours</label>

				<select name="hours">

					<?php 
						$hours = get_option('lbc_hours');
						for ($i=0; $i<=24; $i++) {
							if($hours == $i) {
								echo '<option value="' . $i . '" selected>' . $i . '</option>';
							} else {
								echo '<option value="' . $i . '">' . $i . '</option>';
							}
						}
					?>

				</select>

			</fieldset>

			<fieldset>

				<label for="days">Expires in days</label>

				<select name="days">

					<?php 
						$days = get_option('lbc_days');
						for ($i=0; $i<=30; $i++) {
							if($days == $i) {
								echo '<option value="' . $i . '" selected>' . $i . '</option>';
							} else {
								echo '<option value="' . $i . '">' . $i . '</option>';
							}
						}
					?>

				</select>

			</fieldset>

			<fieldset>

				<label for="months">Expires in months</label>

				<select name="months">

					<?php 
						$months = get_option('lbc_months');
						for ($i=0; $i<=12; $i++) {
							if($months == $i) {
								echo '<option value="' . $i . '" selected>' . $i . '</option>';
							} else {
								echo '<option value="' . $i . '">' . $i . '</option>';
							}
						}
					?>

				</select>

			</fieldset>

			<fieldset>

				<?php wp_nonce_field('browser_nonce', 'browser_nonce_field'); ?>
				<input type="hidden" name="submitted" id="submitted" value="true" />
				<button type="submit"><?php _e('Update Cache', 'wpp') ?></button>

			</fieldset>

		</form>

	<?php } ?>
	
</div>