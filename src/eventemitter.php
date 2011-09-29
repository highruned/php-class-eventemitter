<?

class EventEmitter {
	public $events;

	public function __construct() {
		$this->events = array();
	}
	
	private function init_event($name) {
		return $this->events[$name] = array(
			'handlers' => array(),
			'sticky' => false,
			'result' => array()
		);
	}

	public function add_event($name, $handler) {
		if(!isset($this->events[$name]))
			$this->init_event($name);
			
		if($this->events[$name]['sticky']) {
			call_user_func_array($handler, $this->events[$name]['result']);
			
			return;
		}

		$this->events[$name]['handlers'][] = $handler;
	}
	
	public function add_events($events) {
		foreach($events as $name => $handler) {
			$this->add_event($name, $handler);
		}
		
		return $this;
	}
	
	public function remove_event($name, $handler = null) {
		if(!isset($this->events[$name]))
			return;
			
		if($handler === null) {
			$this->events[$name]['handlers'] = array();
		}
		else {
			foreach($this->events[$name]['handlers'] as $key => $h1) {
				if($h1 === $handler) {
					unset($this->events[$name]['handlers'][$key]);
				}
			}
		}
		
		return $this;
	}
	
	public function once($name, $handler) {
		$self = &$this;
	
		$handler = function() use(&$self, $name, $handler) {
			$handler->__invoke();
		
			$self->remove_event($name);
		};
	
		return $self->add_event($name, $handler);
	}

	public function fire($name) {
		if(!isset($this->events[$name]))
			return $this;
		
		$result = func_get_args();
		array_shift($result);
		
		foreach($this->events[$name]['handlers'] as $event) {
			call_user_func_array($event, $result);
		}
		
		return $this;
	}
	
	public function fire_once($name) {
		if(!isset($this->events[$name]))
			$this->init_event($name);
		
		$result = func_get_args();
		array_shift($result);
		
		foreach($this->events[$name]['handlers'] as $event) {
			call_user_func_array($event, $result);
		}
		
		$this->events[$name]['sticky'] = true;
		$this->events[$name]['result'] = $result;
		
		return $this;
	}
	
	public function on($name, $handler) {
		return $this->add_event($name, $handler);
	}
	
	public function add_listener($name, $handler) {
		return $this->add_event($name, $handler);
	}
	
	public function remove_listener($name, $handler = null) {
		return $this->remove_event($name, $handler);
	}
	
	public function emit($name) {
		return call_user_func_array(array($this, 'fire'), func_get_args());
	}
	
	public function emit_once($name) {
		return call_user_func_array(array($this, 'fire_once'), func_get_args());
	}
}