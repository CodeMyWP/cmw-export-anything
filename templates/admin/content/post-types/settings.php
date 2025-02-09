<?php
$settings = get_option( 'cmw-export-anything-settings' );
?>

<form method="post" action="options.php">
  <?php settings_fields( 'cmw-export-anything-settings-group' ); ?>
  <?php do_settings_sections( 'cmw-export-anything-settings' ); ?>
  <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
</form>
