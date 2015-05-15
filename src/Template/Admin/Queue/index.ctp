<?php
use Cake\Core\Configure;
use Cake\I18n\Time;
?>
<div class="page index">
<h2><?= __d('queue', 'Queue'); ?></h2>

<h3><?= __d('queue', 'Status'); ?></h3>
<?php 
if ($status) {
	$running = (time() - $status['time']) < MINUTE;

	echo ($running ? __d('queue', 'Running') : __d('queue', 'Not running')) . " (" . __d('queue', 'last {0}', Time::parse($status['time'])->i18nFormat()) . ")";
	
	echo '<div><small>'.__d('queue','Currently {0} worker(s) total.', $status['workers']).'</small></div>';
} else {
	echo 'n/a';
}
?>

<h3><?= __d('queue', 'Queued Tasks'); ?></h3>
<?= __d('queue', '{0} task(s) await processing',$current); ?>

<ol>
<?php
foreach ($pendingDetails as $item) {
	echo '<li>'.$item['jobtype'] . " (" . $item['reference'] . "):";
	echo '<ul>';
		echo '<li>'.__d('queue', 'Created').': '.$item['created'].'</li>';
		echo '<li>'.__d('queue', 'Fetched').': '.$item['fetched'].'</li>';
		echo '<li>'.__d('queue', 'Status').': '.$item['status'].'</li>';
		echo '<li>'.__d('queue', 'Progress').': '.$this->Number->toPercentage($item['progress']*100).'</li>';
		echo '<li>'.__d('queue', 'Failures').': '.$item['failed'].'</li>';
		echo '<li>'.__d('queue', 'Failure Message').': '.$item['failure_message'].'</li>';
	echo '</ul>';
	echo '</li>';
}
?>
</ol>

<h3><?= __d('queue', 'Statistics'); ?></h3>
<ul>
<?php
foreach ($data as $item) {
	echo '<li>'.$item['jobtype'] . ":";
	echo '<ul>';
		echo '<li>'.__d('queue', 'Finished Jobs in Database').': '.$item['num'].'</li>';
		echo '<li>'.__d('queue', 'Average Job existence').': '.$item['alltime'].'s</li>';
		echo '<li>'.__d('queue', 'Average Execution delay').': '.$item['fetchdelay'].'s</li>';
		echo '<li>'.__d('queue', 'Average Execution time').': '.$item['runtime'].'s</li>';
	echo '</ul>';
	echo '</li>';
}

if (empty($data->toArray())) {
	echo 'n/a';
}
?>
</ul>

<h3><?= __d('queue', 'Settings');?></h3>
<ul>
<?php
	$configurations = Configure::read('Queue');
	foreach ($configurations as $key => $configuration) {
		echo '<li>';
		if (is_dir($configuration)) {
			$configuration = str_replace(APP, 'APP' . DS, $configuration);
			$configuration = str_replace(DS, '/', $configuration);
		} elseif (is_bool($configuration)) {
			$configuration = $configuration ? 'true' : 'false';
		}
		echo h($key). ': ' . h($configuration);
		echo '</li>';
	}

?>
</ul>
</div>

<div class="actions">
	<ul>
		<li><?= $this->Form->postLink(__d('queue', 'Reset {0}', __d('queue', 'Queue Tasks')), ['action' => 'reset'], ['confirm' => __d('queue', 'Sure? This will completely reset the queue.')]); ?></li>
	</ul>
</div>
