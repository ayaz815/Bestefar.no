<style>
/* Colors and font match the real site (theme/head.php): agency_fbbold body
   font, #ffc000 gold and #9dc3e6 blue - which are also the two colors used
   in the Catalog_Quiz.pdf mockup, so this lines up with both. Column
   alignment (Dato right, Kategorier left, Navn left, Ant spørsmål right)
   and the blue header / gold rows follow that mockup exactly. */
.quiz-catalog{ max-width:1100px; margin:30px auto; padding:0 20px; font-family:'agency_fbbold', Arial, sans-serif; min-height:60vh; color:#000; }
.quiz-catalog-head{ display:flex; justify-content:space-between; align-items:baseline; margin-bottom:15px; }
.quiz-catalog h1{ font-size:28px; margin:0; text-decoration:underline; }
.quiz-catalog-count{ font-size:16px; }
.quiz-filter{ margin-bottom:15px; position:relative; display:inline-block; }
.quiz-filter-btn{ background:#ffc000; border:1px solid #d6a600; padding:8px 16px; cursor:pointer; font-size:16px; }
.quiz-filter-menu{ display:none; position:absolute; top:100%; left:0; background:#fff; border:1px solid #bbb; min-width:160px; z-index:10; box-shadow:0 2px 6px rgba(0,0,0,.15); }
.quiz-filter-menu a{ display:block; padding:8px 14px; color:#222; text-decoration:none; }
.quiz-filter-menu a:hover, .quiz-filter-menu a.active{ background:#9dc3e6; }
.quiz-filter.open .quiz-filter-menu{ display:block; }
table.quiz-table{ width:100%; border-collapse:collapse; font-size:16px; }
table.quiz-table th{ background:#9dc3e6; text-align:left; padding:10px 12px; }
table.quiz-table th a{ color:#000; text-decoration:none; }
table.quiz-table td{ padding:10px 12px; background:#ffc000; border-bottom:1px solid #f0f0f0; }
.quiz-table th.align-right, .quiz-table td.align-right{ text-align:right; }
.quiz-info{ display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:50%; background:#5b9bd5; color:#fff; font-size:13px; font-weight:bold; font-family:Georgia, serif; font-style:italic; cursor:pointer; border:none; padding:0; position:relative; }
.quiz-info-popup{
	display:none;
	position:absolute;
	right:0;
	top:28px;
	background:#fff;
	color:#222;
	padding:10px 14px;
	border-radius:6px;
	width:240px;
	font-family:Arial, sans-serif;
	font-size:13px;
	line-height:1.4;
	text-align:left;
	font-weight:normal;
	text-decoration:none;
	box-shadow:0 4px 14px rgba(0,0,0,.2);
	border:1px solid #ddd;
	z-index:20;
}
.quiz-info-popup::before{
	content:"";
	position:absolute;
	top:-7px;
	right:6px;
	width:12px;
	height:12px;
	background:#fff;
	border-left:1px solid #ddd;
	border-top:1px solid #ddd;
	transform:rotate(45deg);
}
.quiz-info-popup.empty{ color:#888; font-style:italic; }
.quiz-info.open .quiz-info-popup{ display:block; }
.quiz-empty{ padding:30px 0; color:#666; text-decoration:none !important; }
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
