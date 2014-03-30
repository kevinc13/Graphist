	<div id="global_post_snip" class="modal">
		<header>Compose a snip<a class="close_modal" onclick="require('main').document.closeModal($('[data-modal-toggled=true]'));">&times;</a></header>
		<form action="home" method="post" class="post-snip global-post-snip" enctype="multipart/form-data">
			<section>
				<textarea name="snip_content" maxlength="200" placeholder="Compose a Snip"></textarea>
				<span class="shrink">&times;</span>
				<div class="photo_previews">
					<figure>
						<img id="snip_photo_preview" height="50" width="50" />
					</figure>
				</div>
				<div class="progress">
					<div class="progress-bar"></div>
				</div>
				<div class="form_attachments">
					<ul>
						<li>
							<a href="#" data-action="attach_photo">
								<img src="public/img/ic_attach_photo.png">
							</a>
						</li>
						<li>
							<a href="#" data-action="attach_video">
								<img src="public/img/ic_attach_video.png">
							</a>
						</li>
					</ul>
				</div>
				<div class="snip-privacy-selection">
					<div class="privacy-input">
						<input type="text" placeholder="Add groups, hubs, and people" id="snip_people_search"/>
					</div>
					<span>&times;</span>
					<ul>
						<li data-type="default">All Friends</li>
						<li data-type="default">Public</li>
						<li class="dropdown_divider"></li>
						<section></section>
						<li class="dropdown_divider"></li>
						<section></section>
					</ul>	
				</div>
				<div class="form_utilities">			
					<button type="submit" class="btn primary-btn">Post</button>
					<span class="char_count">200</span>
				</div>
			</section>
		</form>
		
		<form action="" style="display:none;" id="global_img_form" method="post" enctype="multipart/form-data">
        	<input type="file" style="display:none;" id="global_snip_photo" />
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'];?>"/>
            <input type="hidden" name="img_count" value="<?php echo $numImage + 1;?>">
       	</form>

       	<form action="" style="display:none;" id="global_video_form" method="post" enctype="multipart/form-data">
       		<input type="file" style="display:none;" id="global_snip_video" />
       		<input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'];?>" />
       		<input type="hidden" name="video_count" value="<?php echo $numVideo + 1;?>">
       	</form>
	</div>
	<!-- End Global Post Snip Form -->