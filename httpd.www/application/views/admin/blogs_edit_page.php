<link rel="stylesheet" href="<?php echo base_url();?>assets/font/Agency.ttf">
<link rel="stylesheet" href="<?php echo base_url();?>assets/font/ArchivoBlack.ttf">
<link rel="stylesheet" href="<?php echo base_url();?>assets/font/ArialBlack.ttf">

<style>
body{
    color: black !important;
}
.tbl_img{width:40px;}
@font-face {
        font-family: 'Agency';
        src: url('<?php echo base_url();?>assets/font/Agency.ttf') format('truetype');
    }
    @font-face {
    font-family: 'Archivo Black';
    src: url('<?php echo base_url();?>assets/fonts/ArchivoBlack.ttf') format('truetype');
}

@font-face {
    font-family: 'Arial Black';
    src: local('Arial Black'), local('Arial Black'), url('<?php echo base_url();?>assets/font/ArialBlack.ttf') format('truetype');
}
    .ck-editor__editable {
        font-family: 'Agency', sans-serif;
    }
</style>

<link rel="stylesheet" href="https://www.tiny.cloud/css/codepen.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="pcoded-content">
	<div class="pcoded-inner-content">
		<div class="main-body">
			<div class="page-wrapper">
				<!-- Page-header start -->
				<div class="page-header">
					<div class="row align-items-end">
						<div class="col-lg-8">
							<div class="page-header-title">
								<div class="d-inline">
									<h4><?php echo $page_title; ?></h4>
									<span><?php echo $page_sub_title; ?></span>
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="page-header-breadcrumb">
								<ul class="breadcrumb-title">
									<li class="breadcrumb-item">
										<a href="<?php echo base_url(); ?>admin"> <i class="fa fa-cogs"></i> </a>
									</li>
									<li class="breadcrumb-item"><a href="#!">Admin</a>
									</li>
									<li class="breadcrumb-item"><a href="#"><?php echo $page_title; ?></a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!-- Page-header end -->
				<div class="page-body">
					<div class="row"  id="print_div">
						<div class="card col-md-12">
							<div class="card-header">
								<h5>Blogs Details</h5>
								<div class="card-header-right">
									<ul class="list-unstyled card-option">
										<li><i class="feather icon-maximize full-card print_hide"></i></li>
										<li><i class="feather icon-minus minimize-card print_hide"></i></li>
										<li><i class="feather icon-trash-2 close-card print_hide"></i></li>
									</ul>
								</div>
								
								<hr>

							</div>
							<div class="card-block">
								<div class="dt-responsive table-responsive">
<?php 
		$blog = $this->Admin_model->get_row_id('blogs',$param2); 
		$categories 	= $this->Admin_model->get_data_conditions('categories', ' main_categories_id != 2')->result_array();
 ?>
<style>
.chk_box{ 
	margin-right: 15px;
    margin-top: 5px;
    margin-left: 5px;
}
#cke_1_contents{
	height: 600px !important;
}
</style>
<style> .row_div{ margin-top:20px; border-bottom: 1px solid #d3d3d352; } </style>
<div class="contentpanel">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<center><h3 class="panel-title">Edit Blog</h3></center><hr/>
				</div>
				<div class="panel-body">
					
					<div class="row ">
						
						<div class="col-md-12">
							<form method="POST" action="<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/blogs/update"  enctype="multipart/form-data">
								<div class="row">
									<div class="col-md-12">
									    <div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label">Blog status</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="icofont icofont-ui-check"></i></span>
													<select name="status" class="form-control" required>
													    <option <?php echo $blog->status == 'Draft' ? 'selected' : ''; ?> value="Draft">Draft</option>
													    <option <?php echo $blog->status == 'Active' ? 'selected' : ''; ?> value="Active">Active</option>
													</select>
												
												</div>
											</div>
										</div>
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label">Blog Title Lorum</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
													<input type="hidden" name="blogs_id" value="<?php echo $blog->blogs_id; ?>">
													<input type="text" name="title" class="form-control" placeholder="Enter Blog Title" required value="<?php echo $blog->title; ?>">
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label">Author Name</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-user"></i></span>
													<input type="text" name="writer" class="form-control" placeholder="Writer Name" required  value="<?php echo $blog->writer; ?>">
												</div>
											</div>
										</div>
										
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Category</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="icofont icofont-list"></i></span>
													<select  name="categories_id[]" id="categories_id" class="js-example-basic-single" required multiple  onchange="get_sub_categories();">
														<?php 
														$categories_ids = json_decode($blog->categories_id);
													
														if(!$categories_ids){
														  $categories_ids = ["0"];
														}
														foreach($categories as $cat){ ?>
															<option value="<?php echo $cat['categories_id']; ?>" <?php if(in_array($cat['categories_id'],$categories_ids)){ echo 'selected'; }?>> <?= $cat['name']; ?> </option>
														
														<?php } ?>
													</select>
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Sub Category</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="icofont icofont-list"></i></span>
													<select  name="sub_categories_id[]"  id="sub_categories_id" class="js-example-basic-single" multiple>
														<?php 
														$sub_categories_ids = json_decode($blog->sub_categories_id);
														if(!$sub_categories_ids){
														  $sub_categories_ids = ["0"];
														}
														$this->db->where_in('categories_id', $categories_ids);
                                                        $rows = $this->db->get('sub_categories')->result_array();
                                                		if($rows){
                                                		foreach($rows as $cat){ ?>
															<option value="<?php echo $cat['sub_categories_id']; ?>" <?php if(in_array($cat['sub_categories_id'],$sub_categories_ids)){ echo 'selected'; }?>> <?= $cat['name']; ?> </option>
														
														<?php }} ?>
													</select>
												</div>
											</div>
										</div>
										
										<!--
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Class Session</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon">!</span>
													<select  name="zee_class_sessions_id" class="form-control" required>
														<option disabled selected> Select </option>
														<option value="Male"> Male </option>
														<option value="Female"> Female </option>
													</select>
												</div>
											</div>
										</div>
										-->
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Feature Image</label>
											<div class="col-sm-8 col-lg-10">		
												<div class="input-group">
													<span class="input-group-addo"><img src="<?php echo base_url().'uploads/admin/blogs/'.$blog->f_image; ?>" style="height: 42px;"></span>
													<input type="file" name="f_image"  value="<?php echo $blog->f_image; ?>"  accept="image/*" class="form-control"  />
												
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Cover Image</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addo"><img src="<?php echo base_url().'uploads/admin/blogs/'.$blog->c_image; ?>" style="height: 42px;"></span>
													<input type="file"  accept="image/*" name="c_image"  value="<?php echo $blog->c_image; ?>" class="form-control"/>
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Cover Image Url</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<input type="text" name="cover_url"  value="<?php echo $blog->cover_url; ?>" class="form-control"/>
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Video Image </label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<input type="file"  accept="image/*" name="v_image[]" multiple  class="form-control"/>
													
												</div>
												<br>
												<span style="display:flex align-items:center">
												   
												<?php 
													    $v_images = json_decode($blog->v_image);
													    foreach($v_images as $image){?>
													    
													     <i class="fa fa-trash" aria-hidden="true" onclick="delete_v_image('<?php echo $blog->blogs_id;?>','<?php echo $image;?>')"></i>
													        <img src="<?php echo base_url().'uploads/admin/blogs/'.$image; ?>" style="height: 42px;">
													        <?php
													    }
													?>
													</span>
											</div>
											
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> </label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<input name="home_page" type="checkbox" id="d1" value="home_page" <?php if($blog->home_page == 1){ echo 'checked'; } ?>> 
													<label for="d1" class="chk_box"> Show on "Home Page"</label> 
													
													<input name="category_list" type="checkbox" id="d2" value="category_list" <?php if($blog->category_list == 1){ echo 'checked'; } ?>>
													<label for="d2" class="chk_box" > Keep the page also in category list.</label> 
													<input name="author_date" type="checkbox" id="d3" <?php if($blog->author_date == 1){ echo 'checked'; } ?>>
													<label for="d2" class="chk_box" > Show Author/Date.</label> 
												
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> </label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													
													<input name="background_color" type="checkbox" id="d1" value="1" <?php if($blog->background_color == 1){ echo 'checked'; } ?>> 
													<label for="d1" class="chk_box"> Black Background</label> 
													<input name="background_color" type="checkbox" id="d1" value="2" <?php if($blog->background_color == 2){ echo 'checked'; } ?>> 
													<label for="d1" class="chk_box"> Light Blue Background</label> 
													<input name="background_color" type="checkbox" id="d1" value="3" <?php if($blog->background_color == 3){ echo 'checked'; } ?>> 
													<label for="d1" class="chk_box"> Yellow Background</label>
												</div>
											</div>
										</div>
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label">Caption</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<textarea name="caption" rows="5" class="form-control"  placeholder="Enter the Caption"><?php echo $blog->caption; ?></textarea>
												</div>
											</div>
										</div>
										
										<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Shows</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
 														<select class="form-control" id="shows_id" onchange="get_image_url()">
 														    <option>Selection Show</option>
 														    <?php 
 														        if($shows){
 														            foreach($shows as $show){
 														                ?>
 														                <option value="<?php echo $show['shows_id']?>"><?php echo $show['name']?></option>
 														                <?php
 														            }
 														        }
 														    ?>
 														</select>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Shows Image Url</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-image"></i></span>
														<input type="text" id="image_url" readonly name="image_url" class="form-control"/>
													</div>
												</div>
											</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label">Description</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<textarea name="description" rows="5" class="form-control textarea" id="full-featured-non-premium" placeholder="Enter Blog Description"><?php echo $blog->description; ?></textarea>
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> </label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<button type="sunmit" class="btn btn-success">Update Blog</button>
												</div>
											</div>
										</div>
										
									</div>
								</div>
							</form>
						</div>
						
						
						
					</div>
				</div>
			</div>
		</div>
	</div>      
</div>
<!--<script src="https://cdn.tiny.cloud/1/nx39nrcfe8sd34zvoy95zq05pgv33s90oa0ub2ih9aaspks6/tinymce/6/tinymce.min.js"></script>-->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/frontend/js/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
  selector: 'textarea#full-featured-non-premium',
  plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
  automatic_uploads: true,
  images_upload_url: '<?php echo base_url("admin/tinymceUpload")?>',
  relative_urls : false,
remove_script_host : false,
convert_urls : true,
  file_picker_types: 'image media',
  imagetools_cors_hosts: ['picsum.photos'],
  menubar: 'file edit view insert format tools table help',
  toolbar: 'lineheightButton mainTitleButton sectionTitleButton paragraphButton captionButton undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
  toolbar_sticky: true,
  autosave_ask_before_unload: true,
  autosave_interval: "30s",
  autosave_prefix: "{path}{query}-{id}-",
  autosave_restore_when_empty: false,
  autosave_retention: "2m",
  font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 30pt 32pt 34pt 36pt 38pt 40pt 42pt 44pt 46pt 48pt',
  image_advtab: true,
  content_css: '//www.tiny.cloud/css/codepen.min.css',
  link_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_class_list: [
    { title: 'None', value: '' },
    { title: 'Some class', value: 'class-name' }
  ],
  importcss_append: true,
  setup: function (editor) {
       editor.ui.registry.addMenuButton('lineheightButton', {
      text: 'Line Height',
      fetch: function(callback) {
        var items = [
          { type: 'menuitem', text: '1', onAction: function() { setLineHeight(editor, '1'); } },
          { type: 'menuitem', text: '1.5', onAction: function() { setLineHeight(editor, '1.5'); } },
          { type: 'menuitem', text: '2', onAction: function() { setLineHeight(editor, '2'); } },
          { type: 'menuitem', text: '2.5', onAction: function() { setLineHeight(editor, '2.5'); } },
          { type: 'menuitem', text: '3', onAction: function() { setLineHeight(editor, '3'); } }
        ];
        callback(items);
      }
    });

    // Function to set the line height
     function setLineHeight(editor, value) {
      editor.formatter.register('lineheight', {
        inline: 'span',
        styles: { 'line-height': value }
      });
      editor.formatter.apply('lineheight');
    }
     // Add font weight dropdown to the toolbar
    // editor.ui.registry.addMenuButton('fontweight', {
    //   text: 'Font Weight',
    //   fetch: function (callback) {
    //     var items = [
    //       { type: 'menuitem', text: 'Normal', onAction: function () { applyFontWeight(editor, 'normal'); } },
    //       { type: 'menuitem', text: 'Bold', onAction: function () { applyFontWeight(editor, 'bold'); } },
    //       { type: 'menuitem', text: 'Bolder', onAction: function () { applyFontWeight(editor, 'bolder'); } },
    //       { type: 'menuitem', text: 'Light', onAction: function () { applyFontWeight(editor, 'lighter'); } }
    //     ];
    //     callback(items);
    //   }
    // });

    // // Define the custom command to apply font weight
    // function applyFontWeight(editor, weight) {
    //   editor.execCommand('mceApplyTextStyle', false, { 'font-weight': weight });
    // }
  
	// Add custom button to the toolbar
    editor.ui.registry.addButton('mainTitleButton', {
      text: 'Main Title',
      onAction: function () {
        editor.execCommand('mceInsertContent', false, '<p style="font-family:Georgia, Arial, sans-serif; font-size:36pt;color:black"><sub>Your main title content here...</sub></p>');
      }
    });
	editor.ui.registry.addButton('paragraphButton', {
      text: 'Paragraph',
      onAction: function () {
        editor.execCommand('mceInsertContent', false, '<div><b style="font-family:Georgia, Arial, sans-serif; font-size:14pt;color:black">Your paragraph title here...</b><br><p style="font-family:Georgia, Arial, sans-serif; font-size:14pt; line-height:1.5;text-align:justify;color:black">Your paragraph here...</p></div>');
      }
    });

	editor.ui.registry.addButton('sectionTitleButton', {
      text: 'Section Title',
      onAction: function () {
        editor.execCommand('mceInsertContent', false, '<p style="font-family:Georgia, Arial, sans-serif; font-size:18pt; line-height:1;color:black"><b>Your paragraph title content here...</b></p>');
      }
    });
    
    editor.ui.registry.addButton('captionButton', {
      text: 'Caption',
      onAction: function () {
        editor.execCommand('mceInsertContent', false, '<h6 style="font-family:Georgia, Arial, sans-serif; font-size:10pt; line-height:1.5;color:black">Your caption content here...</h6>');
      }
    });
	

	// Listen for the 'BeforeSetContent' event
	editor.on('BeforeSetContent', function (e) {
		// Check if the content being set is a video element
		if (e.content.indexOf('<video') !== -1) {
		// Add autoplay and muted attributes to video tags
		e.content = e.content.replace(/<video/g, '<video class="video-source" data-played="true" autoplay loop playsinline');
		}
	});

	// Listen for the 'BeforeExecCommand' event to intercept the insert command
	editor.on('BeforeExecCommand', function (e) {
		if (e.command === 'mceInsertContent') {
		var content = editor.selection.getContent();
		if (content.indexOf('<video') !== -1) {
			// Add autoplay and muted attributes to video tags
			content = content.replace(/<video/g, '<video class="video-source" data-played="true" autoplay loop playsinline');
			editor.selection.setContent(content);
			e.preventDefault();
		}
		}
	});
  },
  file_picker_callback: function (callback, value, meta) {
    /* Provide file and text for the link dialog */
    
    if (meta.filetype === 'file') {
      callback('https://www.google.com/logos/google.jpg', { text: 'My text' });
    }

    /* Provide image and alt text for the image dialog */
    // if (meta.filetype === 'image') {
    //   callback('https://www.google.com/logos/google.jpg', { alt: 'My alt text' });
    // }

    /* Provide alternative source and posted for the media dialog */
    if (meta.filetype === 'media' || meta.filetype === 'image') {
        var input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.setAttribute('accept', meta.filetype === 'image' ? 'image/*' : 'video/*');

      input.onchange = function () {
        var file = this.files[0];
        var formData = new FormData();
        formData.append('file', file);

        fetch('<?php echo base_url("admin/tinymceUpload")?>', {
          method: 'POST',
          body: formData
        }).then(response => response.json()).then(data => {
          if (data.location) {
            //   alert(data.location)
            callback(data.location, {title: file.name });
          } else {
            alert('Upload failed: ' + data.error);
          }
        }).catch(() => {
          alert('Upload failed.');
        });
      };

      input.click();
    //   callback('movie.mp4', { source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg' });
    }
  },
  templates: [
        { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
    { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
    { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
  ],
  template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
  template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
  height: 520,
  image_caption: true,
  quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
  noneditable_noneditable_class: "mceNonEditable",
  toolbar_mode: 'wrap',
  toolbar_sticky: false,
  contextmenu: "link image imagetools table",
  font_family_formats: 'Arial=arial;Times New Roman=times new roman,times;Agency=Agency;Arial Black=Arial Black;Archivo Black=Archivo Black; Andale Mono=andale mono,times; Arial=arial,helvetica,sans-serif; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Terminal=terminal,monaco; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
  content_style: `
        @font-face {
            font-family: 'Agency';
            src: url('<?php echo base_url()?>assets/font/Agency.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Archivo Black';
            src: url('<?php echo base_url()?>assets/font/ArchivoBlack.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Arial Black';
            src: local('Arial Black'), local('Arial Black'), url('<?php echo base_url();?>assets/font/ArialBlack.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'Agency', 'Archivo Black', sans-serif;
            color: black;
        }
    `
 });
    
</script>
<!--<script src="<?php echo base_url();?>assets/admin/ckeditor/ckeditor.js"></script>-->
<!--<script src="<?php echo base_url();?>assets/admin/ckfinder/ckfinder.js"></script>-->

<script>
// var ckedit = CKEDITOR.replace('editor1');
// var ckedit = CKEDITOR.replace('editor1', {
// 	filebrowserBrowseUrl:'<?php echo base_url();?>assets/admin/ckfinder/ckfinder.html',
//     filebrowserUploadUrl: '<?php echo base_url();?>assets/admin/ckeditor/ck_upload1.php',
//     filebrowserUploadMethod: 'form'
// });

// CKFinder.setupCKEditor(ckedit);
</script>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
   
	function get_categories(){
		var mid = $('#main_categories_id').val(); 
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/get_categories/"; ?>',
			data: {mid: mid},
			success:function (result) {
				var res = "'"+result+"'";
				$('#categories_id').html(result);  
				$('#sub_categories_id').html('<option value="">No Record Found</option>');  
			}
		});	
	}
	function get_image_url(){
		var shows_id = $('#shows_id').val(); 
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/get_image_url/"; ?>',
			data: {shows_id: shows_id},
			success:function (result) {
				$('#image_url').val(result);   
			}
		});	
	}
	function get_sub_categories(){
		var cid = $('#categories_id').val(); 
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/get_sub_categories/"; ?>',
			data: {cid: cid},
			success:function (result) {
				var res = "'"+result+"'";
				$('#sub_categories_id').html(result);  
			}
		});	
	}
	
	function delete_v_image(id,image){
	    
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/delete_v_image/"; ?>',
			data: {
			    id: id,
			    image: image
			    
			},
			success:function (result) {
			 //   console.log(result);
				location.reload(); 
			}
		});	
	}
	

	
</script>

