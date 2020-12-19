<?php

class ViewMustache
{
  private $view_dir = '';
  private $cache_dir = '';
  private $helper_dir = '';
  private $param = [];
  private $helper_include = '';
  private const MUSTACHE_EXTENSION = '.view.php';
  private const PHP_KEYWORD = [
    'require',
    'foreach',
    'endforeach',
    'for',
    'endfor',
    'var',
    'if',
    'elseif',
    'else',
    'endif',
    'helper',
  ];
  public $use_cache = false;


  public function __construct(string $view_dir, string $cache_dir, string $helper_dir)
  {
    $this->view_dir = $view_dir;
    $this->cache_dir = $cache_dir;
    $this->helper_dir = $helper_dir;
  }
  public function helper($helper_names)
  {
    $helper_array = '';
    foreach ($helper_names as $helper_name) {
      $helper_file = sprintf("%s/%s.php", rtrim($this->helper_dir, '/'), ltrim($helper_name, '/'));
      $helper_array .= "<?php include_once( '{$helper_file}' ); ?>";
    }
    $this->helper_include = $helper_array;
  }

  public function render(string $file_name, array $param = []): string
  {
    $this->param = array_merge($this->param, $param);
    $view ="";
    if($this->use_cache === true){
      $view = $this->makeCache($file_name);
    }else{
      $view_file = $this->view_dir . $file_name . self::MUSTACHE_EXTENSION;
      $view = $this->write($view_file);
    }
    extract($this->param);

    ob_start();
    ob_implicit_flush(0);
    // require $view;
    if($this->use_cache === true){
      require $view;
    }else{
      //https://amaraimusi.sakura.ne.jp/sample/php/a020/eval_demo/eval_demo.php
      //文字列をPHPコードとして評価する
      eval( "?>" . $view.'<?php');
    }
    return ob_get_clean();
  }

  private function searchPHPKeyword($word): bool
  {
    return preg_match('/^(' . join('|', self::PHP_KEYWORD) . ')/', $word) === 1;
  }

  /**
   * sha1_file()でファイルのハッシュ値の計算をし、
   * テンプレートファイルが更新される毎に値が変わるので、
   * 変更された場合、新規のキャッシュファイルが生成される。
   */
  private function makeCache(string $file_name): string
  {
    $view_file = $this->view_dir . $file_name . self::MUSTACHE_EXTENSION;
    $cache_file = $this->cache_dir . sha1_file($view_file) . '.cache';

    if ($this->checkCache($view_file, $cache_file)) {
      return $cache_file;
    }

    $this->write($view_file, $cache_file);
    return $cache_file;
  }

  private function write(string $view_file, string $cache_file = '')
  {
    $source = $this->helper_include;
    $source .= file_get_contents($view_file);

    // 改行削除
    $source = str_replace(PHP_EOL, '', $source);

    // ムスタッシュキーワード検索
    $source = preg_replace_callback('#\{\{(.*?)\}\}#', function ($m) {
      $v = trim($m[1]);

      // 予約語なら命令を実行する
      if ($this->searchPHPKeyword($v)) {
        // requireの場合、親ファイル呼び出し
        if (strpos($v, 'require') === 0) {
          $file = preg_split("/\s/", $v)[1];
          $file = trim($file, "\'\"");
          return $this->render($file);
        }
        if (strpos($v, 'var') === 0) {
          //var の場合
          //$var_replace = str_replace('var', '', $v);
          //先頭のみ削除
          $var_replace = preg_replace("/^var/", '', $v);
          $var_replace = trim($var_replace);
          return '<?php ' . $var_replace . ' ?>';
        }

        if (strpos($v, 'helper') === 0) {
          //先頭を削除 から後方の)を削除
          // バックスラッシュでメタ文字をエスケープ括弧
          $helper_replace = preg_replace("/^helper\(/", '', $v);
          $helper_replace = trim(rtrim($helper_replace, ')'));

          $helper_tag = $this->Tags_helper($helper_replace);
          return '<?php ' . $helper_tag . ' ?>';
        }

        return '<?php ' . $v . ' ?>';
      }


      if (strpos($v, '|')) {
        //最初に見つかった「|」から配列に格納する
        $par = explode('|', $v);
        $variable_name = array_shift($par);
        foreach ($par as $mod) {
          $variable_name = $this->modifier_escape_over_smarty($variable_name, trim($mod));
        }
        return '<?= ' . $variable_name . ' ?>';
      }
      // そうでないなら変数として扱う
      return '<?= ' . $v . ' ?>';
    }, $source);

    if ($this->use_cache === true) {
      file_put_contents($cache_file, $source, LOCK_EX);
      // if (file_put_contents($cache_file, $source, LOCK_EX)) {
      //   //新規作成するときに古いキャッシュファイルをチェックして7日前なら削除
      //   $this->deleteCache();
      // }
    } else {
      return $source;
    }

    // file_put_contents($cache_file, $source, LOCK_EX);
  }

  /**
   * 下記の条件が全てtrueの場合のみキャッシュを使う
   * 
   * requireで呼び出しているファイルのキャッシュが存在する
   * 現在のビューのキャッシュファイルが存在する
   * キャッシュを使う
   */
  private function checkCache(string $view_file, string $cache_file): bool
  {
    if (!$this->checkRequireCache($view_file)) {
      return false;
    }
    if (!is_file($cache_file)) {
      return false;
    }

    return true;
  }

  /**
   * require命令から呼び出されているファイルのキャッシュを確認する
   */
  private function checkRequireCache(string $file_name): bool
  {
    $source = file_get_contents($file_name);
    preg_match_all('#\{\{(.*require .*)\}\}#', $source, $match_list);
    $requires = $match_list[1];

    foreach ($requires as $require) {
      $str = preg_split("/\s/", $require)[2];
      $require_file = trim($str, "\"\'");

      $cache_file = $this->cache_dir . sha1_file($this->view_dir . $require_file . self::MUSTACHE_EXTENSION) . '.cache';
      if (!is_file($cache_file)) {
        return false;
      }
    }

    return true;
  }

  private function deleteCache()
  {
    // ディレクトリが存在するかチェック
    if (file_exists($this->cache_dir)) {

      // 指定の拡張子のファイルを取得
      $cache_files  = glob($this->cache_dir . '*.cache');

      // この日付以前のファイルを削除する(これは1分前指定)
      $target_day = strtotime(date('YmdHis') . '-1 second');

      foreach ($cache_files as $_cache_files) {
        // ファイルの最終更新日を取得
        $m_date = filemtime($_cache_files);

        // 最終更新日が指定日より前であれば削除
        if (strtotime(date('YmdHis', $m_date)) < $target_day) {
          unlink($_cache_files);
        }
      }
    }
  }

  private function Tags_helper($tag)
  {
    //配列で区切った後 listで変数に分ける
    list($helper_function, $vars) = explode(',', $tag, 2);
    $helper_function = trim($helper_function, " \t\n\"'");  // 空白とクォートを削除
    //$helper_name = trim($helper_name,"");
    $src = " if(function_exists(\"$helper_function\")) { echo $helper_function($vars); }";
    return $src;
  }







  /**
   *  Smartyのescapeに準拠
   */
  private function modifier_escape_over_smarty($string, $esc_type = 'html', $char_set = NULL)
  {
    // if ($char_set == NULL) {
    //   $char_set = $this->skConf['ENCODE']['INTERNAL'];
    // }
    switch ($esc_type) {
      case 'upper':
      case 'toupper':
      case 'strtoupper':
        return "strtoupper($string)";

      case 'lower':
      case 'tolower':
      case 'strtolower':
        return "strtolower($string)";

      case 'strip':
      case 'strip_tags':
        return "strip_tags($string)";

      case 'br':
      case 'nl2br':
        return "nl2br($string)";

      case 'html':
      case 'htmlspecialchars':
        return "htmlspecialchars($string,ENT_QUOTES,'$char_set')";

      case 'htmlall':
      case 'htmlentities':
        return "htmlentities($string,ENT_QUOTES,'$char_set')";

      case 'url':
      case 'urlencode':
        return "rawurlencode($string)";

      case 'urld':
      case 'urldecode':
        return "urldecode($string)";

      case 'rurl':
      case 'rawurlencode':
        return "rawurlencode($string)";

      case 'rurld':
      case 'rawurldecode':
        return "rawurldecode($string)";

      case 'urlpathinfo':
        return "str_replace('%2F','/',rawurlencode($string))";

      case 'quotes':
        // escape unescaped single quotes
        return "preg_replace(\"%(?<!\\\\\\\\)'%\", \"\\\\'\", $string)";


      case 'javascript':
        // escape quotes and backslashes, newlines, etc.
        return "strtr($string, array('\\'=>'\\\\',\"'\"=>\"\\'\",'\"'=>'\\\"',\"\r\"=>'\\r',\"\n\"=>'\\n','</'=>'<\/'))";

      case 'mail':
        /* メールアドレスクローラ対策にメール記号のアット'@'とドット'.'を違う文字列に置き換えます。 */
        // safe way to display e-mail address on a web page
        $at = $this->skConf['SKINNY']['MAILAT'];
        $dot = $this->skConf['SKINNY']['MAILDOT'];
        return "str_replace(array('@', '.'),array('{$at}', '{$dot}'), $string)";
        /** ここからSkinny独自のエスケープ */

      case 'nbsp':
      case 'space':
        return "str_replace(' ', '&nbsp;', $string)";

      case 'number':
      case 'number_format':
        return "number_format($string)";

      case 'ahref':
      case 'link':
        return 'preg_replace(\'/(https?)(:\\/\\/[-_.!~*\\\'()a-zA-Z0-9;\\/?:\\@&=+\\$,%#]+)/\', \'<a href="\\\\1\\\\2">\\\\1\\\\2</a>\',' . $string . ')';
        //return 'ereg_replace("http://[^<>[:space:]]+[[:alnum:]/]",\'<a href="\\0">\\0</a>\','.$string.')';

      case 'rquote':
        // form input value escape
        return "str_replace('\"', '&quot;', $string)";

      case 'strrev':
      case 'reverse':
        return "_skGlobal_mb_strrev($string)";

      default:

        /**
         * 半角文字を1、全角文字を2として文字数カットを行う
         * width[s,n]   s文字目からn長で切り出す
         */
        if (preg_match("/^width\[([0-9]+),([0-9]+)\]$/", $esc_type, $mc)) {
          return "strlen($string)<={$mc[1]} ? $string : mb_strimwidth($string,{$mc[1]},{$mc[2]})";
        }


        /**
         * 文字単位で文字列カットを行う
         * substr[s,n]   s:0以上の数値
         */
        if (preg_match("/^substr\[([-,0-9]+)\]$/", $esc_type, $mc)) {
          return "mb_substr($string,{$mc[1]})";
        }


        /**
         * バイト単位で文字列カットを行う
         * strcut[s,n]   n:0以上の数値
         */
        if (preg_match("/^strcut\[([-,0-9]+)\]$/", $esc_type, $mc)) {
          return "mb_strcut($string,{$mc[1]})";
        }


        /**
         * 文字のカナ変換を行う
         * kana['option']
         * mb_convert_kana( $string , option ); と同義
         */
        if (preg_match("/^kana\[(.+)\]$/", $esc_type, $mc)) {
          return "mb_convert_kana({$string},{$mc[1]})";
        }


        /**
         * 日付形式を変換する
         * date['format'] : date(format,$date) と同義（unixtimestamp以外に strtotimeが通るもの全てが対象）
         * dval同等
         */
        if (preg_match("/^date\[(.+)\]$/", $esc_type, $mc)) {
          return "date({$mc[1]},((is_numeric($string))?$string:strtotime($string)))";
        }


        /**
         * 日付形式（or UNIXTIME）から日本語の曜日へ変換する
         * week : 日～土の全角文字に変換する
         * dval同等
         */
        if (preg_match("/^week$/", $esc_type, $mc)) {
          return "_skGlobal_mb_weekjp($string)";
        }

        return $string;
    }
  }
}
