<h2>Hello Index.php {{ $title }}</h2>

{{ require 'layout/dummy' }}
{{ require 'layout/base' }}

<h2>{{ $title }}</h2>
{{helper('test02',$value,'tesr')}}<br>
{{helper('toOsaka',$value)}}tesf