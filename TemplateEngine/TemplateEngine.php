<?php

/**
 * Created by PhpStorm.
 * User: Toby
 * Date: 13/01/2017
 * Time: 13:15
 */

namespace Framework\TemplateEngine;

require_once("autoloader.php");

final class TemplateEngine {
    private $file;

    public function __construct($file) {
        $this->file = file_get_contents($file);
    }

    /**
     * @return bool|string
     */
    public function getFile() {
        return $this->file;
    }

    public function compile() {
        foreach (get_class_methods(self::class) as $func) {
            // Each replacement function begins with 'match'
            // Therefor we check all the functions in this class and if it start with 'match' we execute it
            if(substr($func, 0, 5) == "match") $this->{$func}();
        }
    }
    
    private function matchComment() {
        $this->file = preg_replace("/{#\s*(.*)\s*#}/", "<? // $1?>", $this->file);
    }

    private function matchVar() {
        $this->file = preg_replace("/{{\s*(.*)\s*}}/", "<?= htmlspecialchars( $1) ?>", $this->file);
    }
    private function matchEscapedVar() {
        $this->file = preg_replace("/{!\s*(.*)\s*!}/", "<?= $1?>", $this->file);
    }

    private function matchIf() {
        $this->file = preg_replace("/{%\s*if (.*)\s*%}/", "<? if ( $1): ?>", $this->file);
    }
	
	private function matchElseIf() {
        $this->file = preg_replace("/{%\s*elseif (.*)\s*%}/", "<? elseif ( $1): ?>", $this->file);
    }

    private function matchElse() {
        $this->file = preg_replace("/{%\s*else\s*%}/", "<? else: ?>", $this->file);
    }

    private function matchEndIf() {
        $this->file = preg_replace("/{%\s*endif\s*%}/", "<? endif ?>", $this->file);
    }

    private function matchFor() {
        $this->file = preg_replace("/{%\s*for (.*)\s*%}/", "<? for ( $1): ?>", $this->file);
    }

    private function matchForeach() {
        $this->file = preg_replace("/{%\s*foreach (.*)\s*%}/", "<? foreach ( $1): ?>", $this->file);
    }

    private function matchForEmpty() {
        $this->file = preg_replace("/{%\s*empty\s*%}/", "<? else: ?>", $this->file);
    }

    private function matchEndForeach() {
        $this->file = preg_replace("/{%\s*endforeach\s*%}/", "<? endforeach ?>", $this->file);
    }

    private function matchEndFor() {
        $this->file = preg_replace("/{%\s*endfor\s*%}/", "<? endfor ?>", $this->file);
    }

    private function matchWhile() {
        $this->file = preg_replace("/{%\s*while (.*)\s*%}/", "<? while ( $1): ?>", $this->file);
    }

    private function matchEndWhile() {
        $this->file = preg_replace("/{%\s*endwhile\s*%}/", "<? endwhile ?>", $this->file);
    }

    private function matchInclude() {
        $this->file = preg_replace_callback("/{%\s*include (.*)\s*%}/", function($m){
            $split_string = explode(" ", $m[1]);

            // Import has to arguments if size of $split_string == 1
            if(sizeof($split_string) == 1) {
                return "<? $" . "this->include(" . $split_string[0] . "); ?>";
            } else {
                $vars = array_slice($split_string, 2);

                foreach ($vars as $i => $var) {
                    if(empty($var)) {
                        unset($vars[$i]);
                        continue;
                    }
                    $split = explode("=", $var);
                    $vars[$i] = "\"" . $split[0] ."\"" . " => " . $split[1];
                }

                return "<? $" . "this->include(" . $split_string[0] . ", $" . "with=[" . implode(", " , $vars) . "]); ?>";
            }
        }, $this->file);
    }

    private function matchStatic() {
        $this->file = preg_replace_callback("/{%\s*static (.*)\s*%}/", function($m){
            return "<? $" . "this->static(" . explode(" ", $m[1])[0] . "); ?>";
        }, $this->file);
    }

    private function matchCSRFToken() {
        $this->file = preg_replace_callback("/{%\s*csrf_token\s*%}/", function($m){
            return "<?= $" . "this->csrf_token() ?>";
        }, $this->file);
    }

    private function matchExtend() {
        $this->file = preg_replace_callback("/{%\s*extends (.*)\s*%}/", function($m){
            return "<? $" . "this->extend(" . explode(" ", $m[1])[0] . "); ?>";
        }, $this->file);
    }

    private function matchDefineBlock() {
        $this->file = preg_replace_callback("/{%\s*define (.*)\s*%}/", function($m){
            return "<? $" . "this->define('" . explode(" ", $m[1])[0] . "'); ?>";
        }, $this->file);
    }


    private function matchStartBlock() {
        $this->file = preg_replace_callback("/{%\s*block (.*)\s*%}/", function($m){
            return "<? $" . "this->block('" . explode(" ", $m[1])[0] . "'); ?>";
        }, $this->file);
    }

    private function matchEndBlock() {
        $this->file = preg_replace_callback("/{%\s*endblock\s*%}/", function($m){
            return "<? $" . "this->endblock(); ?>";
        }, $this->file);
    }

    private function matchAutoEscape() {
        $this->file = preg_replace_callback("/{%\s*autoescape (.*)\s*%}/", function($m){
            if(!empty($m[1]))
                return "<? $" . "this->autoescape('" . explode(" ", $m[1])[0] . "'); ?>";
            else
                return "<? $" . "this->autoescape(); ?>";
        }, $this->file);
    }

    private function matchEndAutoEscape() {
        $this->file = preg_replace_callback("/{%\s*endautoescape\s*%}/", function($m){
            return "<? $" . "this->endautoescape(); ?>";
        }, $this->file);
    }

    private function matchSpaceless() {
        $this->file = preg_replace_callback("/{%\s*spaceless\s*%}/", function($m){
            return "<? $" . "this->spaceless(); ?>";
        }, $this->file);
    }

    private function matchEndSpaceless() {
        $this->file = preg_replace_callback("/{%\s*endspaceless\s*%}/", function($m){
            return "<? $" . "this->endspaceless(); ?>";
        }, $this->file);
    }
};

//$compiler = new TemplateEngine("tests/test_view.php");
//
//$compiler->compile();
//
//echo $compiler->getFile();