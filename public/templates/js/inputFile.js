$(document).ready(function() {
		$(".main_input_file").change(function() {
			var f_name = [];
			for (var i = 0; i < $(this).get(0).files.length; ++i) {
				f_name.push(" " + $(this).get(0).files[i].name);
			}
			$("#f_name").val(f_name.join(", "));
		});
	});
