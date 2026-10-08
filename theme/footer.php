<?php
/**
 * Footer template
 */

?>

    <footer id="colophon" class="site-footer">
        <div class="site-info">
            <p>&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'wc-boilerplate-theme'); ?></p>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>
