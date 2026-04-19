jQuery(document).ready(function($) {
	$('[data-bs-target="#unipixelFeedbackModal"]').on('click', function() {
		const type = $(this).data('feedback-type');
		$('#unipixelFeedbackType').val(type);
		$('#unipixel-feedback-form').show();
		$('#unipixel-feedback-loading').addClass('d-none');
		$('#unipixel-feedback-success').addClass('d-none');
	});

	$('#unipixel-feedback-form').on('submit', function(e) {
		e.preventDefault();

		const formData = {
			action: 'unipixel_submit_feedback',
			nonce: UniPixelFeedbackAjax.nonce,
			unipixel_feedback_type: $('#unipixelFeedbackType').val(),
			unipixel_feedback: $('#unipixelFeedback').val(),
			unipixel_email: $('#unipixelEmail').val()
		};

		// Hide form, show loading
		$('#unipixel-feedback-form').hide();
		$('#unipixel-feedback-loading').removeClass('d-none');
		$('#unipixel-feedback-success').addClass('d-none');

		$.post(UniPixelFeedbackAjax.ajax_url, formData)
			.done(function(response) {
				$('#unipixel-feedback-loading').addClass('d-none');
				$('#unipixel-feedback-success').removeClass('d-none');
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				$('#unipixel-feedback-loading').addClass('d-none');
				$('#unipixel-feedback-form').show();
				alert('There was an error sending your feedback. Please try again.');
				console.error('Feedback error', textStatus, errorThrown);
			});
	});
});
