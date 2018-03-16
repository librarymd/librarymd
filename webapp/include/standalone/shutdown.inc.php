<?php
// managing the shutdown callback events:
class shutdownScheduler {
    private $callbacks; // array to store user callbacks
   
    public function __construct() {
        $this->callbacks = array();
        register_shutdown_function(array($this, 'callRegisteredShutdown'));
    }
    public function registerShutdownEvent() {
        $callback = func_get_args();
        $this->callbacks[] = $callback;
        return true;
    }
    public function callRegisteredShutdown() {
        ob_flush();
        foreach ($this->callbacks as $arguments) {
            $callback = array_shift($arguments);
            call_user_func_array($callback, $arguments);
        }
    }
}