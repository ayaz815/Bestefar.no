<style>
/* General Styles */
body {
    font-family: arial;
    font-size: 16px;
    line-height: 25.6px;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 25px;
}

.caption {
    font-family: Arial, sans-serif;
    font-size: 14px;
    line-height: 16.6px;
    margin-top: 10px;
    color: gray;
}

h1, h2, h3 {
    margin-top: 0;
}

p {
    line-height: 28px;
    margin: 0 !important;
}

/* Blog Description Styles */
.blog_desc h2 {
    font-size: 18pt !important;
    line-height: 1.1 !important;
}

.blog_desc h3 {
    font-size: 14pt !important;
    line-height: 1.1 !important;
    font-weight: bold !important;
}

.blog_desc p {
    font-size: 14pt !important;
    line-height: 1.5 !important;
}
</style>

<div id="content" class="site-content">
    <div class="elementor">
        <div class="elementor-section elementor-top-section">
            <div class="elementor-container">
                <div class="elementor-column elementor-col-100 elementor-top-column" style="margin-top: 5px;">
                    <div class="elementor-widget-wrap elementor-element-populated">
                        <section class="elementor-section elementor-inner-section elementor-section-full_width">
                            <div class="elementor-container">
                                <div class="elementor-column elementor-col-100 elementor-inner-column">
                                    <div class="elementor-widget-wrap">
                                        <div class="container">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <img src="<?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog->c_image; ?>" class="img-responsive" style="width: 100%; height: auto;">
                                                </div>
                                                <div class="col-md-12">
                                                    <h5 class="caption">
                                                       <?php if (!empty($blog->caption)) { echo $blog->caption; } ?>
                                                    </h5>
                                                    <h5 class="ds_f">
                                                        <?php echo $blog->writer; ?>,
                                                        <?php setlocale(LC_TIME, "norwegian"); ?>
                                                        <?php echo strftime('%e', strtotime($blog->date)); ?>.
                                                        <?php echo strftime('%B', strtotime($blog->date)); ?>,
                                                        <?php echo date("Y", strtotime($blog->date)); ?>
                                                    </h5>
                                                </div>
                                                <div class="col-md-12 blog_desc" style="font-family: 'Agency FB', sans-serif; font-size: large;">
                                                    <?php echo $blog->description; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
