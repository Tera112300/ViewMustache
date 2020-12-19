<h2>Hello Index.php {{ $title }}</h2>

{{ require 'layout/dummy' }}
{{ require 'layout/base' }}

<h2>{{ $title }}</h2>
<ul>
  {{ foreach ($articles as $index => $article): }}
  <li>{{$index}}:{{ $article }}</li>
  {{ endforeach; }}
</ul>
{{for($i=0; $i<=$hoge; $i++):}}
{{$i}}<br>
{{endfor;}}
{{ if ($hoge === 0): }}
<p>hoge = 0</p>
{{ elseif ($hoge === 1): }}
<p>hoge = 1</p>
{{ else: }}
<p>hoge = else</p>
{{ endif; }}
{{var $var_hoge = 1; }}
{{$var_hoge}}<br>

{{helper('toOsaka_func',$value)}}