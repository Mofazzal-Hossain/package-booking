<?php

if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<div class="tf-package-archive">
    <div class="tf-container">
        <h1><?php echo esc_html__('Packages', 'tourfic-package'); ?></h1>
        <div class="tf-package-list">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <div class="tf-package-item">
                        <h2><?php the_title(); ?></h2>
                        <a href="<?php echo esc_url(the_permalink());?>"><?php echo esc_html__('View package', 'tourfic-package'); ?></a>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php echo esc_html__('No packages found.', 'tourfic-package'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?> 