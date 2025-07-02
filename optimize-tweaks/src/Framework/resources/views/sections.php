<?php
/**
 * Template để hiển thị các section và các nút điều khiển chính.
 *
 * @package     AZ_Settings
 * @subpackage  Framework/Views
 * @since       1.0.0
 */

use TieuCA\WPSettings\Helpers; ?>

<div id="poststuff">
    <div id="post-body" class="<?php echo (!$settings->get_sidebar()) ?: 'columns-2'; ?>">
        <div id="post-body-content">
            <form id="<?php echo $settings->slug; ?>-form" method="post" action="<?php echo $settings->get_full_url(); ?>">
                <?php Helpers::view('section-menu', compact('settings')); ?>            
                <div class="nav-tab-content">
                    <?php foreach ($settings->get_active_tab()->get_active_sections() as $section) { ?>
                        <?php Helpers::view('section', compact('section')); ?>
                    <?php } ?>
                </div>
                
                <?php wp_nonce_field('wp_settings_save_' . $settings->option_name, '_wpnonce');
                $active_tab = $settings->get_active_tab();
                
                if ($active_tab && $active_tab->show_submit_buttons) :
                ?>
                    <div class="components-panel__row">
                        <button type="submit" name="submit" id="submit" class="components-button is-primary">
                            <?php _e('Save'); ?>
                        </button>
                        <button type="button" id="azs-restore-button" class="components-button is-secondary"><?php _e('Restore'); ?></button>
                    </div>
                <?php endif; ?>
            </form>
            
            <div class="fixed-save-button">
                <?php
                if ($active_tab && $active_tab->show_submit_buttons) : ?>
                    <button type="submit" name="submit" id="<?php echo $settings->slug . '-fixed-submit'; ?>" class="components-button is-primary">
                        <?php _e('Save'); ?>
                    </button>
                <?php endif; ?>
            </div>
            
        </div>
        <?php if ($sidebars = $settings->get_sidebar()) { ?>
            <div id="postbox-container-1" class="postbox-container sidebar">
                <?php foreach ($sidebars as $sidebar) { ?>
                    <div class="postbox">
                        <h3 class="postbox-header hndle" style="border-color: #EFEFEF"><?php echo $sidebar['title']; ?></h3>
                        <div class="inside"><?php echo $sidebar['message']; ?></div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#azs-restore-button').on('click', function(e) {
            e.preventDefault();
            const $button = $(this);

            showNotify( azs_i18n.restoreConfirm, 'confirm', {}, 'sweetalert2', azs_i18n.areYouSure).then((confirmed) => {
                if (confirmed) {
                    $button.addClass('is-busy').prop('disabled', true);

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: '<?php echo esc_js($settings->slug . '_restore_settings'); ?>',
                            nonce: '<?php echo esc_js(wp_create_nonce('azs_restore_nonce')); ?>',
                            tab: '<?php echo esc_js($settings->get_active_tab()->slug); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                showNotify(response.data.message, 'success', { timeout: 2000 }, 'izitoast');
                                setTimeout(function() {
                                    window.location.href = response.data.redirect_url;
                                }, 2000);
                            } else {
                                showNotify(response.data.message || 'An error occurred.', 'error', {}, 'izitoast');
                                $button.removeClass('is-busy').prop('disabled', false);
                            }
                        },
                        error: function() {
                            showNotify(azs_i18n.ajaxFailed, 'error', {}, 'izitoast');
                            $button.removeClass('is-busy').prop('disabled', false);
                        }
                    });
                }
            });
        });
    });
</script>
