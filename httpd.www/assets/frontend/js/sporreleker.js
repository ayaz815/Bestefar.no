function quizInfoToggle(btn){
	var wasOpen = btn.classList.contains('open');
	document.querySelectorAll('.quiz-info.open').forEach(function(el){ el.classList.remove('open'); });
	if(!wasOpen){ btn.classList.add('open'); }
}
document.addEventListener('click', function(e){
	if(!e.target.closest('.quiz-info')){
		document.querySelectorAll('.quiz-info.open').forEach(function(el){ el.classList.remove('open'); });
	}
	if(!e.target.closest('#quizFilter')){
		var f = document.getElementById('quizFilter');
		if(f){ f.classList.remove('open'); }
	}
});
