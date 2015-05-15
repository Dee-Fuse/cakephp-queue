<?php
namespace Queue\Controller\Admin;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Queue\Controller\AppController;

class QueueController extends AppController {

	public $modelClass = 'Queue.QueuedTask';

	/**
	 * Overwrite shell initialize to dynamically load all Queue Related Tasks.
	 *
	 * @return void
	 */
	public function initialize() {
		$this->loadModel('Queue.QueuedTasks');
		$this->loadComponent('Flash');
	}

	/**
	 * QueueController::beforeFilter()
	 *
	 * @return void
	 */
	public function beforeFilter(Event $event) {
		$this->QueuedTasks->initConfig();

		parent::beforeFilter($event);
	}

	/**
	 * Admin center.
	 * Manage queues from admin backend (without the need to open ssh console window).
	 *
	 * @return void
	 */
	public function index() {
		$status = $this->_status();

		$current = $this->QueuedTasks->getLength();
		$pendingDetails = $this->QueuedTasks->getPendingStats();
		$data = $this->QueuedTasks->getStats();

		$this->set(compact('current', 'data', 'pendingDetails', 'status'));
	}

	/**
	 * Truncate the queue list / table.
	 *
	 * @return void
	 * @throws MethodNotAllowedException when not posted
	 */
	public function reset() {
		$this->request->allowMethod('post');
		$res = $this->QueuedTasks->deleteAll([]);

		if ($res) {
			$message = __d('queue', 'OK');
			$class = 'success';
		} else {
			$message = __d('queue', 'Error');
			$class = 'error';
		}

		$this->Flash->{$class}($message);

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * QueueController::_status()
	 *
	 * If pid loggin is enabled, will return an array with
	 * - time: int Timestamp
	 * - workers: int Count of currently running workers
	 *
	 * @return array Status array
	 */
	protected function _status() {
		if (!($pidFilePath = Configure::read('Queue.pidfilepath'))) {
			return [];
		}
		$file = $pidFilePath . 'queue.pid';
		if (!file_exists($file)) {
			return [];
		}

		$sleepTime = Configure::read('Queue.sleeptime');
		$thresholdTime = time() - $sleepTime;
		$count = 0;
		foreach (glob($pidFilePath . 'queue_*.pid') as $filename) {
			$time = filemtime($filename);
			if ($time >= $thresholdTime) {
				$count++;
			}
		}

		$res = [
			'time' => filemtime($file),
			'workers' => $count,
		];
		return $res;
	}

	/**
	 * Add Job with UI
	 *
	 * @param string $task Task to add
	 * @return void
	 */
	public function add($task = null) {
		if ($this->request->is('post')) {
			$task = substr($task, 5);
			$this->loadModel('Queue.QueuedTasks');
			$res = $this->QueuedTasks->createJob($task);
			if ($res) {
				$message = __d('queue', '{0} Added to queue');
				$class = 'success';
			} else {
				$message = __d('queue', 'Error');
				$class = 'error';
			}
			$this->Flash->{$class}($message);
			return $this->redirect(['action' => 'add']);

		}

		$tasks = array();
		$paths = App::path('Shell/Task');

		foreach ($paths as $path) {
			$Folder = new Folder($path);
			$res = array_merge($tasks, $Folder->find('Queue.+\.php'));
			foreach ($res as &$r) {
				$r = basename($r, 'Task.php');
			}
			$tasks = $res;
		}
		$plugins = Plugin::loaded();
		foreach ($plugins as $plugin) {
			$pluginPaths = App::path('Shell/Task', $plugin);
			foreach ($pluginPaths as $pluginPath) {
				$Folder = new Folder($pluginPath);
				$res = $Folder->find('Queue.+Task\.php');
				foreach ($res as &$r) {
					$r = /*$plugin . '.' .*/ basename($r, 'Task.php');
				}
				$tasks = array_merge($tasks, $res);
			}
		}
		$status = $this->_status();

		$current = $this->QueuedTasks->getLength();

		$this->set(compact("tasks", 'status', 'current'));
	}

}
