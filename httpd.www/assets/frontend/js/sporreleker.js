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

// Wires up the mobile row-tap popup and the filter dropdown toggle. Called
// once on load and again after every AJAX swap (see loadQuizCatalog below),
// since those replace the whole .quiz-catalog markup with fresh elements
// that have no listeners on them yet.
function initQuizCatalogInteractions(){
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
			// Music Type (Musikk/Talemåte/Lyd) only applies to AudioQuiz - the
			// wrap span is hidden entirely for other categories instead of
			// showing an empty " – " gap.
			var musicType = row.dataset.musictype || '';
			modal.querySelector('.qmm-musictype').textContent = musicType;
			modal.querySelector('.qmm-musictype-wrap').style.display = musicType ? '' : 'none';
			var quizId = row.dataset.id || '';

			// Skriv ut: AudioQuiz rows now have a real multi-page PDF (title +
			// participant sheet + answer key) generated server-side - opening it
			// lets the browser's own PDF viewer handle printing, which already
			// searches for nearby AirPrint/Mopria printers and lets the user set
			// a copy count, same as any other PDF. Other quiz types fall back to
			// printing just the popup card, as before.
			var printBtn = modal.querySelector('.quiz-mobile-print');
			if(printBtn){
				if(row.dataset.category === 'AudioQuiz' && quizId){
					printBtn.onclick = function(){
						window.open('https://api.bestefar.no/api/forms/pdf/' + encodeURIComponent(quizId), '_blank');
					};
				}else{
					printBtn.onclick = function(){ window.print(); };
				}
			}

			// Start Quiz: the live player already supports loading any quiz by
			// ID via ?show=<id> (used by the generator's own Preview button),
			// so this reuses that instead of needing a new endpoint. Only
			// wired up for AudioQuiz for now - the other quiz types' player
			// apps (bestefar-image, bestefar-imagemusic, bestefar-crossword)
			// haven't been confirmed to support the same ?show=<id> pattern.
			var startBtn = modal.querySelector('.quiz-mobile-start');
			if(startBtn){
				if(row.dataset.category === 'AudioQuiz' && quizId){
					startBtn.disabled = false;
					startBtn.removeAttribute('title');
					startBtn.onclick = function(){
						window.location.href = 'https://bestefar.no/bestefar-audio/index.html?screen=1&show=' + encodeURIComponent(quizId) + '&autostart=1';
					};
				}else{
					startBtn.disabled = true;
					startBtn.title = 'Kommer snart';
					startBtn.onclick = null;
				}
			}

			modal.classList.add('open');
		});
	});
	var quizMobileModal = document.getElementById('quizMobileModal');
	if(quizMobileModal){
		quizMobileModal.addEventListener('click', function(e){
			if(e.target === quizMobileModal){ quizMobileModal.classList.remove('open'); }
		});
	}
}

// AJAX filter/sort - clicking a category or a column header used to be a
// plain link, so every change reloaded the whole page: header, nav, footer
// all flashed and the scroll position reset, which read as the table
// "jumping". This fetches just the table markup (the controller returns the
// partial view for an X-Requested-With: XMLHttpRequest request - see
// Home::sporreleker()) and fades it in in place instead, updating the URL
// with pushState so filters/sorting stay bookmarkable and back/forward work.
function loadQuizCatalog(url, pushState){
	var container = document.querySelector('.quiz-catalog');
	if(!container){ window.location.href = url; return; }

	fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
		.then(function(res){
			if(!res.ok){ throw new Error('Request failed: ' + res.status); }
			return res.text();
		})
		.then(function(html){
			container.style.opacity = '0';
			setTimeout(function(){
				var wrapper = document.createElement('div');
				wrapper.innerHTML = html;
				var newContainer = wrapper.querySelector('.quiz-catalog');
				if(newContainer){
					container.replaceWith(newContainer);
					newContainer.style.opacity = '0';
					requestAnimationFrame(function(){
						newContainer.style.opacity = '1';
					});
					initQuizCatalogInteractions();
					bindQuizCatalogLinks();
				}
				if(pushState !== false){
					history.pushState({ quizCatalog: true }, '', url);
				}
			}, 150);
		})
		.catch(function(){
			// Fetch failed for any reason (offline, server error) - fall back
			// to a normal navigation rather than silently doing nothing.
			window.location.href = url;
		});
}

// Intercepts clicks on the filter dropdown links and the column sort
// headers (both plain <a href> elements rendered by the PHP view) and
// routes them through loadQuizCatalog() instead of a full navigation.
function bindQuizCatalogLinks(){
	document.querySelectorAll('.quiz-filter-menu a, .quiz-table thead a').forEach(function(a){
		a.addEventListener('click', function(e){
			e.preventDefault();
			loadQuizCatalog(a.getAttribute('href'));
		});
	});
}

document.querySelector('.quiz-catalog').style.transition = 'opacity 0.15s ease';
initQuizCatalogInteractions();
bindQuizCatalogLinks();

window.addEventListener('popstate', function(){
	loadQuizCatalog(window.location.href, false);
});
