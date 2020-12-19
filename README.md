# ViewMustache
PHPテンプレートエンジン
<br>
<br>

# 変数内容の出力

Smarty互換の修飾子を指定して出力します。<br>
修飾子は左方から右方の順で実行されます。<br><br>

## 書式
```php
{{argument|opt1|opt2|opt3|...}}
```
## パラメータ
argument : 出力する文字列<br>
opt1 : 修飾子1<br>
opt2 : 修飾子2<br>
opt3 : 修飾子3<br><br><br>


# 修飾子一覧

## upper / toupper / strtouppr
英小文字を大文字に変換します。<br>
strtoupper(argument)を行います。<br><br>

## lower / tolower / strtolower
英大文字を小文字に変換します。<br>
strtolower(argument)を行います。<br><br>

## strip / strip_tags
HTMLタグを取り除きます。<br>
strip_tags(argument) を行います。<br><br>


## nl2br
改行コードを<br />に置き換えます。<br>
nl2br(argument)を行います。<br><br>

## html / htmlspecialchars
特殊文字を HTML エンティティに変換します。<br>
htmlspecialchars(argument)を行います。<br><br>

## htmlall / htmlentities
適用可能な文字を全て HTML エンティティに変換します。<br>
htmlentities(argument)を行います。<br><br>

## url / urlencode
文字列をURLエンコードします。<br>
urlencode(argument)を行います。<br><br>

## urld / urldecode
文字列をURLデコードします。<br>
urldecode(argument)を行います。<br><br>

## rurl / rawurlencode
RFC1738に基づきURLエンコードを行います。<br>
rawurlencode(argument)を行います。<br><br>

## rurld / rawurldecode
RFC1738に基づきURLデコードを行います。<br>
rawurldecode(argument)を行います。<br><br>

## urlpathinfo
RFC1738に基づきURLエンコードを行い、スラッシュ'/'を'%02F'に置き換えます。<br>
str_replace('%2F','/',rawurlencode(argument))を行います。<br><br>

## quotes
クォート文字に対してクォート処理を行います。<br>
preg_replace(¥"%(?<!¥¥¥¥¥¥¥¥)'%¥", ¥"¥¥¥¥'¥", argument)を行います。<br><br>

## javascript
JavaScript記述に対してクォート処理を行います。<br>
strtr(argument, array('¥¥'=>'¥¥¥¥',¥"'¥"=>¥"¥¥'¥",'¥"'=>'¥¥¥"',¥"¥r¥"=>'¥¥r',¥"¥n¥"=>'¥¥n','</'=>'<¥/'))を行います。<br><br>

## mail
メールアドレスクローラ対策にメール記号のアット'@'とドット'.'を違う文字列に置き換えます。<br>
str_replace(array('@', '.'),array(' [AT] ', ' [DOT] '), argument)を行います。<br>
置き換える [AT]と[DOT]はskinnyの設定で変更可能です。<br><br>

## nbsp / space
文字列中の半角スペースを &nbsp; に置き換えます。<br>
str_replace(' ','&nbsp;',argument)を行います。<br><br>

## number / number_format
数値文字列を３桁区切りのカンマ表記に変換します。<br>
number_format(argument)を行います。<br><br>


## ahref / link
文字列中のURLをリンクに変更します。<br>
preg_replace(¥'/(https?)(:¥¥/¥¥/[-_.!~*¥¥¥'()a-zA-Z0-9;¥¥/?:¥¥@&=+¥¥$,%#]+)/¥', ¥'<a href="¥¥¥¥1¥¥¥¥2">¥¥¥¥1¥¥¥¥2</a>¥','.$string.')を行います。<br><br>


## rquote
文字列中のダブルクォーテーションをエンティティに変換します。<br>
str_replace('\"', '"', argument)を行います。<br><br>

## strrev / reverse
文字列反転します。この処理はマルチバイトにも対応しています。<br><br>

## width[start,length]
指定した幅で文字列を丸めます。<br>
mb_strimwidth(argument, start, length)を行います。<br><br>

## substr[start,length]
文字列の一部を取得します。<br>
lengthを省略すると文字列の最後までを取得します。<br>mb_substr(argument, start, length)を行います。<br><br>


## strcut[start,length]
バイト単位で文字列の一部を取得します。<br>
substrと違いマルチバイト文字の途中を指定した場合は、その文字の先頭バイトから取得されます。<br>
mb_strcut(argument, start, length)を行います。<br><br>

## kana[modetype]
指定した変換オプションに従って文字列を変換します。<br>
modetypeはシングルクォートで囲ってください。<br>
mb_convert_kana(argument, 'modetype')を行います。<br>
modetypeについてはPHPマニュアルの [mb_convert_kana](https://www.php.net/manual/ja/function.mb-convert-kana.php) の項を参照してください。<br><br>

## date[format]
指定したフォーマットに従った日付文字列に変換します。<br>
echoで与えたargumentが数値の場合はunixtime値として扱い、そうでなければstrtotimeを通した結果の値を使用します。<br>
date('format',argument) もしくは date('format',strtotime(argument)) を行います。<br><br>

## week
argument文字列の一部を取得します。<br>
argumentをタイムスタンプとした曜日を「日月火水木金土」のいずれか漢字１字に変換します。<br>
echoで与えたargumentが数値の場合はunixtime値として扱い、そうでなければstrtotimeを通した結果の値を使用します。<br><br>


# 比較

## 書式
```php
{{ if ($hoge === 0): }}
<p>hoge = 0</p>
{{ elseif ($hoge === 1): }}
<p>hoge = 1</p>
{{ else: }}
<p>hoge = else</p>
{{ endif; }}
```

## 説明
このタグは、テンプレート内で条件分岐を行う際に使用します。<br><br>


# 繰り返し
<br>

## 書式
```php
{{for($i=0; $i<=$hoge; $i++):}}
{{$i}}<br>
{{endfor;}}
```

## 説明
このタグは、テンプレート内でfor文を行う際に使用します。<br><br>


# 配列の繰り返し

## 書式
```php
<ul>
  {{ foreach ($articles as $index => $article): }}
  <li>{{$index}}:{{ $article }}</li>
  {{ endforeach; }}
</ul>
```

## 説明
このタグは、テンプレート内でforeach文を行う際に使用します。<br><br>


#  変数を定義する

## 書式
```php
{{var $var_hoge = 1; }}
```

## 説明
このタグは、テンプレート内で利用できる変数をテンプレート内で宣言します。<br>
既に存在する名前が指定された場合は、既存の変数に値が代入されますのでご注意ください。<br><br>

# 外部テンプレートファイルを展開する

## 書式
```php
{{ require 'layout/dummy' }}
```

## 説明
部分的なテンプレートとして保存された別のテンプレートファイルを差し込み展開します。<br><br>


# 独自の処理を出力する

## 書式
```php
{{helper('helper-function',argument)}}
```
## パラメータ
helper-function : 呼び出すヘルパー名<br>
argument : helperに渡す引数名<br>
## 説明
出力タグにない機能をヘルパーとして登録する事が出来ます。<br><br>


# ViewMustache実行方法

```php
<?php
include __DIR__ . '/ViewMustache.php';
const VIEW_DIR = __DIR__ . '/views/';
const CACHE_DIR = __DIR__ . '/cache/';
const HELPER_DIR = __DIR__ . '/helper/';
$view = new ViewMustache(VIEW_DIR, CACHE_DIR,HELPER_DIR);

$view->helper(["toOsaka"]);
echo $view->render('index', [
  'title' => 'Articles',
  'value' => 'ViewMustacheを使ってくれてどうもありがとう。',
  'articles' => [
    'article1',
    'article2',
    'article3',
  ],
  'hoge' => 10,
  'base_url' => 'http://example.com'
]);
```
## 説明
PHPのプログラムからViewMustacheの機能を呼び出す方法です。<br>
「render」で画面に出力します。<br>
「$view->helper」でヘルパーディレクトリーにあるPHPファイルを登録します。<br><br>

# CACHE_DIRについて
```php
$view->use_cache = true;
```
## 説明
「use_cache」をtrueにすることで1回目以降のPHPの処理を軽減することができます。<br>
(デフォルトではfalse)<br><br>

# その他
そのままPHPコードを使うことも出来ます。
```php
<?php echo 'hoge'; ?>
```