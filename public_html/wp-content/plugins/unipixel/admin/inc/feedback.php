<?php if (!defined('ABSPATH')) exit;

/**
 * Render the feedback buttons (Something Not Working? / Feature Missing?).
 * Call this wherever buttons should appear on the page.
 */
function unipixel_render_feedback_buttons() {
?>
<div class="d-flex gap-2">
	<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#unipixelFeedbackModal" data-feedback-type="Issue">
		<i class="fa-solid fa-bug"></i> Something Not Working?
	</button>
	<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#unipixelFeedbackModal" data-feedback-type="Feature">
		<i class="fa-solid fa-bullhorn"></i> Feature Missing?
	</button>
</div>
<?php
}

/**
 * Render the feedback modal.
 * Hooked into admin_footer so it only appears once per page.
 */
function unipixel_render_feedback_modal() {
?>
<!-- Feedback Modal -->
<div class="modal fade" id="unipixelFeedbackModal" tabindex="-1" aria-labelledby="unipixelFeedbackModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<!-- Feedback Form -->
			<form id="unipixel-feedback-form" method="post">
				<div class="modal-header">
					<h5 class="modal-title" id="unipixelFeedbackModalLabel"><i class="fa-solid fa-comments"></i> Let us know about something</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p>If you spot something not working properly, or need a feature to be considered, please let us know and we can look into it as soon as possible.</p>

					<div class="mb-3">
						<label for="unipixelFeedbackType" class="form-label">Feedback Type</label>
						<select class="form-select" id="unipixelFeedbackType" name="unipixel_feedback_type" required>
							<option value="Issue">Something not working</option>
							<option value="Feature">Feature missing / Feature request</option>
						</select>
					</div>

					<div class="mb-3">
						<label for="unipixelFeedback" class="form-label">Please Describe</label>
						<textarea class="form-control" id="unipixelFeedback" name="unipixel_feedback" rows="4" required></textarea>
					</div>

					<div class="mb-3">
						<label for="unipixelEmail" class="form-label">Email (optional - so we can update you)</label>
						<input type="email" class="form-control" id="unipixelEmail" name="unipixel_email" placeholder="youremail@example.com">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Submit Feedback</button>
				</div>
			</form>

			<!-- Loading State -->
			<div id="unipixel-feedback-loading" class="d-none text-center p-4">
				<div class="d-flex align-items-center justify-content-center gap-2">
					<div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
					<span>Sending your feedback...</span>
				</div>
			</div>

			<!-- Success Message -->
			<div id="unipixel-feedback-success" class="d-none text-center p-4">
				<h2><i class="fa-solid fa-hands-clapping"></i></h2>
				<h5 class="mb-3">Thanks for your feedback!</h5>
				<p>We appreciate you taking the time to let us know, we will try to get back to you as soon as possible.</p>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php
}
