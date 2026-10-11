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

// Mobile only: the table shows just Dato/Navn there, so tapping a row opens
// a popup with the rest (Kategorier, Ant spørsmål, info, a print button) -
// filled from the row's own data-* attributes, no extra request needed.
document.querySelectorAll('.quiz-row').forEach(function(row){
	row.addEventListener('click', function(){
		if(window.innerWidth > 767){ return; }
		var modal = document.getElementById('quizMobileModal');
		if(!modal){ return; }
		modal.querySelector('.qmm-name').textContent = row.dataset.name || '';
		modal.querySelector('.qmm-date').textContent = row.dataset.date || '';
		modal.querySelector('.qmm-category').textContent = row.dataset.category || '';
		modal.querySelector('.qmm-count').textContent = row.dataset.count || '';
		modal.querySelector('.qmm-info').textContent = row.dataset.info || '';
		modal.classList.add('open');
	});
});
var quizMobileModal = document.getElementById('quizMobileModal');
if(quizMobileModal){
	quizMobileModal.addEventListener('click', function(e){
		if(e.target === quizMobileModal){ quizMobileModal.classList.remove('open'); }
	});
}
