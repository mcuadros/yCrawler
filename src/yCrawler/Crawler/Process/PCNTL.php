<?php
namespace yCrawler;
declare(ticks = 1);

class Crawler_Process_PCNTL {
    private $_jobs = Array();
    private $_signals = Array();

    private $_maxExecutionTime;
    private $_pid;
    private $_callback;

    private $_counterGWJ = 0;

    public function __construct(){
        $this->_pid = getmypid();
        pcntl_signal(SIGCHLD, array($this, 'jobSignalHandler'));
        
        $this->_maxExecutionTime = Config::get('max_execution_time');
        
        //TODO: Make a more elegant solution, due to call_user_func_array, bug related
        //      to pass by refered be make a direct call
        if ( $ncurses = Output::getInstance('yCrawler\Output_Driver_Ncurses') ) {
            $ncurses->linkJobs($this->_jobs);
        }

        if ( $console = Output::getInstance('yCrawler\Output_Driver_Console') ) {
            $console->linkJobs($this->_jobs);
        }
    }

    public function getWaitingJobs() {
        $this->_counterGWJ++;
        //Every 20 cycles we check if we have zombie process!!
        if ( $this->_counterGWJ > 5 ) {
            $this->_counterGWJ = 0;
            foreach($this->_jobs as $pid => &$job) {
                if ( (time() - $job[0])  >  $this->_maxExecutionTime ) {
                    Output::log('Killing process '. $pid.  ' alive for more than '.$this->_maxExecutionTime.' secs' , Output::WARNING);  
                    
                    $job[1]->setStatus(Request::STATUS_RETRY);
                    $this->jobDone($pid, $job[1]);     

                    posix_kill($pid,SIGTERM);
                    unset($this->_jobs[$pid]);       
                }
            }
        } 

        pcntl_signal_dispatch();
        return count($this->_jobs);
    }

    public function setJobDoneCallback(array $callback) {
        $this->_callback = &$callback;
    }

    public function sendJob(Document &$document){
        pcntl_signal_dispatch();

        $pid = pcntl_fork();
        if($pid == -1){
            throw new Exception('Could not launch new job, exiting');
            return false;
        } else if ($pid) {
            $this->_jobs[$pid] = Array(time(), &$document);
            if( isset($this->_signals[$pid]) ) {
                $this->jobSignalHandler(SIGCHLD, $pid, $this->_signals[$pid]);
                unset($this->_signals[$pid]);
            }
        } else {
            Output::log('Forked proccess for documentId: ' . $document->getId(), Output::DEBUG);
            $this->write(getmypid(),  $document->parse());
            exit(0);
        }
        
        return $pid;
    }

    public function jobSignalHandler($signo, $pid = null, $status = null){  
        if( !$pid ){
            $pid = pcntl_waitpid(-1, $status, WNOHANG);
        }

        //Make sure we get all of the exited children
        while( $pid > 0 ){
            if( $pid && isset($this->_jobs[$pid]) ){
                //pcntl_wexitstatus($status);
                unset($this->_jobs[$pid]);                  
                return $this->jobDone($pid);     
            } else if ( $pid ) {
                $this->_signals[$pid] = $status;
            }

            $pid = pcntl_waitpid(-1, $status, WNOHANG);
        }

        return true;
    }

    private function jobDone($pid, Document &$document = null) {
        if ( !$this->_callback ) {
            Output::log('No jobDoneCallback configured', Output::ERROR);
            return false;
        } else if ( $document ) {
            return call_user_func_array($this->_callback, Array($pid, $document));
        } else if ( $document = $this->read($pid) ) {
            return call_user_func_array($this->_callback, Array($pid, $document));
        } else {
            Output::log('Unable to recover document from job ('.$pid.')', Output::ERROR);
        }  
    }

    private function write($pid, Document &$document) {
        return Cache::driver('APC')->set($pid, $document);
    }

    private function read($pid) {
        return Cache::driver('APC')->get($pid);
    }
 
}