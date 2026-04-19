<?php
// File: admin/page-console-logger.php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

function unipixel_page_console_logger()
{
?>
    <div class="UniPixelShell position-relative">

        <div class="UniPixelSpinner d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo esc_html__('Loading…', 'unipixel'); ?></span>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-start">
            <h1><i class="fa-solid fa-terminal"></i> Real Time Event Test Log</h1>
            <?php unipixel_render_feedback_buttons(); ?>
        </div>
        <p>
            This screen gives you live feedback on events to assist with testing, and to see which events fire with what data. To get started, 
            <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener noreferrer">
                Open your site in another window
            </a>, interact normally with that tab, and check for event logs here. This live logging is activated by being on this screen, 
            these event logs only show the events you trigger on your browser, they do not show events triggered by your site visitors. 
        </p>


        

        <hr/>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="unipixel-initiate-toggle">
            <label class="form-check-label" for="unipixel-initiate-toggle">
                Show <code>EVENT SETUP</code> Logs<br/>
                <small>Shows events that are setup to be listened for as triggers.</small>
            </label>
        </div>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="unipixel-send-toggle" checked disabled>
            <label class="form-check-label" for="unipixel-send-toggle">
                Show <code>EVENT SEND</code> Logs<br/>
                <small>Shows events as they are triggered (always on for this screen)</small>
            </label>
        </div>
        <hr/>

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-rotate fa-spin"></i>
                <span class="UniPixelListeningTxt">Listening for events...</span>
            </div>
            <button id="unipixel-clear-log" class="btn btn-primary"><i class="fa-solid fa-trash-can"></i> Clear Logs</button>
        </div>

        <div id="unipixel-console-log" style="
            margin-top:20px;
            padding:10px;
            background:#f7f7f7;
            border:1px solid #ddd;
            font-family:monospace;
            font-size:12px;
        "></div>
    </div>
<?php


}
