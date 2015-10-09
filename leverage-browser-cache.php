<?php


/*
Plugin Name: Leverage Browser Cache
Plugin URI: http://wordpress.org/plugins/leverage-browser-caching-ninja/
Description: Quickly (and easily) add custom leverage browser caching to your website!
Version: 1.0
Author: mattg123
Author URI: http://www.saowapan.com/about
Tested up to: 4.1
*/

register_activation_hook( __FILE__, 'lbc_install' );
register_deactivation_hook( __FILE__, 'lbc_uninstall' );

function lbc_install() {
	lbc_make_htaccess();
}

function lbc_uninstall() {
	//use our backup_uninstall .htaccess!
	if(lbc_is_writable_html()) {
		if(file_exists(ABSPATH . '.htaccess') && file_exists(ABSPATH . 'leverage-browser-cache-uninstall.htaccess')) {
			if(!unlink(ABSPATH . '.htaccess')) {
				//opps something went wrong, we certainly don't want to delete our backup-uninstall now!
				return false;
			} else {
				if(!copy(ABSPATH . 'leverage-browser-cache-uninstall.htaccess', ABSPATH . '.htaccess')) {
					//we didn't copy, dont delete backup-uninstall!
					return false;
				} else {
					//finally we can clear our backups as we've created our new .htaccess!
					if(file_exists(ABSPATH . 'leverage-browser-cache.htaccess')) {
						unlink(ABSPATH . 'leverage-browser-cache-uninstall.htaccess');
						unlink(ABSPATH . 'leverage-browser-cache.htaccess');
					}
				}
			}
		}
	} else {
		add_action('admin_notices', 'lbc_uninstall_error');
	}
}

function lbc_make_htaccess() {

	if(lbc_is_writable_html()) {

		//first things first, make an uninstall backup!

		$backup_uninstall = 'leverage-browser-cache-uninstall.htaccess';
		if(!file_exists(ABSPATH . $backup_uninstall)) {
			if( copy(ABSPATH . '.htaccess', ABSPATH . $backup_uninstall)) {
				//something went horribly wrong and we do not want to continue!
				return false;
			}
		}

		//make a backup to edit and replace the current htaccess!
		$backup_name = 'leverage-browser-cache.htaccess';
		if(!file_exists(ABSPATH . $backup_name)) {
			if(copy ( ABSPATH . '.htaccess' , ABSPATH . $backup_name )) {
				return false;
			}
		}

		//make sure the htaccess is editable!
		if(lbc_is_writable_htaccess()) {
			//it is writable do some more checks
			if(!unlink( ABSPATH . '.htaccess')) { //remove the current .htaccess 
				return false;
			}
			//finally make this our current .htaccess
			if(!file_exists(ABSPATH . '.htaccess')) { //copy our backup as the new .htaccess
				if(!copy(ABSPATH . 'leverage-browser-cache.htaccess', ABSPATH . '.htaccess')) {
					return false;
				}
			}
			//finally write to our .htaccess
			lbc_write_htaccess();


		} else {
			//its not, lets let the user know
			add_action('admin_notices', 'lbc_htaccess_error');
		}
		
		
	} else {
		add_action('admin_notices', 'lbc_html_error');
	}

}

function lbc_write_htaccess() {
	$open = fopen(ABSPATH . '.htaccess', 'a');
	$total_time = 'plus ';

	if(!empty(get_option('lbc_months') || !get_option('lbc_months') === 0 )) {

		$months = get_option('lbc_months');
		if ($months === 1) {
			$months = $months . ' month ';
			$total_time .= $months;
		} elseif ($months >=2) {
			$months = $months . ' months ';
			$total_time .= $months;
		}
	}

	if(!empty(get_option('lbc_days') || !get_option('lbc_days') === 0 )) {

		$days = get_option('lbc_days');
		if ($days === 1) {
			$days = $days . ' day ';
			$total_time .= $days;
		} elseif ($days >=2) {
			$days = $days . ' days ';
			$total_time .= $days;
		}
	}

	if(!empty(get_option('lbc_hours') || !get_option('lbc_hours') === 0 )) {

		$hours = get_option('lbc_hours');

		if ($hours === 1) {
			$hours = $hours . ' hour ';
			$total_time .= $hours;
		} elseif ($hours >=2) {
			$hours = $hours . ' hours ';
			$total_time .= $hours;
		}
	}

	if($total_time == 'plus ') {
		$total_time = 'plus 1 month';
	}

	$total_time = trim($total_time);

	$wb = 1;
	if (flock($open, LOCK_EX, $wb)) {  // acquire an exclusive lock
		fwrite($open, "\n"); //make sure we're on a new line
		fwrite($open, "# Leverage Browser Cache -- Start DO NOT WRITE BETWEEN START AND END!\n");
		fwrite($open, "<IfModule mod_expires.c>\n");
		fwrite($open, "ExpiresActive On \n");
		fwrite($open, "ExpiresDefault \"access $total_time\" \n");
		fwrite($open, "ExpiresByType image/x-icon \"access $total_time\" \n");
		fwrite($open, "ExpiresByType image/gif \"access $total_time\" \n");
		fwrite($open, "ExpiresByType image/png \"access $total_time\" \n");
		fwrite($open, "ExpiresByType image/jpg \"access $total_time\" \n");
		fwrite($open, "ExpiresByType image/jpeg \"access $total_time\" \n");
		fwrite($open, "ExpiresByType text/css \"access $total_time\" \n");
		fwrite($open, "ExpiresByType application/javascript \"access $total_time\" \n");
		fwrite($open, "</IfModule> \n");
		fwrite($open, "# Leverage Browser Cache -- End DO NOT WRITE BETWEEN START AND END!\n");
		fflush($open);
		flock($open, LOCK_UN, $wb);    // release the lock
	}

}

add_action( 'admin_menu', 'lbc_admin_menu' );

function lbc_admin_menu() {

	add_menu_page( 'Leverage Browser Cache', 'Leverage Browser Cache', 'activate_plugins', 'leverage-browser-cache', 'lbc_page', 'dashicons-dashboard', 75);

}

function lbc_save_options() {

	if(isset( $_POST['browser_nonce_field'] ) && wp_verify_nonce( $_POST['browser_nonce_field'], 'browser_nonce' ) && isset($_POST['submitted'])) {
		if(isset($_POST['hours'])) {
			//hours option
			update_option('lbc_hours', $_POST['hours']);
		}
		if(isset($_POST['days'])) {
			//days option
			update_option('lbc_days', $_POST['days']);
		}
		if(isset($_POST['months'])) {
			//months option
			update_option('lbc_months', $_POST['months']);
		}

		lbc_make_htaccess();
	}

}

function lbc_html_error() {
	echo '<div class="updated">';
	echo 	'<p>Please make your html directory writable (chmod 777 /var/www/) to save a backup of your .htaccess!</p>';
	echo '</div>';
}

function lbc_htaccess_error() {
	echo '<div class="updated">';
	echo 	'<p>Please make your .htaccess is writable (chmod 777 /var/www/html/.htaccess) we cannot save your cache options otherwise!</p>';
	echo '</div>';	
}

function lbc_uninstall_error() {
	echo '<div class="updated">';
	echo 	'<p>While deactivating the plugin we were unable to backup you .htaccess!<br />
			    Do not worry, we have not deleted our backups to fix the issue please make the /var/www/html writable!<br/>
			    (chmod 777 /var/www/html/.htaccess) after you have done this, activate and deactivate the plugin to fix the problems!</p>';
	echo '</div>';	
}

function lbc_is_writable_htaccess() {

	//let user know .htaccess is not writable, so we cannot work!
	if(!is_writable(ABSPATH . '.htaccess')) {
		return false;
	} else {
		return true;
	}

}

function lbc_is_writable_html() {

	//html directory is not writable so we cannot make backups!
	if(!is_writable(ABSPATH)) {
		return false;
	} else {
		return true;
	}

}

function lbc_page() {
	//admin-page template
	require_once 'templates/admin-page.php';
}