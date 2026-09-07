<!-- Title of your blog post -->
    <meta property="og:title" content="<?php if(!empty($blog->title)){ echo $blog->title; } ?>" />
    <meta property="og:description" content="<?php if(!empty($blog->title)){ echo $blog->title; } ?>" />
    <meta property="og:url" content="https://bestefar.com" />
    <!--<meta property="og:image" content="<?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog->c_image; ?>" />-->
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="bestefar.com" />
    <meta property="article:author" content="bestefar.com" />
<style>

.ds_f{
	font-family: sans, sans-serif !important;
    font-size: 16px;
    line-height: 25.6px;
	margin-top: 25px;
}
.caption{
	font-family: arial;
    font-size: 14px;
    line-height: 16.6px;
    margin-top: 10px;
    color: gray;
}
.h1, .h2, .h3, h1, h2, h3 {
	margin-top: 0px;
}
p{
    line-height: 28px;
    margin: 0px !important;
    /*text-align:justify !important;*/
}

hr{
    border-top: 1px solid #808080 !important;
}

/* Force all content images to stay within the container */
.blog_desc img,
.blog_desc iframe,
.blog_desc video {
    max-width: 100% !important;
    height: auto !important;
    width: auto !important;
}
/* Tables must stay within container but keep their own width */
.blog_desc table {
    max-width: 100% !important;
    table-layout: fixed;
    word-wrap: break-word;
    box-sizing: border-box;
}

</style>

<style>
    h1 {
        font-size: 36pt !important;
    }
    .blog_desc h2 {
        font-size: 18pt !important;
        line-height: 1.1 !important;
    }
    .blog_desc h3 {
        font-size: 14pt !important;
        line-height: 1.1 !important;
        font-weight:bold !important;
    }
    .blog_desc p {
        font-size: 14pt !important;
        line-height: 1.5 !important;
    }
    .video-placeholder {
        cursor: pointer;
        position: relative;
        display: inline-block;
    }
    .video-placeholder img {
        width: 100%; /* Make it responsive */
    }
    .video-placeholder .play-button {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 64px; /* Adjust to your preferred size */
        height: 64px; /* Adjust to your preferred size */
        background: url('<?php echo base_url();?>assets/frontend/images/playbutton.png') no-repeat center center;
        background-size: contain;
    }
    ol {
    list-style-type: decimal;
    padding-left: 20px;
    margin: 20px 0;
    font-family: Georgia, serif;
    font-size: 18px;
}

ol li {
    margin-bottom: 10px;
}
ul {
    list-style-type: disc;
    padding-left: 20px;
    margin: 20px 0;
    font-family: Georgia, serif;
    font-size: 18px;
}

ul li {
    margin-bottom: 10px;
}
/*.play-button, .control-button {*/
/*    position: absolute;*/
/*    top: 100%;*/
/*    left: 50%;*/
/*    transform: translate(-50%, -50%);*/
/*    background-color: rgba(255, 0, 0, 0.6);*/
/*    color: white;*/
/*    padding: 10px 20px;*/
/*    border-radius: 5px;*/
/*    font-size: 16px;*/
/*    cursor: pointer;*/
/*    text-align: center;*/
/*}*/

/*.control-button {*/
/*    position: absolute;*/
    top: 100%; /* Adjust as needed */
    right: 10px; /* Adjust as needed */
/*}*/
</style>

<?php 

if($blog->background_color == 1){
?>
<style>
    body{
        background: black !important;
        color: white !important;
    }
</style>
<?php }elseif($blog->background_color == 2){?>
<style>
    body{
        background: #9DC3E7 !important;
        color: black !important;
    }
</style>
<?php }elseif($blog->background_color == 3){?>
<style>
    body{
        background: #FFBF01 !important;
        color: black !important;
    }
</style>
<?php }?>
	<div id="content" class="site-content">
		<div id="content" class="elementor elementor-16 elementor-14">
			<div id="content" class="elementor-section elementor-top-section elementor-element elementor-element-60622e5 elementor-section-boxed elementor-section-height-default elementor-section-height-default">
				<div class="elementor-container elementor-column-gap-default">
				 <!------------>


	<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-c9a3d65" data-id="c9a3d65" data-element_type="column" style="margin-top:5px;">
		<div class="elementor-widget-wrap elementor-element-populated">

			<section class="elementor-section elementor-inner-section elementor-element elementor-element-a473ad5 elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="a473ad5" data-element_type="section">
				<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-100 elementor-inner-column elementor-element elementor-element-bcd4cfc" data-id="bcd4cfc" data-element_type="column">
						<div class="elementor-widget-wrap">

							<div id="blog-content-wrapper">

								<?php if($blog->cover_url){ ?>
								<a href="<?php echo $blog->cover_url; ?>"><img src="<?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog->c_image; ?>" class="img-responsive" style="width:100%;display:block;"></a>
								<?php } else { ?>
								<img src="<?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog->c_image; ?>" class="img-responsive" style="width:100%;display:block;">
								<?php } ?>

								<h5 class="caption"><?php if(!empty($blog->caption)){ echo $blog->caption; } ?></h5>

								<?php if($blog->author_date == 1){
									$months = [1=>'januar','februar','mars','april','mai','juni','juli','august','september','oktober','november','desember'];
									$day   = date('j', strtotime($blog->date));
									$month = $months[date('n', strtotime($blog->date))];
									$year  = date('Y', strtotime($blog->date));
								?>
								<h5 class="ds_f"><?php echo $blog->writer; ?>, <?php echo "{$day}. {$month}, {$year}"; ?></h5>
								<?php } ?>

								<div class="blog_desc" style="font-family:agency_fbregular;font-size:large;">
									<?php echo $blog->description; ?>
								</div>
								<script>
								document.querySelectorAll('.blog_desc img').forEach(function(img) {
									img.addEventListener('error', function() { this.style.display = 'none'; });
									if (img.complete && img.naturalWidth === 0) { img.style.display = 'none'; }
								});
								</script>

							</div><!-- /blog-content-wrapper -->
						</div>
					</div>
				</div>
			</section>

		</div>
	</div>
	
	
	<!------------>
				</div>
			</div>
		</div>
	</div><!-- #content -->
	
	<script>
 document.addEventListener('DOMContentLoaded', function() {
    // Fetch images from the API
    fetch('<?php echo base_url()."ajax/get_video_images/?id=".$blog->blogs_id; ?>')
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                var images = data;
                var videoElements = document.querySelectorAll('.video-source');
                console.log(images);
                videoElements.forEach(function(video, index) {
                    if (index < images.length) {  // Ensure there's an image for this video
                        var videoSrc = video.querySelector('source').getAttribute('src');

                        var placeholder = document.createElement('div');
                        placeholder.className = 'video-placeholder';
                        placeholder.setAttribute('data-video-src', videoSrc);

                        var img = document.createElement('img');
                        img.src = '<?php echo base_url();?>uploads/admin/blogs/' + images[index]; // Use image from the API
                        img.alt = 'Play Video';

                        var playButton = document.createElement('div');
                        playButton.className = 'play-button';

                        placeholder.appendChild(img);
                        placeholder.appendChild(playButton);

                        video.parentNode.replaceChild(placeholder, video);

                        placeholder.addEventListener('click', function() {
                            var videoElement = document.createElement('video');
                            videoElement.setAttribute('src', videoSrc);
                            videoElement.setAttribute('controls', 'controls');
                            videoElement.setAttribute('autoplay', 'autoplay');

                            // Apply dimensions of the image to the video
                            videoElement.style.width = img.clientWidth + 'px';
                            videoElement.style.height = img.clientHeight + 'px';

                            // Add a play/pause button overlay on the video
                            var controlButton = document.createElement('div');
                            controlButton.className = 'control-button';
                            // controlButton.innerText = 'Pause';

                            // videoElement.addEventListener('play', function() {
                            //     controlButton.innerText = 'Pause';
                            // });

                            // videoElement.addEventListener('pause', function() {
                            //     controlButton.innerText = 'Play';
                            // });

                            controlButton.addEventListener('click', function() {
                                if (videoElement.paused) {
                                    videoElement.play();
                                } else {
                                    videoElement.pause();
                                }
                            });

                            placeholder.parentNode.replaceChild(videoElement, placeholder);
                            videoElement.parentNode.insertBefore(controlButton, videoElement.nextSibling);

                            // Automatically replay the video after it ends
                            videoElement.addEventListener('ended', function() {
                                videoElement.currentTime = 0;
                                videoElement.play();
                            });
                        });
                    }
                });
            } else {
                console.error('Invalid data format:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching images:', error);
        });
});



document.querySelectorAll('div').forEach(function(div) {
    if (div.outerHTML.trim() === '<div>&nbsp;</div>') {
        div.remove();
    }
});

	</script>