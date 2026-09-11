<style>
.quiz-catalog{ max-width:1100px; margin:30px auto; padding:0 20px; font-family:'Agency', Arial, sans-serif; }
.quiz-catalog h1{ font-size:26px; margin-bottom:20px; }
.quiz-filter{ margin-bottom:15px; position:relative; display:inline-block; }
.quiz-filter-btn{ background:#e8e8e8; border:1px solid #bbb; padding:8px 16px; cursor:pointer; font-size:15px; }
.quiz-filter-menu{ display:none; position:absolute; top:100%; left:0; background:#fff; border:1px solid #bbb; min-width:160px; z-index:10; box-shadow:0 2px 6px rgba(0,0,0,.15); }
.quiz-filter-menu a{ display:block; padding:8px 14px; color:#222; text-decoration:none; }
.quiz-filter-menu a:hover, .quiz-filter-menu a.active{ background:#f6c94c; }
.quiz-filter.open .quiz-filter-menu{ display:block; }
table.quiz-table{ width:100%; border-collapse:collapse; }
table.quiz-table th{ background:#f6c94c; text-align:left; padding:10px 12px; border-bottom:2px solid #d6ad1f; }
table.quiz-table th a{ color:#222; text-decoration:none; }
table.quiz-table td{ padding:10px 12px; border-bottom:1px solid #eee; }
table.quiz-table tr:nth-child(even) td{ background:#fbfbfb; }
.quiz-table th.num, .quiz-table td.num{ text-align:right; }
.quiz-info{ display:inline-block; width:18px; height:18px; border-radius:50%; background:#5b9bd5; color:#fff; text-align:center; line-height:18px; font-size:12px; font-weight:bold; cursor:pointer; position:relative; }
.quiz-info .quiz-info-popup{ display:none; position:absolute; right:0; top:24px; background:#333; color:#fff; padding:8px 12px; border-radius:4px; width:220px; font-size:13px; text-align:left; z-index:5; }
.quiz-info:hover .quiz-info-popup, .quiz-info:focus .quiz-info-popup{ display:block; }
.quiz-empty{ padding:30px 0; color:#666; }
</style>

<div class="quiz-catalog">
	<h1>Velkommen til BestefarQuiz</h1>

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
				<th class="num"><?php echo quiz_sort_link('Ant spørsmål', 'count', $sort, $dir, $quiz_type); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($quizzes)){ ?>
				<?php foreach($quizzes as $quiz){ ?>
					<tr>
						<td><?php echo date('d.m.y', strtotime($quiz['date'])); ?></td>
						<td><?php echo htmlspecialchars($quiz['quiz_type']); ?></td>
						<td><?php echo htmlspecialchars($quiz['name']); ?></td>
						<td class="num"><?php echo (int) $quiz['question_count']; ?></td>
						<td>
							<?php if(!empty($quiz['info'])){ ?>
							<span class="quiz-info" tabindex="0">i
								<span class="quiz-info-popup"><?php echo htmlspecialchars($quiz['info']); ?></span>
							</span>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			<?php }else{ ?>
				<tr><td colspan="5" class="quiz-empty">Ingen spørreleker funnet.</td></tr>
			<?php } ?>
		</tbody>
	</table>
</div>
