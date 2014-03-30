	<div id="create_stack_modal" class="modal">
		<header>Create a Stack <a class="close_modal" onclick="require('main').document.closeModal($('[data-modal-toggled=true]'));">&times;</a></header>
		<form action="home" method="post">
			<input type="text" name="name" placeholder="Name" />
			<input type="submit" class="btn primary-btn" value="Create" disabled="disabled" />	
		</form>
	</div>
	<!-- End Create Stack Modal -->