<?php
// lib/php/init.php 20150101 - 20190320
// Copyright (C) 2015-2019 Mark Constable <markc@renta.net> (AGPL-3.0)

class Init
{
    private $t = null;

    public function __construct(object $g)
    {
error_log(__METHOD__);

error_log('GET=' . var_export($_GET, true));

error_log('POST=' . var_export($_POST, true));

        $g->cfg['host'] = $g->cfg['host'] ?? getenv('HOSTNAME');

        session_start();

        //$_SESSION = []; // to reset session for testing
//error_log('SESSION=' . var_export($_SESSION, true));

        util::cfg($g);
        $g->in = util::esc($g->in);
        $g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);

        if (!isset($_SESSION['c'])) $_SESSION['c'] = Util::random_token(32);
        util::ses('o'); util::ses('m'); util::ses('l');
        $t = util::ses('t', '', $g->in['t']);

        $t1 = 'themes_' . $t . '_' . $g->in['o'];
        $t2 = 'themes_' . $t . '_theme';

        $this->t = $thm = class_exists($t1) ? new $t1($g)
            : (class_exists($t2) ? new $t2($g) : new Theme($g));

        $p  = 'plugins_' . $g->in['o'];
        if (class_exists($p)) {
            $g->in['a'] ? util::chkapi($g) : util::remember($g);
            $g->out['main'] = (string) new $p($thm);
        } else $g->out['main'] = "Error: no plugin object!";

        if (empty($g->in['x']))
            foreach ($g->out as $k => $v)
                $g->out[$k] = method_exists($thm, $k) ? $thm->$k() : $v;
    }

    public function __toString() : string
    {
error_log(__METHOD__);

        $g = $this->t->g;
        $x = $g->in['x'];
        if ($x === 'text') {
            return preg_replace('/^\h*\v+/m', '', strip_tags($g->out['main']));
        } elseif ($x === 'json') {
            header('Content-Type: application/json');
            return $g->out['main'];
        } elseif ($x) {
            $out = $g->out[$x] ?? '';
            if ($out) {
                header('Content-Type: application/json');
                return json_encode($out, JSON_PRETTY_PRINT);
            }
        }
        return $this->t->html();
    }

    public function __destruct()
    {
//error_log('SESSION=' . var_export($_SESSION, true));
        error_log($_SERVER['REMOTE_ADDR'].' '.round((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']), 4));
    }
}

function dbg($var = null)
{
    if (is_object($var))
        error_log(ReflectionObject::export($var, true));
    ob_start();
    print_r($var);
    $ob = ob_get_contents();
    ob_end_clean();
    error_log($ob);
}

?>
