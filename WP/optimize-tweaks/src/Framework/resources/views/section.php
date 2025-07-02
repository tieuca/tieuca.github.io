<?php
$style = $section->get_condition_attribute() ? 'style="display:none;"' : ''; ?>
<section id="<?php echo esc_attr($section->tab->slug . '-' . $section->slug); ?>" class="tab-content" <?php echo $section->get_condition_attribute(); ?> <?php echo $style; ?>>
<?php 
    if (count($section->tab->sections) <= 1): 
    ?>
    <div class="title">
        <?php if ($section->title) { ?>
            <h3><?php echo wp_kses_post($section->title); ?></h3>
        <?php } ?>
        <?php if ($section->description) { ?>
            <p><?php echo wp_kses_post($section->description); ?></p>
        <?php } ?>
    </div>
    <?php endif; ?>

    <table class="form-table striped">
        <tbody>
        <?php foreach ($section->options as $option) { ?>
            <?php echo $option->render(); ?>
        <?php } ?>
        </tbody>
    </table>
</section>
