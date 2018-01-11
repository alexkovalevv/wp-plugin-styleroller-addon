<?php

function opanda_sr_style_styleroller_help() {
    ?>
    <div class="onp-help-section">
        <h1><?php _e('StyleRoller Add-On', 'styleroller'); ?></h1>
        <p>
            <?php _e('The StyleRoller adds a new option called "Style" for all the locker themes.', 'styleroller'); ?>
            <?php _e('Every theme has own set of styles. The "style" means a modified locker look based on one of the themes.', 'styleroller'); ?>
        </p>
    </div>
    <div class="onp-help-section">
        <h2><?php _e('How to create a new style?', 'styleroller'); ?></h2>
        <p>
            <?php _e('Open to edit any of your lockers. At the right of the dropdown list "Theme", you will find another dropdown list which contains only one item with the name "Default".', 'styleroller'); ?>
        </p>
        <p>
            <?php _e('Click the Plus button to run the StyleRoller and create a new style.', 'styleroller'); ?>
            <?php _e('When you create a new style for one of the themes, the StyleRoller clones an initial look of a selected theme.', 'styleroller'); ?>
        </p>
        <p class='onp-img'>
            <img src='<?php echo OPANDA_SR_PLUGIN_URL . '/assets/img/how-to-use/1.png' ?>' />
        </p>
        <p>
            <?php _e('View the short video below to get overal undestanding how the StyleRoller works:', 'styleroller'); ?>
        <p> 
        <p>
            <iframe src="http://www.screenr.com/embed/JohN" width="650" height="396" frameborder="0"></iframe>
        </p>
    </div>
    <div class="onp-help-section">
        <h2><?php _e('How to edit an exising style?', 'styleroller'); ?></h2>
        <p>
            <?php _e('When you have created and saved a new style, you can edit it at any time. Click the Pencil button to make that.', 'styleroller'); ?>
            <?php _e('Note that you cannot edit the Default styles in order to prevent overwriting initial looks.', 'styleroller'); ?>
            <p class='onp-img'>
                <img src='<?php echo OPANDA_SR_PLUGIN_URL . '/assets/img/how-to-use/2.png' ?>' />
            </p>
        </p>
    </div>
    <div class="onp-help-section">
        <h2><?php _e('Why does every theme have its own set of editable options?', 'styleroller'); ?></h2>
        <p>
            <?php _e('Yes, every of the themes has its own set of options. The first reason is that this way allows to simplify the process of editing styles and focus on the most important options of a given theme.', 'styleroller'); ?>
        </p>
        <p>
            <?php _e('The second reason is that we\'re going to release other themes in future. These themes will have non-standard look and feel. One of the future themes is the theme called "Blurred".', 'styleroller'); ?> 
            <?php _e('The one will overlap and blur the locked content.', 'styleroller'); ?>
        </p>
        <p>
            <?php _e('To keep editing easy and save an ability to add non-standard themes in the future, the StyleRoller offers different options to edit for each theme.', 'styleroller'); ?>
        </p>
    </div>
    <?php
}