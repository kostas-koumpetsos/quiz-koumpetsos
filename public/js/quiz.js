const answerLabels = [...document.querySelectorAll("label.answer-label")];
const quizForm = document.querySelector("#quiz-wrapper");

var selectedRadio = null;

answerLabels.forEach(lbl => {
	const radio = lbl.querySelector("input");
	const ddiv = lbl.querySelector("div");
	ddiv.addEventListener('click', lbl => {
		if(radio == selectedRadio && radio.checked) quizForm.submit();
		selectedRadio = radio;
	});
});
