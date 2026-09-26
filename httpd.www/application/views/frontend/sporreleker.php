<style>
/* Flat, literal match to the Catalog_Quiz.pdf / Data.pdf mockups - no cards, no
   shadows, no rounded pills. No custom font here on purpose: the rest of the
   live site references a font ('agency_fbbold') that has no working @font-face
   anywhere, so every other page already just falls back to the plain browser
   default. This page matches that instead of standing out with a real font. */
.quiz-catalog, .quiz-catalog *{ box-sizing:border-box; }
.quiz-catalog{ max-width:1100px; margin:30px auto; padding:0 20px; min-height:60vh; color:#000; font-size:24px; }
.quiz-catalog-head{ display:flex; justify-content:space-between; align-items:baseline; margin-bottom:15px; }
.quiz-catalog h1{ font-size:34px; margin:0; }
.quiz-catalog-count{ font-size:24px; }
.quiz-filter{ margin-bottom:15px; position:relative; display:inline-block; }
.quiz-filter-btn{ background:#ffc200; border:1px solid #d6a600; padding:8px 24px; cursor:pointer; font-size:24px; }
.quiz-filter-menu{ display:none; position:absolute; top:100%; left:0; background:#fff; border:1px solid #999; min-width:160px; z-index:10; }
.quiz-filter-menu a{ display:block; padding:8px 14px; color:#000; text-decoration:none; font-size:24px; }
.quiz-filter-menu a:hover, .quiz-filter-menu a.active{ background:#9dc3e6; }
.quiz-filter.open .quiz-filter-menu{ display:block; }
table.quiz-table{ width:100%; border-collapse:collapse; font-size:24px; }
table.quiz-table th{ background:#9dc3e6; color:#000; text-align:left; padding:8px 12px; }
table.quiz-table th a{ color:#000; text-decoration:none; }
table.quiz-table td{ padding:8px 12px; background:#ffc200; color:#000; border-bottom:2px solid #fff; }
.quiz-table th.align-right, .quiz-table td.align-right{ text-align:right; }
.quiz-info{ display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:#5B9BD5; color:#fff; font-size:15px; font-weight:bold; cursor:pointer; border:none; padding:0; position:relative; }
.quiz-info-popup{
	display:none;
	position:absolute;
	right:0;
	top:26px;
	background:#333;
	color:#fff;
	padding:8px 12px;
	width:240px;
	font-size:17px;
	line-height:1.4;
	text-align:left;
	font-weight:normal;
	text-decoration:none;
	z-index:20;
}
.quiz-info-popup.empty{ color:#ccc; font-style:italic; }
.quiz-info.open .quiz-info-popup{ display:block; }
.quiz-empty{ padding:30px 0; color:#666; }
</style>

<div class="quiz-catalog">
	<div class="quiz-catalog-head">
		<h1>Velkommen til BestefarQuiz</h1>
		<span class="quiz-catalog-count">1 - <?php echo count($quizzes); ?></span>
	</div>

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

	<table class="quiz-table">
		<thead>
			<tr>
				<th class="align-right"><?php echo quiz_sort_link('Dato', 'date', $sort, $dir, $quiz_type); ?></th>
				<th><?php echo quiz_sort_link('Kategorier', 'category', $sort, $dir, $quiz_type); ?></th>
				<th><?php echo quiz_sort_link('Navn', 'name', $sort, $dir, $quiz_type); ?></th>
				<th class="align-right"><?php echo quiz_sort_link('Ant spørsmål', 'count', $sort, $dir, $quiz_type); ?></th>
				<th class="quiz-info-cell">
					<button type="button" class="quiz-info" onclick="quizInfoToggle(this)">i
						<span class="quiz-info-popup">Klikk på (i) på en quiz for å se en kort beskrivelse av den.</span>
					</button>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($quizzes)){ ?>
				<?php foreach($quizzes as $quiz){ ?>
					<tr>
						<td class="align-right"><?php echo $quiz['date'] ? date('d.m.y', strtotime($quiz['date'])) : '-'; ?></td>
						<td><?php echo htmlspecialchars($quiz['quiz_type']); ?></td>
						<td><?php echo htmlspecialchars($quiz['name']); ?></td>
						<td class="align-right"><?php echo (int) $quiz['question_count']; ?></td>
						<td class="quiz-info-cell">
							<button type="button" class="quiz-info" onclick="quizInfoToggle(this)">i
								<span class="quiz-info-popup<?php echo empty($quiz['info']) ? ' empty' : ''; ?>">
									<?php echo !empty($quiz['info']) ? htmlspecialchars($quiz['info']) : 'Ingen info lagt inn for denne quizen ennå.'; ?>
								</span>
							</button>
						</td>
					</tr>
				<?php } ?>
			<?php }else{ ?>
				<tr><td colspan="5" class="quiz-empty">Ingen spørreleker funnet.</td></tr>
			<?php } ?>
		</tbody>
	</table>
</div>

<script>
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
</script>
