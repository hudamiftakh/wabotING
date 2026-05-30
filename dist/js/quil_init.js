if (document.querySelector("#editor")) {
	var toolbarOptions = [["bold", "italic", "strike"], ["clean"]];
	var quill = new Quill("#editor", {
		theme: "snow",
		modules: {
			toolbar: toolbarOptions,
		},
		placeholder: "Isi pesan disini",
	});
	window.quill = quill; // Make it global
	quill.setContents([
		{
			insert: "",
		},
		{
			insert: "",
			attributes: {
				bold: true,
			},
		},
		{
			insert: "",
		},
		{
			insert: "",
			attributes: {
				strike: true,
			},
		},
		{
			insert: "",
		},
		{
			insert: "",
			attributes: {
				italic: true,
			},
		},
		{
			insert: "\n",
		},
	]);
	var quillInnerText = quill.root;
	var adaptiveWA = document.querySelector("#adaptive-wa");
	var adaptiveText = adaptiveWA ? adaptiveWA.querySelector(".text") : null;
	var adaptiveText2 = adaptiveWA ? adaptiveWA.querySelector(".text2") : null;
	const copyBtn = document.querySelector(".copy-btn");

	function replaceTagsWithSymbols(inputElement, outputElement) {
		if (inputElement && outputElement) {
			outputElement.innerHTML = inputElement.innerHTML
				.replaceAll("<strong>", "*")
				.replaceAll("</strong>", "*")
				.replaceAll("<em>", "_")
				.replaceAll("</em>", "_")
				.replaceAll("<s>", "~")
				.replaceAll("</s>", "~");
		}
	}

	function formatText(delta, oldDelta, source) {
		replaceTagsWithSymbols(quillInnerText, adaptiveText);
		replaceTagsWithSymbols(quillInnerText, adaptiveText2);
	}
	formatText();
	function syncToTarget() {
		var editorEl = document.querySelector("#editor");
		if (!editorEl) return;
		var targetId = editorEl.dataset.target;
		if (!targetId) return;
		var target = document.getElementById(targetId);
		if (target) {
			// Convert Quill's HTML to WhatsApp format
			let html = quill.root.innerHTML;
			let whatsappText = html
				.replace(/<strong>(.*?)<\/strong>/g, "*$1*")
				.replace(/<em>(.*?)<\/em>/g, "_$1_")
				.replace(/<s>(.*?)<\/s>/g, "~$1~")
				.replace(/<br>/g, "\n")
				.replace(/<\/p><p>/g, "\n")
				.replace(/<p>(.*?)<\/p>/g, "$1\n")
				.replace(/<[^>]*>/g, "")
				.trim();
			target.value = whatsappText;
		}
	}

	quill.on("text-change", () => {
		formatText();
		syncToTarget();
	});

	// Initial sync
	syncToTarget();
}

// Global helpers for Quill
window.quillInsertText = function (text) {
	if (typeof quill !== "undefined" && quill) {
		const range = quill.getSelection();
		if (range) {
			quill.insertText(range.index, text);
			quill.setSelection(range.index + text.length, 0);
		} else {
			quill.insertText(quill.getLength(), text);
			quill.setSelection(quill.getLength(), 0);
		}
	}
};
