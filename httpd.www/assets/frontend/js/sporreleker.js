// Quiz types that have a PDF, and the api.bestefar.no route segment serving it.
var QUIZ_PDF_PATH = {AudioQuiz: 'forms', BildeQuiz: 'image-quiz', BilderMusikk: 'image-music', TekstQuiz: 'crossword'};
function quizPdfUrl(category, id){
	return 'https://api.bestefar.no/api/' + QUIZ_PDF_PATH[category] + '/pdf/' + encodeURIComponent(id);
}

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
			// Only quiz types with a player and/or a PDF get the popup.
			var playerUrls = {
				AudioQuiz: 'https://bestefar.no/bestefar-audio/index.html?screen=1&show={id}&autostart=1',
				BildeQuiz: 'https://bestefar.no/bestefar-image/index.html?show={id}',
				BilderMusikk: 'https://bestefar.no/bestefar-imagemusic/index.html?show={id}'
			};
			var playerUrl = playerUrls[row.dataset.category];
			var pdfPath = QUIZ_PDF_PATH[row.dataset.category];
			if(!playerUrl && !pdfPath){ return; }
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

			// Skriv ut: types with a server-generated PDF (see QUIZ_PDF_PATH)
			// hand it to the device's own print flow (printer picker, copies);
			// the button is hidden for the rest.
			var printBtn = modal.querySelector('.quiz-mobile-print');
			if(printBtn){
				printBtn.style.display = pdfPath ? '' : 'none';
				printBtn.onclick = pdfPath ? function(){
					printQuizPdf(quizPdfUrl(row.dataset.category, quizId), row.dataset.name);
				} : null;
			}

			// Start Quiz: the live player already supports loading any quiz by
			// ID via ?show=<id> (used by the generator's own Preview and share
			// buttons), so this reuses that instead of needing a new endpoint.
			var startBtn = modal.querySelector('.quiz-mobile-start');
			if(startBtn){
				startBtn.style.display = playerUrl ? '' : 'none';
				startBtn.onclick = playerUrl ? function(){
					window.location.href = playerUrl.replace('{id}', encodeURIComponent(quizId));
				} : null;
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

// Opens the system print dialog (printer picker + copies) for a quiz PDF
// instead of just showing it in a viewer tab. Mobile: share sheet with the
// PDF file (has "Skriv ut"/Print -> AirPrint/Mopria printer list). Desktop:
// hidden iframe + print(). Falls back to opening the PDF in a new tab.
function printQuizPdf(url, name){
	var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent) ||
		(navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
	// Open the fallback tab synchronously so popup blockers allow it.
	var fallback = function(){ window.open(url, '_blank'); };
	fetch(url).then(function(r){
		if(!r.ok) throw new Error('pdf ' + r.status);
		return r.blob();
	}).then(function(blob){
		var fileName = ((name || 'quiz').replace(/[^\w\-]+/g, '_')) + '.pdf';
		var file = new File([blob], fileName, {type: 'application/pdf'});
		if(isMobile && navigator.canShare && navigator.canShare({files: [file]})){
			return navigator.share({files: [file], title: name || 'Quiz'}).catch(function(e){
				if(e && e.name !== 'AbortError') fallback();
			});
		}
		var frame = document.createElement('iframe');
		frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
		frame.src = URL.createObjectURL(blob);
		frame.onload = function(){
			try{
				frame.contentWindow.focus();
				frame.contentWindow.print();
			}catch(e){ fallback(); }
			setTimeout(function(){ URL.revokeObjectURL(frame.src); frame.remove(); }, 60000);
		};
		document.body.appendChild(frame);
	}).catch(fallback);
}

// Table PDF icon (desktop): same print flow; href stays as no-JS fallback.
document.addEventListener('click', function(e){
	var a = e.target.closest && e.target.closest('a.quiz-pdf');
	if(!a) return;
	var row = a.closest('.quiz-row');
	if(!row || !row.dataset.id) return;
	e.preventDefault();
	e.stopPropagation();
	printQuizPdf(a.href, row.dataset.name);
}, true);
