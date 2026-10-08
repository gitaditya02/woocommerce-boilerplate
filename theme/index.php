<?php
/**
 * Main template file
 */

get_header();
?>

<main id="main" class="site-main">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header>
                <div class="entry-content">
                    <?php
                    the_content(
                        sprintf(
                            wp_kses(
                                /* translators: %s: Name of current post. Only visible to screen readers */
                                __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'wc-boilerplate-theme'),
                                array('span' => array('class' => array()))
                            ),
                            wp_kses_post(get_the_title())
                        )
                    );
                    ?>
                </div>
            </article>
            <?php
        }
    } else {
        ?>
        <p><?php esc_html_e('No posts found.', 'wc-boilerplate-theme'); ?></p>
        <?php
    }
    ?>
</main>

<?php
get_footer();
