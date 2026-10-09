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
				<?php foreach($quizzes as $quiz){ ?>
					<tr>
						<td class="quiz-date"><?php echo $quiz['date'] ? date('d.m.y', strtotime($quiz['date'])) : '-'; ?></td>
						<td><?php echo htmlspecialchars($quiz['quiz_type']); ?></td>
						<td><?php echo htmlspecialchars($quiz['name']); ?></td>
						<td class="align-center quiz-count"><?php echo (int) $quiz['question_count']; ?></td>
						<td class="quiz-info-cell">
							<span class="quiz-icons">
								<button type="button" class="quiz-info" onclick="quizInfoToggle(this)">i
									<span class="quiz-info-popup<?php echo empty($quiz['info']) ? ' empty' : ''; ?>">
										<?php echo !empty($quiz['info']) ? htmlspecialchars($quiz['info']) : 'Ingen info lagt inn for denne quizen ennå.'; ?>
									</span>
								</button>
								<!-- The client's own suggested document symbol (Symbol.png) - a plain
								     document with a folded corner and three text lines, not a copy of
								     any app's logo. This is the standard "file-lines" glyph shape. -->
								<span class="quiz-pdf" title="PDF-nedlasting er ikke klar ennå">
									<svg viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg"><path d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64zm384 32H256V0L384 128zM64 288c0 8.8 7.2 16 16 16H304c8.8 0 16-7.2 16-16s-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm16 64c-8.8 0-16 7.2-16 16s7.2 16 16 16H304c8.8 0 16-7.2 16-16s-7.2-16-16-16H80z"/></svg>
								</span>
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
</div>

<script src="<?php echo base_url('assets/frontend/js/sporreleker.js'); ?>"></script>
