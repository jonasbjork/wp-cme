<?php
/*
	Admin part of Wordpress cMe plugin.
	Author: Jonas Björk, http://www.jonasbjork.net/
	License: GPL v2, with sole exeption that license does not hold.
*/

	$msgs = array(
		'cme_init' => array( 'class' => '', 'text' => 'cMe Plugin initialized' ),
		'settings_saved' => array( 'class' => '', 'text' => 'Your settings have been saved.' ),
		'drupal_saved' => array( 'class' => '', 'text' => 'Your Drupal account has been saved.'),
		'twitter_saved' => array( 'class' => '', 'text' => 'Your Twitter account has been saved.'),
		'facebook_saved' => array( 'class' => '', 'text' => 'Your Facebook account has been saved.'),
		'no_curl' => array( 'class' => '', 'text' => 'Curl extension is not loaded, this module will not work.')
	);

	// Check if curl extension is loaded in PHP
	if(!extension_loaded('curl')) {
		$ms = 'no_curl';
	}	

	// Check if a checkbox should be selected.
	function jb_checkBox($fieldName){
		if( get_option($fieldName) == '1' ){
			echo('checked="true"');
		}
	}

	function jb_selected($name) {
		if( get_option('use-shortlink-service') == $name ) {
			echo(' selected="selected"');
		}
	}

	// Uncomment this to reset all settings.
	//update_option('cMe-init', '0');

	// Set all settings to default values
	if( get_option('cMeInit') != 1 ) {
		update_option('use-shortlinks', '0');
		update_option('use-shortlink-service', '');
		update_option('drupal-site-enable', '0');
		update_option('drupal-account-rpc', '');
		update_option('drupal-account-blogid', '');
		update_option('drupal-account-username', '');
		update_option('drupal-account-password', '');
		update_option('twitter-enable', '0');
		update_option('twitter-account-username', '');
		update_option('twitter-account-password', '');
		update_option('facebook-enable', '0');
		update_option('facebook-account-username', '');
		update_option('facebook-account-firstname', '');
		update_option('facebook-account-password', '');
		$ms = 'cme_init';
	}

	if( $_POST['submit-type'] == 'cMe-configuration' ) {
		update_option('drupal-site-enable', $_POST['drupal-site-enable']);
		update_option('twitter-enable', $_POST['twitter-enable']);
		update_option('facebook-enable', $_POST['facebook-enable']);
		update_option('use-shortlinks', $_POST['use-shortlinks']);
		update_option('use-shortlink-service', $_POST['use-shortlink-service']);
		update_option('cMeInit', '1');
		$ms = 'settings_saved';

	} else if( $_POST['submit-type'] == 'drupal-account' ) {
		update_option('drupal-account-rpc', $_POST['drupal-account-rpc']);
		update_option('drupal-account-blogid', $_POST['drupal-account-blogid']);
		update_option('drupal-account-username', $_POST['drupal-account-username']);
		update_option('drupal-account-password', $_POST['drupal-account-password']);
		$ms = 'drupal_saved';

	} else if( $_POST['submit-type'] == 'twitter-account' ) {
		update_option('twitter-account-username', $_POST['twitter-account-username']);
		update_option('twitter-account-password', $_POST['twitter-account-password']);
		$ms = 'twitter_saved';
	} else if( $_POST['submit-type'] == 'facebook-account' ) {
		update_option('facebook-account-username', $_POST['facebook-account-username']);
		update_option('facebook-account-password', $_POST['facebook-account-password']);
		update_option('facebook-account-firstname', $_POST['facebook-account-firstname']);
		$ms = 'facebook_saved';
	}

?>
<style type="text/css">
 fieldset {
	margin: 20px 0;
	border: 1px solid #cecece;
	padding: 15px;
 }
</style>

<?php if( isset($ms) ) { ?>
<div id="message" class="updated fade">
	<p><strong><?php echo $msgs[$ms]['text']; ?></strong></p>
</div>
<?php } ?>

<div class="wrap">
  <h2>Wordpress cMe Configuration</h2>


  <form method="post">
	<input type="hidden" name="submit-type" value="cMe-configuration" />
	<div>
		<fieldset>
			<legend>Post to drupalsite</legend>
			<p>
				<input type="checkbox" name="drupal-site-enable" id="drupal-site-enable" value="1" <?php echo jb_checkBox('drupal-site-enable'); ?> />
				<label for="drupal-site-enable">Use Wordpress to Drupal posting?</label>
			</p>
			<p>
				<input type="checkbox" name="twitter-enable" id="twitter-enable" value="1" <?php echo jb_checkBox('twitter-enable'); ?>/>
				<label for="twitter-enable">Use posting to Twitter?</label>
			</p>
			<p>
				<input type="checkbox" name="facebook-enable" id="facebook-enable" value="1" <?php echo jb_checkBox('facebook-enable'); ?>/>
				<label for="facebook-enable">Update status on Facebook when posting new blogs?</label>
			</p>
			<p>
				<input type="checkbox" name="use-shortlinks" id="use-shortlinks" value="1" <?php echo jb_checkBox('use-shortlinks'); ?>/>
				<label for="use-shortlinks">Use shorter URL:s when posting?</label>
			</p>
			<p style="margin-left: 4em">
				<select name="use-shortlink-service" id="use-shortlink-service">
					<option value="zz" <?php jb_selected('zz'); ?>>zz.gd</option>
					<option value="tinyurl" <?php jb_selected('tinyurl'); ?>>tinyurl.com</option>
				</select>
				<label for="use-shortlink-service">Use this shortlink service.</label>
			</p>
		</fieldset>
		<p><input type="submit" name="cMe-configuration-submit" id="cMe-configuration-submit" value="Update settings" /></p>
	</div>
	</form>
</div>

<div class="wrap">
	<h2>Your Drupal account details</h2>
	<p style="color: red">Please note: your account details are stored uncrypted in your Wordpress database!</p>

	<form method="post">
	<input type="hidden" name="submit-type" value="drupal-account" />
	<div>
	<p>
		<label for="drupal-account-rpc">URL to Drupal XML-RPC:</label>
		<input type="text" name="drupal-account-rpc" id="drupal-account-rpc" value="<?php echo (get_option('drupal-account-rpc')); ?>" />
	</p>
	<p>
		<label for="drupal-account-blogid">Your Drupal blog id:</label>
		<input type="text" name="drupal-account-blogid" id="drupal-account-blogid" value="<?php echo (get_option('drupal-account-blogid')); ?>"/>
	</p>
	<p>
		<label for="drupal-account-username">Your Drupal username:</label>
		<input type="text" name="drupal-account-username" id="drupal-account-username" value="<?php echo get_option('drupal-account-username'); ?>"/>
	</p>
	<p>
		<label for="drupal-account-password">Your Drupal password:</label>
		<input type="password" name="drupal-account-password" id="drupal-account-password" value="<?php echo get_option('drupal-account-password'); ?>"/>
	</p>
	
	<p><input type="submit" name="drual-account-submit" id="drupal-account-submit" value="Save login" /></p>

	</div>
	</form>

</div>

<div class="wrap">
	<h2>Your Twitter account details</h2>
	<p style="color: red">Please note: your account details are stored uncrypted in your Wordpress database!</p>

	<form method="post">
	<input type="hidden" name="submit-type" value="twitter-account" />
	<div>
	<p>
		<label for="twitter-account-username">Your email adress registered at Twitter:</label>
		<input type="text" name="twitter-account-username" id="twitter-account-username" value="<?php echo get_option('twitter-account-username'); ?>" />
	</p>
	<p>
		<label for="twitter-account-password">Your Twitter password:</label>
		<input type="password" name="twitter-account-password" id="twitter-account-password" value="<?php echo get_option('twitter-account-password'); ?>" />
	</p>
	<p><input type="submit" name="submit" value="Save login" /></p>
	</div>
	</form>
</div>

<div class="wrap">
	<h2>Your Facebook account details</h2>
	<p style="color: red">Please note: your account details are stored uncrypted in your Wordpress database!</p>

	<form method="post">
	<input type="hidden" name="submit-type" value="facebook-account" />
	<div>
	<p>
		<label for="facebook-account-username">Your email adress registered at Facebook:</label>
		<input type="text" name="facebook-account-username" id="facebook-account-username" value="<?php echo get_option('facebook-account-username'); ?>" />
	</p>
	<p>
		<label for="facebook-account-firstname">Your first name at Facebook:</label>
		<input type="text" name="facebook-account-firstname" id="facebook-account-firstname" value="<?php echo get_option('facebook-account-firstname'); ?>" />
	</p>
	<p>
		<label for="facebook-account-password">Your Facebook password:</label>
		<input type="password" name="facebook-account-password" id="facebook-account-password" value="<?php echo get_option('facebook-account-password'); ?>" />
	</p>
	<p><input type="submit" name="submit" value="Save login" /></p>
	</div>
	</form>
</div>


