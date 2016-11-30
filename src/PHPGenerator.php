<?php
require __DIR__ . '/../vendor/autoload.php';

class PHPGenerator {

    private $input = array();
    private $output = "";
    private $type = "";

    public function __construct( $output, $type = "Doctrine" )
    {
        $this->namespace = ucwords(preg_replace('/\//', ' \\ ', strtolower( $output )));
        $this->namespace = str_replace(' ', '', $this->namespace );
        $this->output = __DIR__ . '/../../' . $output;
        $this->type = $type;
    }

    public function input( $input = array() ){
        $this->input = $input;
        
    }

    public function run(){
        $tmpl = file_get_contents(__DIR__ . '/templates/'.$this->type.'.tmpl',TRUE);
        $m = new Mustache_Engine;
        $m->addHelper('member', [
            'lower' => function($value) { return strtolower((string) $value); },
            'upper' => function($value) { return strtoupper((string) $value); },
            'uc' => function($value) { return ucfirst((string) $value); },
            'underscore' => function($value) { return strtolower(preg_replace('/\B([A-Z])/', '_$1', (string) $value)); }
        ]);

        if (!file_exists($this->output)) {
            mkdir($this->output, 0775, true);
        }
        
        $this->input['namespace'] = $this->namespace;

        $output = $m->render($tmpl, $this->input);
        
        $class = fopen($this->output . '/' . ucfirst($this->input['class']['name']) . ".php", "w");
        fwrite($class, $output);
        fclose($class);
    }
}