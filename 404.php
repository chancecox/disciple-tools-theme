<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns" role="main">

                <article id="content-not-found">

                    <header class="article-header">
                        <h1><?php esc_html_e( 'Epic 404 - Not Found', 'disciple_tools' ); ?></h1>
                    </header> <!-- end article header -->

                    <section class="entry-content">
                        <p><?php esc_html_e( 'The article you were looking for was not found or was deleted', 'disciple_tools' ); ?></p>
                    </section> <!-- end article section -->

<!--                    <section class="search">-->
<!--                        <p>--><?php //get_search_form(); ?><!--</p>-->
<!--                    </section> <!-- end search section -->

                </article> <!-- end article -->

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
