<link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/sporreleker.css'); ?>">

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
				<th><?php echo quiz_sort_link('Dato', 'date', $sort, $dir, $quiz_type); ?></th>
				<th><?php echo quiz_sort_link('Kategorier', 'category', $sort, $dir, $quiz_type); ?></th>
				<th><?php echo quiz_sort_link('Navn', 'name', $sort, $dir, $quiz_type); ?></th>
				<th class="align-center"><?php echo quiz_sort_link('Ant spørsmål', 'count', $sort, $dir, $quiz_type); ?></th>
				<th></th>
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
						<td class="align-center quiz-info-cell">
							<span class="quiz-icons">
								<button type="button" class="quiz-info" onclick="quizInfoToggle(this)">i
									<span class="quiz-info-popup<?php echo empty($quiz['info']) ? ' empty' : ''; ?>">
										<?php echo !empty($quiz['info']) ? htmlspecialchars($quiz['info']) : 'Ingen info lagt inn for denne quizen ennå.'; ?>
									</span>
								</button>
								<span class="quiz-pdf" title="PDF-nedlasting er ikke klar ennå">
									<svg viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg"><path d="M181.9 256.1c-5-16-4.9-46.9-2-46.9 8.4 0 7.6 36.9 2 46.9zm-1.7 47.2c-7.7 20.2-17.3 43.3-28.4 62.7 18.3-7 39-17.2 62.9-21.9-12.7-9.6-24.9-23.4-34.5-40.8zM86.1 428.1c0 .8 13.2-5.4 34.9-40.2-6.7 6.3-29.1 24.5-34.9 40.2zM248 160h136v328c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V24C0 10.7 10.7 0 24 0h200v136c0 13.2 10.8 24 24 24zm-8 171.8c-20-12.2-33.3-29-42.7-53.8 4.5-18.5 11.6-46.6 6.2-64.2-4.7-29.4-42.4-26.5-47.8-6.8-5 18.3-.4 44.1 8.1 77-11.6 27.6-28.7 64.6-40.8 85.8-.1 0-.1.1-.2.1-27.1 13.9-73.6 44.5-54.5 68 5.6 6.9 16 10 21.5 10 17.9 0 35.7-18 61.1-61.8 25.8-8.5 54.1-19.1 79-23.2 21.7 11.8 47.1 19.5 64 19.5 29.2 0 31.2-32 19.7-43.4-13.9-13.6-54.3-9.7-73.6-7.2zM377 105L279 7c-4.5-4.5-10.6-7-17-7h-6v128h128v-6.1c0-6.3-2.5-12.4-7-16.9zm-74.1 255.3c4.1-2.7-2.5-11.9-42.8-9 37.1 15.8 42.8 9 42.8 9z"/></svg>
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

<script src="<?php echo base_url('assets/frontend/js/sporreleker.js'); ?>"></script>
