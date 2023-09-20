<?php 
/* template name: create-post */
get_header();
?>
<style type="text/css">
	body {
  font-family: sans-serif;
  background-color: #eeeeee;
}

.file-upload {
  background-color: #ffffff;
  width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.file-upload-btn {
  width: 100%;
  margin: 0;
  color: #fff;
  background: #1FB264;
  border: none;
  padding: 10px;
  border-radius: 4px;
  border-bottom: 4px solid #15824B;
  transition: all .2s ease;
  outline: none;
  text-transform: uppercase;
  font-weight: 700;
}

.file-upload-btn:hover {
  background: #1AA059;
  color: #ffffff;
  transition: all .2s ease;
  cursor: pointer;
}

.file-upload-btn:active {
  border: 0;
  transition: all .2s ease;
}

.file-upload-content {
  display: none;
  text-align: center;
}

.file-upload-input {
  position: absolute;
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  outline: none;
  opacity: 0;
  cursor: pointer;
}

.image-upload-wrap {
  margin-top: 20px;
  border: 4px dashed #1FB264;
  position: relative;
}

.image-dropping,
.image-upload-wrap:hover {
  background-color: #1FB264;
  border: 4px dashed #ffffff;
}

.image-title-wrap {
  padding: 0 15px 15px 15px;
  color: #222;
}

.drag-text {
  text-align: center;
}

.drag-text h3 {
  font-weight: 100;
  text-transform: uppercase;
  color: #15824B;
  padding: 60px 0;
}

.file-upload-image {
  max-height: 200px;
  max-width: 200px;
  margin: auto;
  padding: 20px;
}

.remove-image {
  width: 200px;
  margin: 0;
  color: #fff;
  background: #cd4535;
  border: none;
  padding: 10px;
  border-radius: 4px;
  border-bottom: 4px solid #b02818;
  transition: all .2s ease;
  outline: none;
  text-transform: uppercase;
  font-weight: 700;
}

.remove-image:hover {
  background: #c13b2a;
  color: #ffffff;
  transition: all .2s ease;
  cursor: pointer;
}

.remove-image:active {
  border: 0;
  transition: all .2s ease;
}
</style>
<script class="jsbin" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js">
</script>
<script type="text/javascript">
	function readURL(input) {
		  if (input.files && input.files[0]) {

		    var reader = new FileReader();

		    reader.onload = function(e) {
		      $('.image-upload-wrap').hide();

		      $('.file-upload-image').attr('src', e.target.result);
		      $('.file-upload-content').show();

		      $('.image-title').html(input.files[0].name);
		    };

		    reader.readAsDataURL(input.files[0]);

		  } else {
		    removeUpload();
		  }
	}

	function removeUpload() {
	  $('.file-upload-input').replaceWith($('.file-upload-input').clone());
	  $('.file-upload-content').hide();
	  $('.image-upload-wrap').show();
	}

	$('.image-upload-wrap').bind('dragover', function () {
			$('.image-upload-wrap').addClass('image-dropping');
	});

	$('.image-upload-wrap').bind('dragleave', function () {
		$('.image-upload-wrap').removeClass('image-dropping');
	});

</script>
<?php 
if(isset($_POST['submit'])){

	if ( !isset($_POST['title']) ) {
        return;
    }

    // Check that the nonce was set and valid
    if( !wp_verify_nonce($_POST['_wpnonce'], 'wps-frontend-post') ) {
        echo 'Did not save because your form seemed to be invalid. Sorry';
        return;
    }

    // Do some minor form validation to make sure there is content
    if (strlen($_POST['title']) < 3) {
        echo 'Please enter a title. Titles must be at least three characters long.';
        return;
    }
    if (strlen($_POST['content']) < 100) {
        echo 'Please enter content more than 100 characters in length';
        return;
    }

 

    // Add the content of the form to $post as an array
    $post = array(
        'post_title'    => $_POST['title'],
        'post_content'  => $_POST['content'],
        'post_category' => $_POST['cat'], 
        'tags_input'    => $_POST['post_tags'],
        'post_status'   => 'publish',   // Could be: publish
        'post_type' 	=> 'post' // Could be: `page` or your CPT
    );
    $post_id = wp_insert_post($post);

    // Check if a featured image was uploaded
    if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] == 0) {

        // Define the image data
        $image_data = file_get_contents($_FILES['feature_image']['tmp_name']);
        $image_name = $_FILES['feature_image']['name'];

        // Set upload folder
        $upload_dir = wp_upload_dir();

        // Generate a unique file name
        $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name);
        $filename = basename($unique_file_name);

        // Check folder permission and define file location
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        // Create the image file on the server
        file_put_contents($file, $image_data);

        // Check image file type
        $wp_filetype = wp_check_filetype($filename, null);

        // Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Create the attachment
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

        // Include image.php
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);

        // Assign metadata to attachment
        wp_update_attachment_metadata($attach_id, $attach_data);

        // Set the attached image as the featured image of the post
        set_post_thumbnail($post_id, $attach_id);
    }
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $video_file = $_FILES['video_file'];
        
        // Define the upload directory
        $upload_dir = wp_upload_dir();

        // Define the allowed video file types
        $allowed_types = array('mp4', 'mov', 'avi', 'mkv'); // You can add more types if needed

        // Get the file extension
        $file_extension = pathinfo($video_file['name'], PATHINFO_EXTENSION);

        // Check if the file type is allowed
        if (in_array(strtolower($file_extension), $allowed_types)) {
            // Generate a unique file name
            $unique_file_name = wp_unique_filename($upload_dir['path'], $video_file['name']);
            $file_name = basename($unique_file_name);

            // Define the file path
            $file_path = $upload_dir['path'] . '/' . $file_name;

            // Move the uploaded file to the upload directory
            if (move_uploaded_file($video_file['tmp_name'], $file_path)) {
                // Save the file URL as post meta
                $file_url = $upload_dir['url'] . '/' . $file_name;
                update_post_meta($post_id, 'video_url', $file_url);
            }
        }
    }


    echo 'Saved your post successfully! :)';
}?>
<div class="file-upload">
	<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'wps-frontend-post' ); ?>
	  <div class="mb-3">
	    <label for="postname" class="form-label">Title</label>
	    <input type="text" class="form-control"  name="title" id="postname" aria-describedby="emailHelp">
	  </div><br />
	  <div class="mb-3">
	  	<div>
	  		<label for="category" class="form-label">Choose Category</label><br>
	  		<?php wp_dropdown_categories( 'show_option_none=Category&tab_index=4&taxonomy=category' ); ?>
	  	</div><br>
	    <label for="description" class="form-label">Description</label>
	    <textarea class="form-control" name="content" id="description"></textarea> 
	  </div><br />
	  <div class="mb-3">
		    <label for="video_file" class="form-label">Upload Video</label>
		    <input class="video-upload-input" type="file" name="video_file" accept="video/*" />
	  </div><br>
	  
	  <button class="file-upload-btn" type="button" onclick="$('.file-upload-input').trigger( 'click' )">Add feature Image</button>

	  <div class="image-upload-wrap">
	    <input class="file-upload-input" type='file' name="feature_image" onchange="readURL(this);" accept="image/*" />
	    <div class="drag-text">
	      <h3>Drag and drop a file or select add Image</h3>
	    </div>
	  </div>
	  <div class="file-upload-content">
	    <img class="file-upload-image" src="#" alt="your image" />
	    <div class="image-title-wrap">
	      <button type="button" onclick="removeUpload()" class="remove-image">Remove <span class="image-title">Uploaded Image</span></button>
	    </div>
	  </div>
	  

	  <br>
	   <button type="submit" name="submit" class="file-upload-btn">Submit</button>
	</form>
</div>
<?php
get_footer();
?>