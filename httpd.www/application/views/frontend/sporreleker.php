<link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/sporreleker.css'); ?>">

<div class="quiz-catalog">
	<div class="quiz-catalog-head">
		<h1>Velkommen til BestefarQuiz</h1>
		<div class="quiz-catalog-head-right">
			<span class="quiz-catalog-count">1 - <?php echo count($quizzes); ?></span>
			<div class="quiz-filter" id="quizFilter">
				<div class="quiz-filter-btn" onclick="document.getElementById('quizFilter').classList.toggle('open')">
					Kategorier <?php echo $quiz_type ? ': '.$quiz_type : ''; ?> ▾
				</div>
				<div class="quiz-filter-menu">
					<a href="<?php echo base_url('home/sporreleker'); ?>" class="<?php echo $quiz_type=='' ? 'active' : ''; ?>">Alle</a>
					<a href="<?php echo base_url('home/sporreleker?type=AudioQuiz'); ?>" class="<?php echo $quiz_type=='AudioQuiz' ? 'active' : ''; ?>">AudioQuiz</a>
					<a href="<?php echo base_url('home/sporreleker?type=BildeQuiz'); ?>" class="<?php echo $quiz_type=='BildeQuiz' ? 'active' : ''; ?>">BildeQuiz</a>
					<a href="<?php echo base_url('home/sporreleker?type=TekstQuiz'); ?>" class="<?php echo $quiz_type=='TekstQuiz' ? 'active' : ''; ?>">TekstQuiz</a>
					<a href="<?php echo base_url('home/sporreleker?type=BilderMusikk'); ?>" class="<?php echo $quiz_type=='BilderMusikk' ? 'active' : ''; ?>">BilderMusikk</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Mobile-only header: no filter (phones only ever see AudioQuiz, via the
	     nav link's ?type=AudioQuiz), just the category name and count. Hidden
	     on desktop/tablet by CSS, where the full header above is used instead. -->
	<div class="quiz-mobile-head">
		<span><?php echo htmlspecialchars($quiz_type ?: 'Alle'); ?></span>
		<span><?php echo count($quizzes); ?></span>
	</div>

	<?php
		// Build a sort link that keeps the current category filter and flips direction
		// when you click the column that's already sorted.
		if(!function_exists('quiz_sort_link')){
			function quiz_sort_link($label, $key, $sort, $dir, $quiz_type){
				$next_dir = ($sort == $key && $dir == 'asc') ? 'desc' : 'asc';
				$url = base_url('home/sporreleker?sort='.$key.'&dir='.$next_dir);
				if($quiz_type){ $url .= '&type='.$quiz_type; }
				$arrow = ($sort == $key) ? ($dir == 'asc' ? ' ▲' : ' ▼') : '';
				return '<a href="'.$url.'">'.$label.$arrow.'</a>';
			}
		}
	?>

	<div class="quiz-table-wrap">
	<table class="quiz-table">
		<thead>
			<tr>
				<th class="quiz-date-col"><?php echo quiz_sort_link('Dato', 'date', $sort, $dir, $quiz_type); ?></th>
				<th><?php echo quiz_sort_link('Kategorier', 'category', $sort, $dir, $quiz_type); ?></th>
				<th><?php echo quiz_sort_link('Navn', 'name', $sort, $dir, $quiz_type); ?></th>
				<th class="align-center"><?php echo quiz_sort_link('Ant spørsmål', 'count', $sort, $dir, $quiz_type); ?></th>
				<th class="quiz-icons-col"></th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($quizzes)){ ?>
				<?php foreach($quizzes as $quiz){
					$quiz_date_fmt = $quiz['date'] ? date('d.m.y', strtotime($quiz['date'])) : '-';
					$quiz_info_text = !empty($quiz['info']) ? $quiz['info'] : 'Ingen info lagt inn for denne quizen ennå.';
				?>
					<tr class="quiz-row" data-name="<?php echo htmlspecialchars($quiz['name']); ?>" data-date="<?php echo htmlspecialchars($quiz_date_fmt); ?>" data-category="<?php echo htmlspecialchars($quiz['quiz_type']); ?>" data-count="<?php echo (int) $quiz['question_count']; ?>" data-info="<?php echo htmlspecialchars($quiz_info_text); ?>" data-musictype="<?php echo htmlspecialchars(!empty($quiz['music_type']) ? $quiz['music_type'] : ''); ?>" data-id="<?php echo htmlspecialchars($quiz['external_id']); ?>">
						<td class="quiz-date"><?php echo $quiz_date_fmt; ?></td>
						<td><?php echo htmlspecialchars($quiz['quiz_type']); ?></td>
						<td><?php echo htmlspecialchars($quiz['name']); ?></td>
						<td class="align-center quiz-count"><?php echo (int) $quiz['question_count']; ?></td>
						<td class="quiz-info-cell">
							<span class="quiz-icons">
								<button type="button" class="quiz-info" onclick="quizInfoToggle(this)">i
									<span class="quiz-info-popup<?php echo empty($quiz['info']) ? ' empty' : ''; ?>">
										<?php echo htmlspecialchars($quiz_info_text); ?>
									</span>
								</button>
								<!-- The client's own supplied document symbol (Symbol.png).
								     PDF generation only exists for AudioQuiz so far. -->
								<?php if($quiz['quiz_type'] === 'AudioQuiz'){ ?>
								<a class="quiz-pdf" href="https://api.bestefar.no/api/forms/pdf/<?php echo urlencode($quiz['external_id']); ?>" target="_blank" rel="noopener" title="Skriv ut quiz">
									<img src="<?php echo base_url('assets/frontend/images/quiz-pdf-icon.png'); ?>" alt="PDF">
								</a>
								<?php }else{ ?>
								<span class="quiz-pdf" title="PDF-nedlasting er ikke klar ennå">
									<img src="<?php echo base_url('assets/frontend/images/quiz-pdf-icon.png'); ?>" alt="PDF">
								</span>
								<?php } ?>
								<!-- Start Quiz on desktop/laptop too, not just the mobile popup -
								     same ?show=<id> player loading, AudioQuiz only for now. -->
								<?php if($quiz['quiz_type'] === 'AudioQuiz'){ ?>
								<a class="quiz-start" href="https://bestefar.no/bestefar-audio/data/screens/show.html?screen=1&show=<?php echo urlencode($quiz['external_id']); ?>" target="_blank" rel="noopener" title="Start quiz">
									<img src="<?php echo base_url('assets/frontend/images/playbutton.png'); ?>" alt="Start">
								</a>
								<?php }else{ ?>
								<span class="quiz-start" title="Kommer snart">
									<img src="<?php echo base_url('assets/frontend/images/playbutton.png'); ?>" alt="Start">
								</span>
								<?php } ?>
							</span>
						</td>
					</tr>
				<?php } ?>
			<?php }else{ ?>
				<tr><td colspan="5" class="quiz-empty">Ingen spørreleker funnet.</td></tr>
			<?php } ?>
		</tbody>
	</table>
	</div>

	<!-- Mobile-only: tapping a row opens this with the quiz's info and a print
	     button, since the table itself only shows Dato/Navn on phones. Filled
	     in by JS from the row's data-* attributes - see sporreleker.js.
	     Matches the client's 13 Oct mockup: yellow card, Utgitt meta line,
	     two-line info area, Skriv ut (green) + Start Quiz (red) side by side. -->
	<div class="quiz-mobile-modal" id="quizMobileModal">
		<div class="quiz-mobile-modal-inner">
			<button type="button" class="quiz-mobile-modal-close" onclick="document.getElementById('quizMobileModal').classList.remove('open')">×</button>
			<h2 class="qmm-name"></h2>
			<p class="qmm-meta">Utgitt: <span class="qmm-date"></span> – <span class="qmm-category"></span><span class="qmm-musictype-wrap"> – <span class="qmm-musictype"></span></span> – <span class="qmm-count"></span> spørsmål</p>
			<p class="qmm-info"></p>
			<div class="quiz-mobile-actions">
				<button type="button" class="quiz-mobile-print">Skriv ut</button>
				<button type="button" class="quiz-mobile-start" title="Kommer snart">Start Quiz</button>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url('assets/frontend/js/sporreleker.js'); ?>"></script>
