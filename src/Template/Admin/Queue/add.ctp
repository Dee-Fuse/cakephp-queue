<h2><?= __d('queue','Available Tasks');?></h2>
<?php
foreach($tasks as $task)
{
	 echo $this->Form->postLink(__d('queue', 'Add "{0}" Task to Queue',  substr($task,5)), ['action' => 'add', $task]);
	 echo "<br>";
}
?>
<br>
<?php
if($current > 0 && $status['workers'] == 0)
{
	echo __d('queue', 'There are {0} Tasks in Queue, but no worker is running!', $current);
	echo "<br>";
	echo __d('queue', 'To start a worker, open a Console windows and type:');
	echo '<p style="font-family:Courier New", Courier, monospace;">';
	echo "cd ".ROOT."<br>";
	echo "bin".DS."cake Queue runworker";
	echo "</p>";
}elseif($status['workers'] > 0){
	echo __d('queue','{0} workers are running and {1} Tasks awaiting processing.',$status['workers'],$current);
}
?>