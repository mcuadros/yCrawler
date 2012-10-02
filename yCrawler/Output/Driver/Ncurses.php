<?php
namespace yCrawler;

class Output_Driver_Ncurses extends Output_Base {
    private $_ncurses;
    private $_colmuns;
    private $_lines;
    private $_jobs;

    private $_wins = Array();

    public function __construct() {
        $this->_ncurses = ncurses_init();

        $this->_wins['main'] = ncurses_newwin(0, 0, 0, 0); 
        ncurses_getmaxyx($this->_wins['main'], $this->_lines, $this->_columns);
        ncurses_border(0,0, 0,0, 0,0, 0,0);
    }

    private function refresh() {
        ncurses_attron(NCURSES_A_REVERSE);
        ncurses_mvaddstr(0,1,"Traceroute example");
        ncurses_attroff(NCURSES_A_REVERSE);

       foreach($this->_wins as $key => &$win) {
            if ( $key != 'main' ) true;
            
        }

        $this->_wins['lower_frame'] = ncurses_newwin(20, $this->_columns - 3, $this->_lines - 21, 1);
        ncurses_wborder($this->_wins['lower_frame'], 0,0, 0,0, 0,0, 0,0); // border it
        ncurses_wrefresh($this->_wins['lower_frame']);

        $this->_wins['log'] = ncurses_newwin(18, $this->_columns - 5, $this->_lines - 20, 2);
        

        $line = 0;
        $logLines = count($this->_log);
        while($line < 18) {
            $log = $logLines-$line-1;
            if ( $log < 0 ) $log = 0;

            ncurses_mvwaddstr($this->_wins['log'], $line, 1, substr(count($this->_jobs).'!'.$this->_log[$log], 0, $this->_columns - 3));
            $line++;
        }
        ncurses_wrefresh($this->_wins['log']);

        $this->_wins['process'] = ncurses_newwin($this->_lines - 22, $this->_columns - 3, 1, 1);
        ncurses_wborder($this->_wins['process'], 0,0, 0,0, 0,0, 0,0); // border it


        $line = 1; 
        $keys = array_keys($this->_jobs);
        $jobs = count($this->_jobs);
        if ( $jobs > 20 ) { $max = 20; } else { $max = $jobs; }

        while($line < $max) {
            $job = $jobs-$line;
            if ( $job < 0 ) $job = 0;
            ncurses_mvwaddstr(
                $this->_wins['process'], 
                $line, 
                1, 
                substr($this->_jobs[$keys[$job]]->getUrl(), 0, $this->_columns - 3));
            $line++;
        }

        ncurses_wrefresh($this->_wins['process']);
        ncurses_erase();

        ncurses_move(-1,1); // toss cursor out of sight.
        foreach($this->_wins as $key => &$win) {
            if ( $key != 'main' ) true;
            
            
        }


    }


    public function linkJobs(&$jobs) {
        $this->_jobs = &$jobs;
    }


    public function log($string, $level = Output::WARNING) {
        $string = str_replace(Array("\n","\r","\t"), '', $string);
        
        $this->_log[] = Output::levelToString($level) . ' ' . $string;
        return $this->refresh();
    }
}