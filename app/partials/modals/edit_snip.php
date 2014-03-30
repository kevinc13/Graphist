	<div id="edit_snip_modal" class="modal">
		<header>Edit Snip<a class="close_modal" onclick="require('main').document.closeModal($('[data-modal-toggled=true]'));">&times;</a></header>
		<form action="home" method="post">
			<textarea maxlength="200"></textarea>
			<input type="submit" class="btn primary-btn" name="save_edited_snip" value="Save" />
			<span>200</span>
		</form>
	</div>
	<!-- End Edit Snip Modal -->