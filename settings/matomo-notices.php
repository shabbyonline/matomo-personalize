<?php
/**
 * This page contains notices when form submits
 */
    function v8_notice_success($MESSAGE) {
?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( $MESSAGE, TEXT_DOMAIN ); ?></p>
    </div>
<?php
    }
    function v8_notice_error($MESSAGE) {
?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( $MESSAGE, TEXT_DOMAIN ); ?></p>
    </div>
<?php
    }
?>