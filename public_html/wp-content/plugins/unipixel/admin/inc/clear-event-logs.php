<?php
if (!defined('ABSPATH')) exit;

/**
 * Confirm modal for the "Clear Stored Event Logs" action. Rendered once in the
 * admin footer on the two pages that expose the button (Event Logs + General
 * Settings). The button markup itself lives inline in each of those pages; both
 * buttons share the `.js-unipixel-clear-logs` class that admin-common.js binds to.
 */
function unipixel_render_clear_logs_modal()
{
    ?>
    <div class="modal fade" id="unipixelClearLogsModal" tabindex="-1" aria-labelledby="unipixelClearLogsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unipixelClearLogsTitle"><?php echo esc_html__('Delete all stored logs?', 'unipixel'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'unipixel'); ?>"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0"><?php echo esc_html__('This deletes all the event records stored in your database. It will not affect your tracking or your settings.', 'unipixel'); ?></p>
                    <div id="unipixelClearLogsMsg" class="alert mt-3 mb-0 d-none" role="status"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="unipixelClearLogsDismiss" data-bs-dismiss="modal"><?php echo esc_html__('Cancel', 'unipixel'); ?></button>
                    <button type="button" class="btn btn-primary" id="unipixelClearLogsConfirm"><?php echo esc_html__('Delete all logs', 'unipixel'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render the modal in the footer only on the pages that expose the button.
 */
function unipixel_maybe_render_clear_logs_modal()
{
    $screen = get_current_screen();
    if (!$screen) return;
    if (strpos($screen->id, 'unipixel_general_settings') === false) {
        return;
    }
    unipixel_render_clear_logs_modal();
}
add_action('admin_footer', 'unipixel_maybe_render_clear_logs_modal');
